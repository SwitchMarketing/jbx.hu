<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyDetailsToOfferRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('offer_requests', [
            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'name',
            ],
            'company_address' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'company_name',
            ],
            'tax_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'company_address',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('offer_requests', ['company_name', 'company_address', 'tax_number']);
    }
}
