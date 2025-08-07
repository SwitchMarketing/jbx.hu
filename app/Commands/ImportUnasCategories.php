<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportUnasCategories extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'unas:categories';
    protected $description = 'Unas kategóriák importálása';

    public function run(array $params)
    {
        CLI::write('🔄 UNAS kategóriák importálása...', 'yellow');

        try {

            // Importáljuk a kategóriákat
            \App\Helpers\UnasImport::categories();
            CLI::write('✅ Kategóriák sikeresen importálva.', 'green');

        } catch (\Throwable $e) {
            CLI::error('❌ Hiba: ' . $e->getMessage());
        }
    }
}
