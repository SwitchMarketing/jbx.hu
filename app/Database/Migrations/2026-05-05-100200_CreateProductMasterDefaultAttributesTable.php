<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductMasterDefaultAttributesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'master_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'attribute_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'default_value' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);

        $this->forge->addKey(['master_id', 'attribute_id'], true);
        $this->forge->addForeignKey('master_id', 'product_masters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('attribute_id', 'attributes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('product_master_default_attributes');
    }

    public function down()
    {
        $this->forge->dropTable('product_master_default_attributes');
    }
}
