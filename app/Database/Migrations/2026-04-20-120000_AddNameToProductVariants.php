<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameToProductVariants extends Migration
{
    public function up()
    {
        $this->forge->addColumn('product_variants', [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'sku',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('product_variants', 'name');
    }
}
