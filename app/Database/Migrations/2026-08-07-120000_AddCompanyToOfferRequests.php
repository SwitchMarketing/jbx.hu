<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyToOfferRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('offer_requests', [
            'company' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'name',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('offer_requests', 'company');
    }
}
