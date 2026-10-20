<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * A méret-attribútumok globálisan elöl, egymás után jelennek meg
 * (magasság, szélesség, mélység, hosszúság), a többi attribútum utánuk.
 * A termékenkénti sorrend (product_master_default_attributes.position, pl. X-Tray) ezt felülírja.
 */
class SetSizeAttributePositions extends Migration
{
    private const POSITIONS = [
        'Magasság'  => -4,
        'Szélesség' => -3,
        'Mélység'   => -2,
        'Hosszúság' => -1,
    ];

    public function up()
    {
        $db = \Config\Database::connect('shop');
        foreach (self::POSITIONS as $name => $position) {
            $db->table('attributes')->where('name', $name)->update(['position' => $position]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect('shop');
        $db->table('attributes')->whereIn('name', array_keys(self::POSITIONS))->update(['position' => 0]);
    }
}
