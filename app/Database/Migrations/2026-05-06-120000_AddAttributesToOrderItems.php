<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttributesToOrderItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('order_items', [
            'attributes' => [
                'type'    => 'TEXT',
                'null'    => true,
                'after'   => 'qty',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('order_items', 'attributes');
    }
}
