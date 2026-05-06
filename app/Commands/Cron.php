<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Cron extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'cron';
    protected $description = 'Ajánlatkérés és megrendelés emailek kiküldése (queue feldolgozás)';

    public function run(array $params)
    {
        CLI::write('Queue feldolgozás indítása...', 'yellow');

        try {
            $controller = new \App\Controllers\Cron();
            $controller->index();
            CLI::write('Queue feldolgozás kész.', 'green');
        } catch (\Throwable $e) {
            CLI::error('Hiba: ' . $e->getMessage());
        }
    }
}
