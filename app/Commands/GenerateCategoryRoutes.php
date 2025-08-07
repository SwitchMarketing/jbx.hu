<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Helpers\CategoryRouteCache;

class GenerateCategoryRoutes extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'category:routes';
    protected $description = 'Útvonalak generálása a kategóriák alapján és cache-elése fájlba';

    public function run(array $params)
    {
        CLI::write('🔄 Kategória route cache generálása...', 'yellow');

        try {
            CategoryRouteCache::generate();
            CLI::write('✅ Sikeresen generálva: ' . CategoryRouteCache::CACHE_PATH, 'green');
        } catch (\Throwable $e) {
            CLI::error('❌ Hiba: ' . $e->getMessage());
        }
    }
}
