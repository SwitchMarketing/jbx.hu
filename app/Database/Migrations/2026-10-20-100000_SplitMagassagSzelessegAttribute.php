<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;

/**
 * A UNAS-ból örökölt összevont "Magasság x szélesség" attribútum szétbontása
 * külön "Magasság" és "Szélesség" attribútumra.
 *
 * Az érték mindenhol "<első>x<második> mm" formátumú, az első szám a magasság,
 * a második a szélesség (PO egyeztetés, 2026-10-20). Kivételek: a ShelfStore polcnál
 * a két szám szélesség x mélység, az X64 L-elválasztónál szélesség x magasság.
 *
 * Visszafordíthatatlan adatmigráció: a down() nem állítja vissza az összevont
 * attribútumot, ahhoz adatbázis-mentés kell.
 */
class SplitMagassagSzelessegAttribute extends Migration
{
    private const COMBINED = 'Magasság x szélesség';
    private const HEIGHT   = 'Magasság';
    private const WIDTH    = 'Szélesség';
    private const DEPTH    = 'Mélység';

    public function up()
    {
        $db = \Config\Database::connect('shop');

        $combinedId = $this->findAttributeId($db, self::COMBINED);
        if ($combinedId === null) {
            return;
        }

        $rows = $db->query(
            'SELECT vav.variant_id, vav.value, m.id AS master_id, m.name AS master_name
            FROM variant_attribute_values vav
            JOIN product_variants pv ON pv.id = vav.variant_id
            LEFT JOIN product_masters m ON m.id = pv.master_id
            WHERE vav.attribute_id = ?',
            [$combinedId]
        )->getResult();

        $defaults = $db->query(
            'SELECT d.master_id, d.default_value, m.name AS master_name
            FROM product_master_default_attributes d
            JOIN product_masters m ON m.id = d.master_id
            WHERE d.attribute_id = ?',
            [$combinedId]
        )->getResult();

        // Először minden értéket ellenőrzünk, hogy hibás adatnál ne maradjon félkész állapot
        $values = [];
        foreach ($rows as $row) {
            $parts = $this->splitValue((string) $row->value);
            if ($parts === null) {
                throw new \RuntimeException(sprintf(
                    'Nem bontható érték: "%s" (variant_id: %d)',
                    $row->value,
                    $row->variant_id
                ));
            }
            $values[] = [$row, $parts];
        }

        $db->transException(true)->transStart();

        $ids = [
            self::HEIGHT => $this->ensureAttributeId($db, self::HEIGHT),
            self::WIDTH  => $this->ensureAttributeId($db, self::WIDTH),
            self::DEPTH  => $this->ensureAttributeId($db, self::DEPTH),
        ];

        foreach ($values as [$row, $parts]) {
            $targets = $this->targetAttributes((string) ($row->master_name ?? ''));
            foreach ($targets as $i => $attrName) {
                $this->insertVariantValue($db, (int) $row->variant_id, $ids[$attrName], $parts[$i]);
            }
        }

        foreach ($defaults as $default) {
            $parts = $this->splitValue((string) ($default->default_value ?? ''));
            $targets = $this->targetAttributes((string) $default->master_name);
            foreach ($targets as $i => $attrName) {
                $db->query(
                    'INSERT IGNORE INTO product_master_default_attributes (master_id, attribute_id, default_value)
                    VALUES (?, ?, ?)',
                    [(int) $default->master_id, $ids[$attrName], $parts[$i] ?? null]
                );
            }
        }

        // A FK-k CASCADE-elnek, de a sorrend miatt explicit töröljük az értékeket is
        $db->table('variant_attribute_values')->where('attribute_id', $combinedId)->delete();
        $db->table('product_master_default_attributes')->where('attribute_id', $combinedId)->delete();
        $db->table('attributes')->where('id', $combinedId)->delete();

        $db->transComplete();
    }

    public function down()
    {
        // Visszafordíthatatlan adatmigráció, lásd az osztály leírását.
    }

    /**
     * "1900x800 mm" / "2200 x 317 mm" -> ['1900 mm', '800 mm']
     */
    private function splitValue(string $value): ?array
    {
        if (!preg_match('/^\s*(\d+(?:[.,]\d+)?)\s*x\s*(\d+(?:[.,]\d+)?)\s*mm\s*$/iu', $value, $m)) {
            return null;
        }

        return [$m[1] . ' mm', $m[2] . ' mm'];
    }

    private function targetAttributes(string $masterName): array
    {
        if (stripos($masterName, 'ShelfStore') === 0) {
            return [self::WIDTH, self::DEPTH];
        }

        // "45x2300 mm": itt fordított a sorrend, 2300 a magasság
        if (mb_stripos($masterName, 'L-elválasztó') !== false) {
            return [self::WIDTH, self::HEIGHT];
        }

        return [self::HEIGHT, self::WIDTH];
    }

    private function insertVariantValue(BaseConnection $db, int $variantId, int $attributeId, string $value): void
    {
        $existing = $db->table('variant_attribute_values')
            ->where(['variant_id' => $variantId, 'attribute_id' => $attributeId])
            ->get()
            ->getRow();

        if ($existing !== null) {
            if (trim((string) $existing->value) !== $value) {
                throw new \RuntimeException(sprintf(
                    'Ütköző érték (variant_id: %d, attribute_id: %d): "%s" vs "%s"',
                    $variantId,
                    $attributeId,
                    $existing->value,
                    $value
                ));
            }
            return;
        }

        $db->table('variant_attribute_values')->insert([
            'variant_id'   => $variantId,
            'attribute_id' => $attributeId,
            'value'        => $value,
        ]);
    }

    private function findAttributeId(BaseConnection $db, string $name): ?int
    {
        $row = $db->table('attributes')->where('name', $name)->get()->getRow();

        return $row ? (int) $row->id : null;
    }

    private function ensureAttributeId(BaseConnection $db, string $name): int
    {
        $id = $this->findAttributeId($db, $name);
        if ($id !== null) {
            return $id;
        }

        $db->table('attributes')->insert(['name' => $name, 'position' => 0]);

        return (int) $db->insertID();
    }
}
