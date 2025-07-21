<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\Unas;
use CodeIgniter\CLI\CLI;

/**
 * CronUnas
 * 
 * az UNAS API-n keresztül a kategóriák és termékek lekérése
 * és az adatok mentése a helyi adatbázisba
 * 
 */
class CronUnas extends Controller
{
        
    /**
     * token
     *
     * @var mixed
     */
    protected $token;
    
    /**
     * expires
     *
     * @var mixed
     */
    protected $expires;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        // csak parancssorból futtatható
        if( !is_cli() )
        {
            CLI::error('This command can only be run from the command line.');
            exit(1);
        }

        helper(['text', 'url']);

        $this->checkLogin();
        
    }
   
    /**
     * categories
     *
     * a kategóriák lekérése
     * 
     * @return void
     */
    public function categories()
    {

        // ellenőrizzük a bejelentkezést
        if( !$this->token || !$this->expires )
        {
            CLI::error('UNAS login failed. Please check your credentials.');
            return;
        }       
        
        // ide gyűjtjük az adatokat
        $records = [];

        // simple xml object
        CLI::write('Kategóriák lekérése az UNAS API-tól...', 'green');
        $categories = Unas::categories($this->token);

        // a kategóriák XML-ből tömbbé alakítása
        // ha az XML objektum, akkor konvertáljuk tömbbé
        if( is_array($categories) )
        {
            // ha a kategóriák tömb, akkor végigmegyünk rajta
            if(isset($categories['Category']))
            {
                foreach($categories['Category'] as $category)
                {
                    $record = [
                        'unas_id' => $category['Id'],
                        'name' => $category['Name'],
                        'parent_id' => $category['Parent']['Id'] ?? 0,
                        'order' => $category['Order'] ?? 0,
                        'slug' => url_title(convert_accented_characters($category['Name']), '-', true),
                    ];
                    $records[] = $record;
                }

                // a kategóriák mentése az adatbázisba
                CLI::write('Kategóriák mentése az adatbázisba...', 'green');
                if( count($records) > 0 )
                {
                    $categoryModel = new \App\Models\CategoryModel();
                    // töröljük a meglévő kategóriákat
                    $categoryModel->truncate(); 
                    // nullázzuk az auto increment értéket
                    // SQLite esetén szükséges, hogy az auto increment érték ne növekedjen
                    $categoryModel->query("UPDATE SQLITE_SEQUENCE SET SEQ=0 WHERE NAME='category'");
                    // a kategóriák tömeges mentése
                    $categoryModel->insertBatch($records);

                    // a kategóriafa frissítése
                    $categoryTreeModel = new \App\Models\CategoryTreeModel();
                    $categoryTreeModel->updateTree();                    

                } 
                else
                {
                    CLI::error('No categories found to save.');
                    return;
                }
            }
            else
            {
                CLI::error('No categories found.');
                return;
            }
        }        
        else
        {
            CLI::error('Error fetching categories: ' . $categories, 'red');
            return;
        }

        CLI::write('Kategóriák sikeresen lekérve.', 'green');
    }

        
    /**
     * checkLogin
     * 
     * a bejelentkezés ellenőrzése
     *
     * @return void
     */
    private function checkLogin() {

        $loginModel = new \App\Models\UnasLoginModel();

        // utolsó bejelentkezés ellenőrzése
        $lastLogin = $loginModel->orderBy('id', 'DESC')->first();

        if( !$lastLogin || !$lastLogin->token || !$lastLogin->expires )
            return $this->unasLogin();

        $dt = \DateTime::createFromFormat("Y.m.d H:i:s", $lastLogin->expires);
        if($dt->getTimestamp() < time())
            return $this->unasLogin();

        // bejelentkezés sikeres, token és lejárat beállítása
        $this->token = $lastLogin->token;
        $this->expires = $lastLogin->expires;

        return true;        
    }

        
    /**
     * unasLogin
     * 
     * UNAS API bejelentkezés
     *
     * @return void
     */
    private function unasLogin() {        
        

        if( is_array($result = Unas::login()) )
        {
            
            // bejelentkezés sikeres, token és lejárat beállítása
            $loginModel = new \App\Models\UnasLoginModel();
            $loginModel->insert([
                'token' => (string) $result['Token'],
                'expires' => (string) $result['Expire']
            ]);
            $this->token = (string) $result['Token'];
            $this->expires = (string) $result['Expire'];
            return true;
        } else {
            CLI::error(Unas::getError(), 'red');            
        }
        return false;

    }
    
        
}
