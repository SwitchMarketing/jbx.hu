<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoryTreeTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'unas_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'depth' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'path' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
            ],
            'pathIds' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
        ]);
        $this->forge->addKey('unas_id', true);
        $this->forge->createTable('category_tree');
    }

    public function down()
    {
        $this->forge->dropTable('category_tree');
    }
}
