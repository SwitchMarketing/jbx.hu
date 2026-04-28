<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVariantFieldsToCart extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (!$this->hasColumn('cart', 'master_id')) {
            $db->query('ALTER TABLE cart ADD COLUMN master_id INT UNSIGNED NULL AFTER session_id');
        }

        if (!$this->hasColumn('cart', 'variant_id')) {
            $db->query('ALTER TABLE cart ADD COLUMN variant_id INT UNSIGNED NULL AFTER master_id');
        }

        if (!$this->hasColumn('cart', 'legacy_sku')) {
            $db->query('ALTER TABLE cart ADD COLUMN legacy_sku VARCHAR(100) NULL AFTER sku');
        }

        if (!$this->hasColumn('cart', 'selected_options_json')) {
            $db->query('ALTER TABLE cart ADD COLUMN selected_options_json TEXT NULL AFTER status');
        }

        if (!$this->hasColumn('cart', 'unit_price_gross')) {
            $db->query('ALTER TABLE cart ADD COLUMN unit_price_gross DECIMAL(10,2) NULL AFTER price');
            $db->query('UPDATE cart SET unit_price_gross = price WHERE unit_price_gross IS NULL');
            $db->query('ALTER TABLE cart MODIFY unit_price_gross DECIMAL(10,2) NOT NULL');
        }

        if (!$this->hasColumn('cart', 'vat_rate')) {
            $db->query('ALTER TABLE cart ADD COLUMN vat_rate DECIMAL(5,2) NOT NULL DEFAULT 27.00 AFTER unit_price_gross');
        }

        $this->safeQuery('ALTER TABLE cart ADD INDEX idx_cart_session_variant (session_id, variant_id)');
        $this->safeQuery('ALTER TABLE cart ADD INDEX idx_cart_variant_id (variant_id)');
        $this->safeQuery('ALTER TABLE cart ADD INDEX idx_cart_master_id (master_id)');
    }

    public function down()
    {
        $db = $this->db;

        $this->safeQuery('ALTER TABLE cart DROP INDEX idx_cart_session_variant');
        $this->safeQuery('ALTER TABLE cart DROP INDEX idx_cart_variant_id');
        $this->safeQuery('ALTER TABLE cart DROP INDEX idx_cart_master_id');

        if ($this->hasColumn('cart', 'vat_rate')) {
            $db->query('ALTER TABLE cart DROP COLUMN vat_rate');
        }
        if ($this->hasColumn('cart', 'unit_price_gross')) {
            $db->query('ALTER TABLE cart DROP COLUMN unit_price_gross');
        }
        if ($this->hasColumn('cart', 'selected_options_json')) {
            $db->query('ALTER TABLE cart DROP COLUMN selected_options_json');
        }
        if ($this->hasColumn('cart', 'legacy_sku')) {
            $db->query('ALTER TABLE cart DROP COLUMN legacy_sku');
        }
        if ($this->hasColumn('cart', 'variant_id')) {
            $db->query('ALTER TABLE cart DROP COLUMN variant_id');
        }
        if ($this->hasColumn('cart', 'master_id')) {
            $db->query('ALTER TABLE cart DROP COLUMN master_id');
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        $row = $this->db->query("SHOW COLUMNS FROM {$table} LIKE ?", [$column])->getRow();
        return !empty($row);
    }

    private function safeQuery(string $sql): void
    {
        try {
            $this->db->query($sql);
        } catch (\Throwable $e) {
            // Index may already exist from a previous run.
        }
    }
}
