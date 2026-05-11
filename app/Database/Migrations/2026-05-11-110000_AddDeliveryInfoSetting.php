<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeliveryInfoSetting extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect('shop');
        
        // Check if setting already exists
        $exists = $db->table('settings')
            ->where('setting_key', 'delivery_info')
            ->countAllResults() > 0;
        
        if (!$exists) {
            $db->table('settings')->insert([
                'setting_key' => 'delivery_info',
                'setting_value' => 'Gyors és megbízható szállítás egész Magyarország területén. Szállítási költség és idő az aktuális csomagmérettől és célhelytől függ.',
                'data_type' => 'text',
                'description' => 'Szállítási információ a megrendelés oldalon',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect('shop');
        $db->table('settings')
            ->where('setting_key', 'delivery_info')
            ->delete();
    }
}
