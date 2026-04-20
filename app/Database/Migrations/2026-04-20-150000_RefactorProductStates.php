<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorProductStates extends Migration
{
    protected $DBGroup = 'shop';

    public function up()
    {
        // product_masters: collapse every non-inactive value to 'active'.
        // Prior states in this column: 'live' (legacy), 'instock', 'draft', 'inactive'.
        $this->db->table('product_masters')
            ->where('state !=', 'inactive')
            ->update(['state' => 'active']);

        // product_variants: migrate legacy/unknown values into the new 4-value enum.
        // Prior seeded default: 'live'. Map it and any unexpected value to 'instock'
        // so nothing disappears from the shop silently.
        $this->db->table('product_variants')
            ->whereNotIn('state', ['instock', 'backorder', 'inquire', 'inactive'])
            ->update(['state' => 'instock']);
    }

    public function down()
    {
        // Best-effort reversal: there is no way to recover the original distinct
        // master values from 'active' — they all collapse. We restore the pre-migration
        // default ('live') on anything that looks like it was live-equivalent.
        $this->db->table('product_masters')
            ->where('state', 'active')
            ->update(['state' => 'live']);

        $this->db->table('product_variants')
            ->where('state', 'instock')
            ->update(['state' => 'live']);
    }
}
