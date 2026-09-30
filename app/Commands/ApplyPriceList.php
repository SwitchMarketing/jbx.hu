<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * A `price_list_items` forrás táblából árat állít a product_variants sorokra
 * cikkszám alapján – mindig az aktuális (helyi vagy éles) adatbázis termékeire.
 *
 * Alapból próbafuttatás: csak riportot ír. `--apply` esetén ír az adatbázisba,
 * és visszaállító SQL-t is készít a régi árakkal.
 *
 * Riport (CSV): minden variáns, ahol nem sikerült (vagy nem egyértelmű) az árazás,
 * illetve ahol ellenőrzés javasolt.
 */
class ApplyPriceList extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'prices:apply';
    protected $description = 'Termékárak beállítása a price_list_items táblából (alapból próbafuttatás + riport).';
    protected $usage       = 'prices:apply [--apply] [--prefer X-Tray] [--multiplier 1] [--accept-fuzzy] [--overwrite] [--out <mappa>]';
    protected $options = [
        '--apply'        => 'Ténylegesen írja az árakat (enélkül csak riport készül)',
        '--prefer'       => 'Forrás-prioritás több találatnál, vesszővel, névrészlet (alapértelmezés: X-Tray)',
        '--multiplier'   => 'Árszorzó a listaárra, pl. árréshez (alapértelmezés: 1)',
        '--accept-fuzzy' => 'Az egyetlen jelöltes közeli egyezéseket is alkalmazza (előtag, pl. 348-… → W348-…; hossz, pl. 0701 → 0701-2300)',
        '--overwrite'    => 'A már beállított (nem 0) árakat is felülírja',
        '--out'          => 'Kimeneti mappa (alapértelmezés: writable/prices)',
    ];

    // Riport státuszok
    private const NO_MATCH    = 'nincs_egyezes';     // nincs a listákban
    private const AMBIGUOUS   = 'tobbertelmu';       // több lehetséges cikkszám
    private const FUZZY       = 'bizonytalan';       // egy közeli jelölt, nem alkalmazva
    private const FUZZY_DONE  = 'kozeli_alkalmazva'; // egy közeli jelölt, --accept-fuzzy miatt alkalmazva
    private const NO_PRICE    = 'nincs_ar';          // a listában 0 / üres ár
    private const HAS_PRICE   = 'mar_van_ar';        // már van eltérő ár, nem írtuk felül
    private const MULTI_SRC   = 'tobb_forras';       // több listában, eltérő árral – prioritás döntött

    public function run(array $params)
    {
        $apply = CLI::getOption('apply') !== null;
        $acceptFuzzy = CLI::getOption('accept-fuzzy') !== null;
        $overwrite = CLI::getOption('overwrite') !== null;
        $prefer = array_filter(array_map('trim', explode(',', (string) (CLI::getOption('prefer') ?: 'X-Tray'))));
        $multiplier = (float) (CLI::getOption('multiplier') ?: 1);
        $outDir = rtrim((string) (CLI::getOption('out') ?: WRITEPATH . 'prices'), '/\\');

        if ($multiplier <= 0) {
            CLI::error('A --multiplier értéke pozitív szám legyen.');
            return EXIT_ERROR;
        }

        $db = \Config\Database::connect('shop');
        if (!$db->tableExists('price_list_items')) {
            CLI::error('Hiányzik a price_list_items tábla – töltsd fel az SQL exportot, vagy futtasd a prices:build-source parancsot.');
            return EXIT_ERROR;
        }

        $index = $this->loadPriceIndex($db);
        if (!$index) {
            CLI::error('A price_list_items tábla üres.');
            return EXIT_ERROR;
        }

        $variants = $db->table('product_variants v')
            ->select('v.id, v.sku, v.name, v.price, v.state, m.id AS master_id, m.name AS master_name')
            ->join('product_masters m', 'm.id = v.master_id')
            ->where('v.deleted_at', null)
            ->where('m.deleted_at', null)
            ->orderBy('m.name, v.sku')
            ->get()->getResult();

        $report = [];
        $updates = [];
        $stats = [];

        foreach ($variants as $v) {
            $res = $this->resolve($v->sku, $index, $acceptFuzzy);
            $status = $res['status'];
            $item = null;
            $note = $res['note'];

            if ($res['items']) {
                [$item, $conflict] = $this->pickSource($res['items'], $prefer);
                if ($conflict !== null) {
                    $note = trim($note . ' ' . $conflict);
                    $status = $status ?? self::MULTI_SRC;
                }
            }

            $newPrice = null;
            if ($item) {
                if ($item->price === null || (float) $item->price <= 0) {
                    $status = self::NO_PRICE;
                    $item = null;
                } else {
                    $newPrice = round((float) $item->price * $multiplier, 2);
                    $oldPrice = (float) $v->price;

                    if ($status === self::FUZZY) {
                        // csak jelzés, nem írjuk
                    } elseif ($oldPrice > 0 && abs($oldPrice - $newPrice) >= 0.005 && !$overwrite) {
                        $status = self::HAS_PRICE;
                        $note = trim($note . ' Meglévő ár megtartva (--overwrite felülírja).');
                    } elseif (abs($oldPrice - $newPrice) >= 0.005) {
                        $updates[] = ['id' => (int) $v->id, 'old' => $oldPrice, 'new' => $newPrice];
                    }
                }
            }

            $stats[$status ?? 'rendben'] = ($stats[$status ?? 'rendben'] ?? 0) + 1;

            if ($status !== null) {
                $report[] = [
                    'statusz'        => $status,
                    'variant_id'     => $v->id,
                    'master_id'      => $v->master_id,
                    'cikkszam'       => $v->sku,
                    'termek'         => trim($v->master_name),
                    'variant_nev'    => trim((string) $v->name),
                    'allapot'        => $v->state,
                    'jelenlegi_ar'   => $this->num($v->price),
                    'lista_cikkszam' => $item->sku ?? '',
                    'lista_forras'   => $item->source ?? '',
                    'lista_leiras'   => $item->description ?? '',
                    'lista_ar'       => $item ? $this->num($item->price) : '',
                    'uj_ar'          => $newPrice !== null && $status !== self::HAS_PRICE && $status !== self::FUZZY ? $this->num($newPrice) : '',
                    'megjegyzes'     => $note,
                ];
            }
        }

        if (!is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }
        $stamp = date('Ymd_His');
        $reportPath = $outDir . DIRECTORY_SEPARATOR . 'price_report_' . $stamp . '.csv';
        $this->writeCsv($reportPath, $report);

        CLI::newLine();
        CLI::write('Variánsok: ' . count($variants), 'white');
        foreach ($stats as $k => $n) {
            CLI::write(sprintf('  %-20s %d', $k, $n));
        }
        CLI::write('Módosítandó ár: ' . count($updates), 'yellow');
        CLI::write('📄 Riport (' . count($report) . ' sor): ' . $reportPath, 'yellow');

        if (!$apply) {
            CLI::write('ℹ Próbafuttatás volt, az adatbázis nem változott. Íráshoz: --apply', 'cyan');
            return EXIT_SUCCESS;
        }

        if (!$updates) {
            CLI::write('Nincs módosítandó ár.', 'green');
            return EXIT_SUCCESS;
        }

        $rollbackPath = $outDir . DIRECTORY_SEPARATOR . 'price_rollback_' . $stamp . '.sql';
        $this->writeRollback($rollbackPath, $updates);

        $now = date('Y-m-d H:i:s');
        $db->transStart();
        foreach ($updates as $u) {
            $db->table('product_variants')->where('id', $u['id'])->update(['price' => $u['new'], 'updated_at' => $now]);
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            CLI::error('Az árak írása sikertelen, a tranzakció visszagördült.');
            return EXIT_ERROR;
        }

        CLI::write('✅ ' . count($updates) . ' ár frissítve.', 'green');
        CLI::write('↩ Visszaállító SQL: ' . $rollbackPath, 'yellow');

        return EXIT_SUCCESS;
    }

    /** sku_norm => [tétel, ...] (forrásonként egy) */
    private function loadPriceIndex($db): array
    {
        $index = [];
        foreach ($db->table('price_list_items')->get()->getResult() as $row) {
            $index[$row->sku_norm][] = $row;
        }

        return $index;
    }

    /**
     * Cikkszám párosítás. Visszaad: status (null = rendben), items (a választott cikkszám tételei), note.
     *
     * 1. pontos egyezés (szóköz nélkül, nagybetűsen)
     * 2. normalizált: "_" → "-", záró "-"/"_" levágva (pl. XIT_R14-101, 0192-150_)
     * 3. közeli jelöltek – csak --accept-fuzzy és egyetlen jelölt esetén alkalmazva:
     *    - egybetűs előtag a listában (348-220150 → W348-220150)
     *    - hosszabb lista-cikkszám kötőjeles/betűs folytatással (0701 → 0701-2300, D20-XXX570 → D20-XXX570A/B)
     * 4. rövidebb lista-cikkszám (P11-250-A01 → P11-250) – mindig csak jelzés, sosem alkalmazzuk
     */
    private function resolve(string $sku, array $index, bool $acceptFuzzy): array
    {
        $key = BuildPriceSource::normalizeSku($sku);
        if (isset($index[$key])) {
            return ['status' => null, 'items' => $index[$key], 'note' => ''];
        }

        $loose = rtrim(str_replace('_', '-', $key), '-');
        if ($loose !== '' && isset($index[$loose])) {
            return ['status' => null, 'items' => $index[$loose], 'note' => 'Normalizált egyezés: ' . $index[$loose][0]->sku];
        }

        $fuzzy = [];
        $shorter = [];
        foreach (array_keys($index) as $candidate) {
            $candidate = (string) $candidate;
            if (preg_match('/^[A-Z]' . preg_quote($loose, '/') . '$/', $candidate)
                || preg_match('/^' . preg_quote($loose, '/') . '(-[A-Z0-9]+|[A-Z])$/', $candidate)) {
                $fuzzy[] = $candidate;
            } elseif (strlen($candidate) >= 4 && strpos($loose, $candidate . '-') === 0) {
                $shorter[] = $candidate;
            }
        }

        if (count($fuzzy) > 1) {
            return ['status' => self::AMBIGUOUS, 'items' => [], 'note' => 'Jelöltek: ' . $this->skuList($fuzzy, $index)];
        }

        if (count($fuzzy) === 1) {
            $items = $index[$fuzzy[0]];
            if ($acceptFuzzy) {
                return ['status' => self::FUZZY_DONE, 'items' => $items, 'note' => 'Közeli egyezés alkalmazva: ' . $items[0]->sku];
            }
            return ['status' => self::FUZZY, 'items' => $items, 'note' => 'Közeli jelölt (nem alkalmazva, --accept-fuzzy): ' . $items[0]->sku];
        }

        if ($shorter) {
            return ['status' => self::FUZZY, 'items' => [], 'note' => 'Rövidebb lista-cikkszám, valószínűleg más termék: ' . $this->skuList($shorter, $index)];
        }

        return ['status' => self::NO_MATCH, 'items' => [], 'note' => ''];
    }

    /** Több forrás esetén a --prefer sorrend dönt; eltérő áraknál megjegyzést ad vissza. */
    private function pickSource(array $items, array $prefer): array
    {
        usort($items, function ($a, $b) use ($prefer) {
            return $this->sourceRank($a->source, $prefer) <=> $this->sourceRank($b->source, $prefer)
                ?: strcmp($a->source, $b->source);
        });

        if (count($items) < 2) {
            return [$items[0], null];
        }

        $prices = array_unique(array_map(fn ($i) => $this->num($i->price), $items));
        if (count($prices) < 2) {
            return [$items[0], null];
        }

        $parts = array_map(fn ($i) => $i->source . ': ' . $this->num($i->price), $items);
        return [$items[0], 'Több forrás eltérő árral (' . implode('; ', $parts) . '), választott: ' . $items[0]->source . '.'];
    }

    private function sourceRank(string $source, array $prefer): int
    {
        foreach ($prefer as $i => $needle) {
            if (stripos($source, $needle) !== false) {
                return $i;
            }
        }

        return count($prefer);
    }

    private function skuList(array $keys, array $index): string
    {
        return implode(', ', array_map(function ($k) use ($index) {
            $i = $index[$k][0];
            return $i->sku . ' (' . $this->num($i->price) . ' ' . $i->currency . ')';
        }, $keys));
    }

    private function num($value): string
    {
        return $value === null ? '' : number_format((float) $value, 2, ',', '');
    }

    /** Excel-barát CSV: UTF-8 BOM, pontosvessző elválasztó. */
    private function writeCsv(string $path, array $rows): void
    {
        $fh = fopen($path, 'w');
        fwrite($fh, "\xEF\xBB\xBF");
        if ($rows) {
            fputcsv($fh, array_keys($rows[0]), ';');
            foreach ($rows as $row) {
                fputcsv($fh, $row, ';');
            }
        }
        fclose($fh);
    }

    private function writeRollback(string $path, array $updates): void
    {
        $lines = ['-- Árak visszaállítása a prices:apply előtti állapotra – ' . date('Y-m-d H:i:s'), 'START TRANSACTION;'];
        foreach ($updates as $u) {
            $lines[] = sprintf('UPDATE `product_variants` SET `price` = %.2F WHERE `id` = %d;', $u['old'], $u['id']);
        }
        $lines[] = 'COMMIT;';

        file_put_contents($path, implode("\n", $lines) . "\n");
    }
}
