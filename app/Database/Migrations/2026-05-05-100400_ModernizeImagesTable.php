<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModernizeImagesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('images', [
            'master_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'product_id',
            ],
            'variant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'master_id',
            ],
            'position' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'after' => 'alt',
            ],
        ]);

        $db = \Config\Database::connect('shop');

        $db->query('CREATE INDEX idx_images_master_position ON images (master_id, position, id)');
        $db->query('CREATE INDEX idx_images_variant ON images (variant_id)');

        // Best-effort backfill from legacy product_id (= variant unas_id in most rows).
        $db->query('UPDATE images i JOIN product_variants pv ON pv.unas_id = i.product_id SET i.variant_id = pv.id, i.master_id = pv.master_id WHERE i.master_id IS NULL');
        $db->query('UPDATE images SET position = id WHERE position = 0');
    }

    public function down()
    {
        $db = \Config\Database::connect('shop');
        $db->query('DROP INDEX idx_images_master_position ON images');
        $db->query('DROP INDEX idx_images_variant ON images');

        $this->forge->dropColumn('images', ['master_id', 'variant_id', 'position']);
    }
}
