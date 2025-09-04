<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportUnasBlog extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'unas:blog';
    protected $description = 'Unas blog importálása';

    public function run(array $params)
    {
        CLI::write('🔄 UNAS blog importálása...', 'yellow');

        try {

            // Importáljuk a blogbejegyzéseket
            \App\Helpers\UnasImport::blog();
            CLI::write('✅ Blogbejegyzések sikeresen importálva.', 'green');

        } catch (\Throwable $e) {
            CLI::error('❌ Hiba: ' . $e->getMessage());
        }
    }
}
