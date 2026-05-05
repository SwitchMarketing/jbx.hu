<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSoftDeletesToCategoriesAndProducts extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('deleted_at', 'categories')) {
            $this->forge->addColumn('categories', [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->db->query('CREATE INDEX idx_categories_deleted_at ON categories (deleted_at)');
        }

        if (!$this->db->fieldExists('deleted_at', 'product_masters')) {
            $this->forge->addColumn('product_masters', [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'updated_at',
                ],
            ]);
            $this->db->query('CREATE INDEX idx_product_masters_deleted_at ON product_masters (deleted_at)');
        }

        if (!$this->db->fieldExists('deleted_at', 'product_variants')) {
            $this->forge->addColumn('product_variants', [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'updated_at',
                ],
            ]);
            $this->db->query('CREATE INDEX idx_product_variants_deleted_at ON product_variants (deleted_at)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('deleted_at', 'product_variants')) {
            $this->db->query('DROP INDEX idx_product_variants_deleted_at ON product_variants');
            $this->forge->dropColumn('product_variants', 'deleted_at');
        }

        if ($this->db->fieldExists('deleted_at', 'product_masters')) {
            $this->db->query('DROP INDEX idx_product_masters_deleted_at ON product_masters');
            $this->forge->dropColumn('product_masters', 'deleted_at');
        }

        if ($this->db->fieldExists('deleted_at', 'categories')) {
            $this->db->query('DROP INDEX idx_categories_deleted_at ON categories');
            $this->forge->dropColumn('categories', 'deleted_at');
        }
    }
}
