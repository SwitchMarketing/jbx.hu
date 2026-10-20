<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Termékenkénti attribútum-sorrend: ha a position ki van töltve, az felülírja
 * a globális attributes.position sorrendet az adott terméknél (NULL = globális sorrend).
 *
 * Az X-Tray termékeknél a méretek sorrendje szélesség x magasság x hossz
 * (PO egyeztetés, 2026-10-20).
 */
class AddPositionToProductMasterDefaultAttributes extends Migration
{
    private const XTRAY_ORDER = ['Szélesség', 'Magasság', 'Hosszúság'];

    public function up()
    {
        $this->forge->addColumn('product_master_default_attributes', [
            'position' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
                'after'      => 'default_value',
            ],
        ]);

        $db = \Config\Database::connect('shop');
        foreach (self::XTRAY_ORDER as $i => $attrName) {
            $db->query(
                "UPDATE product_master_default_attributes d
                JOIN product_masters m ON m.id = d.master_id
                JOIN attributes a ON a.id = d.attribute_id
                SET d.position = ?
                WHERE m.name LIKE 'X-Tray%' AND a.name = ?",
                [$i + 1, $attrName]
            );
        }
    }

    public function down()
    {
        $this->forge->dropColumn('product_master_default_attributes', 'position');
    }
}
