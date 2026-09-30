<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Köztes forrás tábla a beszállítói árlistákhoz (Axelent, X-Tray, ...).
 * A nyers árlista-sorokat tárolja, a termékekhez (product_variants) csak
 * egy külön lépés párosítja cikkszám alapján – így lokálisan és élesen is
 * ugyanabból a forrásból, a helyi termékadatokra futtatható a párosítás.
 *
 * Az ugyanilyen szerkezetű CREATE TABLE IF NOT EXISTS a `prices:build-source`
 * által generált SQL fájlban is benne van, ezért a sorrend mindegy.
 */
class CreatePriceListItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'source' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'sku' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'sku_norm' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'product_group' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'product_type' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'null' => true,
            ],
            'currency' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'default' => 'EUR',
            ],
            'price_label' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'source_row' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['source', 'sku_norm']);
        $this->forge->addKey('sku_norm');
        $this->forge->createTable('price_list_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('price_list_items', true);
    }
}
