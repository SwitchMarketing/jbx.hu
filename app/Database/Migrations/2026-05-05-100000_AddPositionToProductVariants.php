<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPositionToProductVariants extends Migration
{
    public function up()
    {
        $this->forge->addColumn('product_variants', [
            'position' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'after' => 'stock',
            ],
        ]);

        $db = \Config\Database::connect('shop');

        // Backfill existing rows with stable order-compatible values.
        $db->query('UPDATE product_variants SET position = id WHERE position = 0');

        $db->query('CREATE INDEX idx_product_variants_master_position ON product_variants (master_id, position, id)');
    }

    public function down()
    {
        $db = \Config\Database::connect('shop');
        $db->query('DROP INDEX idx_product_variants_master_position ON product_variants');

        $this->forge->dropColumn('product_variants', 'position');
    }
}
