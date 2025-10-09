<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Helpers\BlogRouteCache;
use App\Helpers\CategoryRouteCache;
use App\Libraries\Unas;

class ImportUnasAll extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'unas:import';
    protected $description = 'Unas termékek, kategóriák, képek és blog importálása';

    public function run(array $params)
    {

        try {

            // Importáljuk a kategóriákat
            CLI::write('🔄 UNAS kategóriák importálása...', 'yellow');
            \App\Helpers\UnasImport::categories();      
            CategoryRouteCache::generate();
            CLI::write('✅ Kategóriák sikeresen importálva.', 'green');

            // Importáljuk a termékeket
            CLI::write('🔄 UNAS termékek importálása...', 'yellow');
            \App\Helpers\UnasImport::products();        
            CLI::write('✅ Termékek sikeresen importálva.', 'green');

            // Importáljuk a képeket
            CLI::write('🔄 UNAS képek importálása...', 'yellow');
            \App\Helpers\UnasImport::images();            
            CLI::write('✅ Képek sikeresen importálva.', 'green');

            // Importáljuk a blog bejegyzéseket
            CLI::write('🔄 UNAS blog bejegyzések importálása...', 'yellow');
            \App\Helpers\UnasImport::blog();          
            BlogRouteCache::generate();     
            CLI::write('✅ Blog bejegyzések sikeresen importálva.', 'green');

            CLI::write('🎉 Az UNAS importálás befejeződött!', 'green');
            

        } catch (\Throwable $e) {
            CLI::error('❌ Hiba: ' . $e->getMessage());
        }
    }
}
