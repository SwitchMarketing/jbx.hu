<?php

namespace App\Commands;

use App\Libraries\XlsxReader;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Beszállítói árlisták (xlsx) betöltése a `price_list_items` köztes táblába,
 * és hordozható SQL fájl készítése, ami az éles adatbázisba is importálható
 * (pl. phpMyAdmin-nal). A termékárakat ez NEM módosítja – az a `prices:apply` dolga.
 */
class BuildPriceSource extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'prices:build-source';
    protected $description = 'Árlista xlsx fájlok betöltése a price_list_items táblába + SQL export az éles szerverhez.';
    protected $usage       = 'prices:build-source [fájl.xlsx ...] [--sql <kimenet.sql>] [--currency EUR]';
    protected $arguments   = [
        'fájlok' => 'Feldolgozandó xlsx fájlok (alapértelmezés: _tmp_/prices/*.xlsx)',
    ];
    protected $options = [
        '--sql'      => 'A generált SQL fájl útvonala (alapértelmezés: writable/prices/price_list_items.sql)',
        '--currency' => 'Az árak pénzneme (alapértelmezés: EUR)',
    ];

    /** Fejléc-felismerés: oszlop szerepe => regex a fejléc szövegére. */
    private const HEADER_PATTERNS = [
        'sku'           => '/^(cikksz[aá]m|item\s*(number|no)|article|sku)/iu',
        'price'         => '/(price|[aá]r(\s|\(|$))/iu',
        'product_group' => '/(product\s*group|term[eé]kcsoport)/iu',
        'product_type'  => '/(product\s*type|t[ií]pus)/iu',
        'description'   => '/(description|megnevez|kivitel)/iu',
    ];

    public function run(array $params)
    {
        $files = array_values(array_filter($params, 'is_string'));
        if (!$files) {
            $files = glob(ROOTPATH . '_tmp_/prices/*.xlsx') ?: [];
        }
        if (!$files) {
            CLI::error('Nincs feldolgozandó xlsx fájl.');
            return EXIT_ERROR;
        }

        $currency = strtoupper((string) (CLI::getOption('currency') ?: 'EUR'));
        $sqlPath = (string) (CLI::getOption('sql') ?: WRITEPATH . 'prices/price_list_items.sql');
        $now = date('Y-m-d H:i:s');

        $db = \Config\Database::connect('shop');
        if (!$db->tableExists('price_list_items')) {
            CLI::error('Hiányzik a price_list_items tábla – futtasd: php spark migrate');
            return EXIT_ERROR;
        }

        $allRows = [];
        foreach ($files as $file) {
            if (!is_file($file)) {
                CLI::error('Nem található: ' . $file);
                return EXIT_ERROR;
            }

            $source = pathinfo($file, PATHINFO_FILENAME);
            try {
                $rows = $this->parseFile($file, $source, $currency, $now);
            } catch (\Throwable $e) {
                CLI::error($source . ': ' . $e->getMessage());
                return EXIT_ERROR;
            }

            CLI::write(sprintf('📄 %s: %d tétel', $source, count($rows)), 'green');
            $allRows[$source] = $rows;
        }

        // Helyi betöltés (forrásonként teljes csere)
        $db->transStart();
        foreach ($allRows as $source => $rows) {
            $db->table('price_list_items')->where('source', $source)->delete();
            foreach (array_chunk($rows, 200) as $chunk) {
                $db->table('price_list_items')->insertBatch($chunk);
            }
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            CLI::error('A helyi betöltés sikertelen.');
            return EXIT_ERROR;
        }

        $this->writeSql($db, $sqlPath, $allRows);

        CLI::write('✅ Helyi price_list_items frissítve.', 'green');
        CLI::write('📦 SQL export az éles szerverhez: ' . $sqlPath, 'yellow');

        return EXIT_SUCCESS;
    }

    private function parseFile(string $file, string $source, string $currency, string $now): array
    {
        $sheet = XlsxReader::readFirstSheet($file);
        [$headerRow, $columns] = $this->detectHeader($sheet);

        $items = [];
        $skipped = 0;
        foreach ($sheet as $rowNum => $cells) {
            if ($rowNum <= $headerRow) {
                continue;
            }

            $sku = trim((string) ($cells[$columns['sku']] ?? ''));
            if ($sku === '') {
                continue;
            }

            $rawPrice = trim((string) ($cells[$columns['price']] ?? ''));
            $price = is_numeric($rawPrice) ? round((float) $rawPrice, 4) : null;

            $description = [];
            foreach ($columns['description'] as $col) {
                if (($cells[$col] ?? '') !== '') {
                    $description[] = trim($cells[$col]);
                }
            }

            $skuNorm = self::normalizeSku($sku);
            if (isset($items[$skuNorm])) {
                $skipped++;
                CLI::write(sprintf('  ⚠ duplikált cikkszám (%d. sor, az elsőt tartjuk meg): %s', $rowNum, $sku), 'yellow');
                continue;
            }

            $items[$skuNorm] = [
                'source'        => $source,
                'sku'           => mb_substr($sku, 0, 100),
                'sku_norm'      => mb_substr($skuNorm, 0, 100),
                'product_group' => $this->cell($cells, $columns['product_group'], 150),
                'product_type'  => $this->cell($cells, $columns['product_type'], 150),
                'description'   => $description ? mb_substr(implode(', ', $description), 0, 500) : null,
                'price'         => $price,
                'currency'      => $currency,
                'price_label'   => mb_substr($columns['price_label'], 0, 100),
                'source_row'    => $rowNum,
                'created_at'    => $now,
            ];
        }

        return array_values($items);
    }

    /** Az első olyan sort keresi (max. 30. sorig), amiben cikkszám és ár oszlop is van. */
    private function detectHeader(array $sheet): array
    {
        foreach ($sheet as $rowNum => $cells) {
            if ($rowNum > 30) {
                break;
            }

            $columns = ['sku' => null, 'price' => null, 'product_group' => null, 'product_type' => null, 'description' => []];
            $priceLabel = '';
            foreach ($cells as $col => $text) {
                $text = trim(preg_replace('/\s+/u', ' ', $text));
                foreach (self::HEADER_PATTERNS as $role => $pattern) {
                    if (!preg_match($pattern, $text)) {
                        continue;
                    }
                    if ($role === 'description') {
                        $columns['description'][] = $col;
                    } elseif ($columns[$role] === null) {
                        $columns[$role] = $col;
                        if ($role === 'price') {
                            $priceLabel = $text;
                        }
                    }
                    break;
                }
            }

            if ($columns['sku'] !== null && $columns['price'] !== null) {
                $columns['price_label'] = $priceLabel;
                return [$rowNum, $columns];
            }
        }

        throw new \RuntimeException('Nem található fejléc cikkszám + ár oszloppal.');
    }

    private function cell(array $cells, ?string $col, int $max): ?string
    {
        if ($col === null || ($cells[$col] ?? '') === '') {
            return null;
        }

        return mb_substr(trim($cells[$col]), 0, $max);
    }

    public static function normalizeSku(string $sku): string
    {
        return strtoupper(preg_replace('/\s+/u', '', $sku));
    }

    private function writeSql($db, string $path, array $allRows): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $cols = ['source', 'sku', 'sku_norm', 'product_group', 'product_type', 'description', 'price', 'currency', 'price_label', 'source_row', 'created_at'];

        $out = [];
        $out[] = '-- JBX árlista forrás tábla (price_list_items) – generálva: ' . date('Y-m-d H:i:s');
        $out[] = '-- Források: ' . implode(', ', array_keys($allRows));
        $out[] = '-- Csak a price_list_items táblát érinti, termékárakat nem módosít.';
        $out[] = 'SET NAMES utf8mb4;';
        $out[] = <<<'SQL'
CREATE TABLE IF NOT EXISTS `price_list_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `source` varchar(150) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `sku_norm` varchar(100) NOT NULL,
  `product_group` varchar(150) DEFAULT NULL,
  `product_type` varchar(150) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `price` decimal(15,4) DEFAULT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `price_label` varchar(100) DEFAULT NULL,
  `source_row` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `source_sku_norm` (`source`,`sku_norm`),
  KEY `sku_norm` (`sku_norm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;
        $out[] = 'START TRANSACTION;';

        foreach ($allRows as $source => $rows) {
            $out[] = 'DELETE FROM `price_list_items` WHERE `source` = ' . $db->escape($source) . ';';
            foreach (array_chunk($rows, 200) as $chunk) {
                $values = [];
                foreach ($chunk as $row) {
                    $values[] = '(' . implode(',', array_map(static fn ($c) => $db->escape($row[$c]), $cols)) . ')';
                }
                $out[] = 'INSERT INTO `price_list_items` (`' . implode('`,`', $cols) . "`) VALUES\n" . implode(",\n", $values) . ';';
            }
        }

        $out[] = 'COMMIT;';

        file_put_contents($path, implode("\n\n", $out) . "\n");
    }
}
