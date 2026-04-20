<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateProductMasterState extends Migration
{
    protected $DBGroup = 'shop';

    public function up()
    {
        $this->db->table('product_masters')
            ->where('state', 'live')
            ->update(['state' => 'instock']);

        $this->db->table('product_masters')
            ->where('state', 'draft')
            ->update(['state' => 'inactive']);
    }

    public function down()
    {
        $this->db->table('product_masters')
            ->where('state', 'instock')
            ->update(['state' => 'live']);

        $this->db->table('product_masters')
            ->where('state', 'inactive')
            ->update(['state' => 'draft']);
    }
}
