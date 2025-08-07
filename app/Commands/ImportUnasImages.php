<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportUnasImages extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'unas:images';
    protected $description = 'Unas termékek képeinek importálása';

    public function run(array $params)
    {
        CLI::write('🔄 UNAS termékek képeinek importálása...', 'yellow');

        try {

            // Importáljuk a termékeket
            \App\Helpers\UnasImport::images();
            CLI::write('✅ Termékek képei sikeresen importálva.', 'green');

        } catch (\Throwable $e) {
            CLI::error('❌ Hiba: ' . $e->getMessage());
        }
    }
}
