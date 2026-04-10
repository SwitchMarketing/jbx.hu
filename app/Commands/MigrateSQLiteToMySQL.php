<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MigrateSQLiteToMySQL extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:migrate-sqlite';
    protected $description = 'Migrates data from SQLite files to the configured MySQL database.';

    public function run(array $params)
    {
        $tables = [
            'default' => ['offer_requests', 'offer_files'],
            'shop'    => ['categories', 'category_tree', 'products', 'images', 'blog', 'cart', 'unaslogin']
        ];

        $sources = [
            'default' => [
                'DBDriver' => 'SQLite3',
                'database' => WRITEPATH . 'db/jbxdb.db',
                'DBPrefix' => '',
            ],
            'shop' => [
                'DBDriver' => 'SQLite3',
                'database' => WRITEPATH . 'db/jbxshop.db',
                'DBPrefix' => '',
            ]
        ];

        foreach ($tables as $group => $groupTables) {
            CLI::write("Processing group: $group", 'yellow');
            
            // Explicitly connect to the SQLite source
            $sourceDb = \Config\Database::connect($sources[$group]);

            // Target (MySQL) - uses the active connection from .env
            $targetDb = \Config\Database::connect('default'); 

            foreach ($groupTables as $table) {
                CLI::write("  Migrating table: $table...", 'cyan');
                
                // Verify if table exists in source
                if (!$sourceDb->tableExists($table)) {
                    CLI::write("    Table $table does not exist in source.", 'red');
                    continue;
                }

                $rows = $sourceDb->table($table)->get()->getResultArray();
                
                if (empty($rows)) {
                    CLI::write("    No data to migrate.", 'white');
                    continue;
                }

                // Truncate target and insert data
                $targetDb->table($table)->truncate();
                $count = 0;
                foreach ($rows as $row) {
                    $targetDb->table($table)->insert($row);
                    $count++;
                }
                CLI::write("    Migrated $count rows.", 'green');
            }
        }

        CLI::write('Migration completed!', 'green');
    }
}
