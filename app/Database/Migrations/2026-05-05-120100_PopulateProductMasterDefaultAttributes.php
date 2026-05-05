<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PopulateProductMasterDefaultAttributes extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect('shop');

        // For each product_master, find all attributes used by any of its variants
        // and insert them as default attributes. Ignore if already exists.
        $sql = '
            INSERT IGNORE INTO product_master_default_attributes (master_id, attribute_id)
            SELECT DISTINCT pv.master_id, vav.attribute_id
            FROM product_variants pv
            JOIN variant_attribute_values vav ON vav.variant_id = pv.id
            WHERE pv.master_id IS NOT NULL
        ';

        $db->query($sql);
    }

    public function down()
    {
        $db = \Config\Database::connect('shop');
        $db->table('product_master_default_attributes')->truncate();
    }
}
