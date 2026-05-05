<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeProductIdNullableInImages extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('images', [
            'product_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Legacy UNAS product ID (nullable for modern images)',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('images', [
            'product_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);
    }
}
