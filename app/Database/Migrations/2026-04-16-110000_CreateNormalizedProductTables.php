<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNormalizedProductTables extends Migration
{
    public function up()
    {
        // 1. Master Products
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => '255'],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => '255'],
            'unit'        => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'state'       => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'live'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('category_id');
        $this->forge->createTable('product_masters');

        // 2. Product Variants
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'master_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'unas_id'    => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'sku'        => ['type' => 'VARCHAR', 'constraint' => '100'],
            'price'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'stock'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'state'      => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'live'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('master_id', 'product_masters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('sku');
        $this->forge->createTable('product_variants');

        // 3. Attributes
        $this->forge->addField([
            'id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => '100'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('attributes');

        // 4. Variant Attribute Values
        $this->forge->addField([
            'variant_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'attribute_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'value'        => ['type' => 'VARCHAR', 'constraint' => '255'],
        ]);
        $this->forge->addKey(['variant_id', 'attribute_id'], true);
        $this->forge->addForeignKey('variant_id', 'product_variants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('attribute_id', 'attributes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('variant_attribute_values');
    }

    public function down()
    {
        $this->forge->dropTable('variant_attribute_values');
        $this->forge->dropTable('attributes');
        $this->forge->dropTable('product_variants');
        $this->forge->dropTable('product_masters');
    }
}
