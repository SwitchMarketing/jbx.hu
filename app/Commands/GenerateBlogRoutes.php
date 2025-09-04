<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Helpers\BlogRouteCache;

class GenerateBlogRoutes extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'blog:routes';
    protected $description = 'Útvonalak generálása a blog bejegyzések alapján és cache-elése fájlba';

    public function run(array $params)
    {
        CLI::write('🔄 Blog route cache generálása...', 'yellow');

        try {
            BlogRouteCache::generate();
            CLI::write('✅ Sikeresen generálva: ' . BlogRouteCache::CACHE_PATH, 'green');
        } catch (\Throwable $e) {
            CLI::error('❌ Hiba: ' . $e->getMessage());
        }
    }
}
