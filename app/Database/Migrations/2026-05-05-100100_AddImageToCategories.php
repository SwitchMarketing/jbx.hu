<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageToCategories extends Migration
{
    public function up()
    {
        $this->forge->addColumn('categories', [
            'image' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'slug',
            ],
        ]);

        $this->forge->addColumn('category_tree', [
            'image' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'slug',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('category_tree', 'image');
        $this->forge->dropColumn('categories', 'image');
    }
}
