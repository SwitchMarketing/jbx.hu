<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportUnasProducts extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'unas:products';
    protected $description = 'Unas termékek importálása';

    public function run(array $params)
    {
        CLI::write('🔄 UNAS termékek importálása...', 'yellow');

        try {

            // Importáljuk a termékeket
            \App\Helpers\UnasImport::products();
            CLI::write('✅ Termékek sikeresen importálva.', 'green');

        } catch (\Throwable $e) {
            CLI::error('❌ Hiba: ' . $e->getMessage());
        }
    }
}
