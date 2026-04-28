<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSlugToProductVariants extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (!$this->hasColumn('product_variants', 'slug')) {
            $db->query("ALTER TABLE product_variants ADD COLUMN slug VARCHAR(255) NULL AFTER name");
        }

        $rows = $db->table('product_variants')
            ->select('id, master_id, name, sku, slug')
            ->orderBy('master_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        $seen = [];

        foreach ($rows as $row) {
            $existingSlug = trim((string) ($row->slug ?? ''));
            if ($existingSlug !== '') {
                $seen[$row->master_id . ':' . $existingSlug] = true;
                continue;
            }

            $base = $this->slugify($row->name ?: $row->sku ?: ('variant-' . $row->id));
            if ($base === '') {
                $base = 'variant-' . $row->id;
            }

            $slug = $this->nextUniqueSlug($base, (int) $row->master_id, $seen);
            $seen[$row->master_id . ':' . $slug] = true;

            $db->table('product_variants')
                ->where('id', $row->id)
                ->update(['slug' => $slug]);
        }

        $db->query("ALTER TABLE product_variants ADD INDEX idx_product_variants_slug (slug)");
        $db->query("ALTER TABLE product_variants ADD UNIQUE KEY uq_product_variants_master_slug (master_id, slug)");
    }

    public function down()
    {
        $db = $this->db;

        if ($this->hasColumn('product_variants', 'slug')) {
            $db->query("ALTER TABLE product_variants DROP INDEX uq_product_variants_master_slug");
            $db->query("ALTER TABLE product_variants DROP INDEX idx_product_variants_slug");
            $db->query("ALTER TABLE product_variants DROP COLUMN slug");
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        $row = $this->db->query("SHOW COLUMNS FROM {$table} LIKE ?", [$column])->getRow();
        return !empty($row);
    }

    private function nextUniqueSlug(string $base, int $masterId, array $seen): string
    {
        $slug = $base;
        $i = 2;

        while (isset($seen[$masterId . ':' . $slug])) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value;
    }
}