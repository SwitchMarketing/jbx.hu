<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiscountPriceAndCurrencyRate extends Migration
{
    protected function hasColumn(string $table, string $column): bool
    {
        return in_array($column, $this->db->getFieldNames($table), true);
    }

    public function up()
    {
        if (!$this->hasColumn('product_variants', 'discount_price')) {
            $this->forge->addColumn('product_variants', [
                'discount_price' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'null' => true,
                    'default' => null,
                    'after' => 'price',
                ],
            ]);
        }

        $db = \Config\Database::connect('shop');
        $exists = $db->table('settings')
            ->select('id')
            ->where('setting_key', 'eur_to_huf_rate')
            ->get()
            ->getRow();

        if (!$exists) {
            $db->table('settings')->insert([
                'setting_key' => 'eur_to_huf_rate',
                'setting_value' => '400',
                'data_type' => 'float',
                'description' => 'EUR -> HUF arfolyam',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        if ($this->hasColumn('product_variants', 'discount_price')) {
            $this->forge->dropColumn('product_variants', 'discount_price');
        }

        $db = \Config\Database::connect('shop');
        $db->table('settings')->where('setting_key', 'eur_to_huf_rate')->delete();
    }
}
