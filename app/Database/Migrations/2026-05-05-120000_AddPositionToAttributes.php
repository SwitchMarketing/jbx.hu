<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPositionToAttributes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('attributes', [
            'position' => [
                'type'    => 'INT',
                'default' => 0,
                'comment' => 'Display order on frontend',
            ],
        ]);

        // Create index for efficient ordering
        $this->db->query('ALTER TABLE attributes ADD INDEX idx_position (position)');
    }

    public function down()
    {
        $this->forge->dropColumn('attributes', 'position');
    }
}
