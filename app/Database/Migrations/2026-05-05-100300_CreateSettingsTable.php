<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
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
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'data_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'string',
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('setting_key');
        $this->forge->createTable('settings');

        $db = \Config\Database::connect('shop');
        $db->table('settings')->insert([
            'setting_key' => 'vat_rate_percent',
            'setting_value' => '27',
            'data_type' => 'float',
            'description' => 'AFA kulcs szazalekban',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}
