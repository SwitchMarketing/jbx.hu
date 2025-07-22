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

        helper(['text', 'url', 'filesystem']);

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
            CLI::error('UNAS belépés sikertelen. Kérjük, ellenőrizze a hitelesítő adatait.');
            return;
        }       
        
        // ide gyűjtjük az adatokat
        $records = [];

        // simple xml object
        CLI::write('Kategóriák lekérése az UNAS API-tól...', 'green');
        $categories = Unas::categories($this->token);

        // a kategóriák feldolgozása
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
                    CLI::error('Nincsenek kategóriák a mentéshez.');
                    return;
                }
            }
            else
            {
                CLI::error('Nincsenek kategóriák.');
                return;
            }
        }        
        else
        {
            CLI::error('Hiba a kategóriák lekérésekor: ' . $categories, 'red');
            return;
        }

        CLI::write('Kategóriák sikeresen lekérve.', 'green');
    }

    /**
     * products
     *
     * a termékek lekérése
     * 
     * @return void
     */
    public function products()
    {
        // ellenőrizzük a bejelentkezést
        if( !$this->token || !$this->expires )
        {
            CLI::error('UNAS belépés sikertelen. Kérjük, ellenőrizze a hitelesítő adatait.');
            return;
        }       
        
        // ide gyűjtjük az adatokat
        $records = [];

        // simple xml object
        CLI::write('Termékek lekérése az UNAS API-tól...', 'green');
        $products = Unas::products($this->token);

        // a termékek feldolgozása
        if( is_array($products) )
        {
            // ha a termékek tömb, akkor végigmegyünk rajta
            if(isset($products['Product']))
            {
                foreach($products['Product'] as $product)
                {

                    // a termék adatok kiírása                
                    $rec = [
                        'product_id' => $product['Id'],
                        'sku' => $product['Sku']                    
                    ];

                    // ha van kategória, akkor hozzáadjuk
                    $rec['category_id'] = null;
                    if( isset($product['Categories']) && isset($product['Categories']['Category']) )
                    {   
                        // ha a kategóriák tömb, akkor végigmegyünk rajta
                        foreach($product['Categories']['Category'] as $category)
                        {
                            // ha a kategória típusa 'base', akkor hozzáadjuk
                            if($category['Type'] == 'base') {
                                $rec['category_id'] = $category['Id'];
                            }
                        }
                    }                  
                    
                    // termék neve
                    $rec['name'] = $product['Name'] ?? null;

                    // kereső szavak
                    $rec['slug'] = strtolower($product['SefUrl'] ?? url_title(convert_accented_characters($product['Name']), '-', false));

                    // mennyiségi egység
                    $rec['unit'] = $product['Unit'] ?? null;

                    // leírás
                    $rec['description'] = null;
                    if( isset($product['Description']) && isset($product['Description']['Short']) ) 
                    {
                        $rec['description'] = $product['Description']['Short'] ?? null;
                    }
                    
                    // params
                    $rec['params'] = null;
                    if( isset($product['Params']) && isset($product['Params']['Param']) )
                    {
                        $rec['params'] = json_encode($product['Params']['Param']);
                    }   

                    // types
                    $rec['types'] = null;
                    if( isset($product['Types']) )
                    {
                        $rec['types'] = json_encode($product['Types']);
                    }

                    // prices
                    $rec['prices'] = null;
                    if( isset($product['Prices']) )
                    {
                        $rec['prices'] = json_encode($product['Prices']);
                    }

                    $records[] = $rec;    
                    
                }
                
                // a termékek mentése az adatbázisba
                CLI::write('Termékek mentése az adatbázisba...', 'green');
                if( count($records) > 0 )
                {
                    $productModel = new \App\Models\ProductModel();
                    // töröljük a meglévő termékeket
                    $productModel->truncate(); 
                    // nullázzuk az auto increment értéket
                    // SQLite esetén szükséges, hogy az auto increment érték ne növekedjen
                    $productModel->query("UPDATE SQLITE_SEQUENCE SET SEQ=0 WHERE NAME='product'");
                    // a termékek tömeges mentése
                    $productModel->insertBatch($records);

                    CLI::write('Termékek sikeresen mentve.', 'green');
                    return;               
                }
            }
            else
            {
                CLI::error('Nincsenek termékek.');
                return;
            }
        }        
        else
        {
            CLI::error('Hiba a termékek lekérésekor: ' . $products, 'red');
            return;
        }

        CLI::write('Termékek sikeresen lekérve.', 'green');

    }

    /**
     * images
     *
     * a termékek képeinek lekérése
     * 
     * @return void
     */
    public function images()
    {
        // ellenőrizzük a bejelentkezést
        if( !$this->token || !$this->expires )
        {
            CLI::error('UNAS belépés sikertelen. Kérjük, ellenőrizze a hitelesítő adatait.');
            return;
        }       
        
        // ide gyűjtjük az adatokat
        $records = [];

        // products array
        CLI::write('Termékek lekérése az UNAS API-tól...', 'green');
        $products = Unas::products($this->token);

        // a termékek feldolgozása
        if( is_array($products) )
        {
            // ha a termékek tömb, akkor végigmegyünk rajta
            if(isset($products['Product']))
            {
                CLI::write('Termék képek lekérése az UNAS API-tól...', 'green');
                foreach($products['Product'] as $product)
                {                    
                    if( isset($product['Images']) && is_array($product['Images']) && count($product['Images']) )
                    {                        
                        foreach($product['Images'] as $k => $v)
                        {

                            if($k == 'Image') 
                            {
                                // ha az Image kulcs, akkor ez egy kép tömb
                                $image = $v;

                                if(isset($image[0]) && is_array($image[0]))
                                {
                                    foreach($image as $img)
                                    {
                                        // ha tömb, akkor végigmegyünk rajta
                                        if($img['Type'] == 'base')
                                        {
                                            $rec = [
                                                'product_id' => $product['Id'],
                                                'filename' => $img['Filename'],
                                                'url' => $img['Url']['Medium'],
                                                'alt' => $img['Alt']
                                            ];
                                            $records[] = $rec;
                                        }
                                    }
                                }
                                else
                                {
                                    if($image['Type'] == 'base')
                                    {
                                        $rec = [
                                            'product_id' => $product['Id'],
                                            'filename' => $image['Filename'],
                                            'url' => $image['Url']['Medium'],
                                            'alt' => $image['Alt']
                                        ];
                                        $records[] = $rec;
                                    }  
                                }
                            }
                        }
                    }
                }

                // mennyi kép van összesen
                CLI::write('Képek száma: ' . count($records), 'green');

                // a képek letöltése helyi mappába
                if( count($records) > 0 )
                {
                    $downloadedImages = 0;
                    $imageBasePath = FCPATH . 'imgs/products/';

                    CLI::write('Képek letöltése...', 'green');
                    foreach($records as $k => $record)
                    {
                        $imageUrl = $record['url'];
                        unset($record['url']); // eltávolítjuk a 'url' kulcsot, mert nem szükséges a mentéshez

                        $fileInfo = pathinfo($imageUrl);
                        $fileName =  $fileInfo['basename'];

                        $records[$k]['filename'] = $fileName; // frissítjük a filename-t a letöltött fájl nevével

                        $imagePath = $imageBasePath . $fileName;
                        if( !file_exists($imagePath) )
                        {
                            CLI::write('Kép letöltése: ' . $imageUrl, 'green');
                            write_file($imagePath, file_get_contents($imageUrl));  
                            $downloadedImages++;                          
                        }                        
                    }
                    CLI::write('Képek letöltve: ' . $downloadedImages, 'green');

                    // a képek mentése az adatbázisba
                    CLI::write('Képek mentése az adatbázisba...', 'green');
                    $imageModel = new \App\Models\ImageModel();
                    // töröljük a meglévő képeket
                    $imageModel->truncate(); 
                    // nullázzuk az auto increment értéket
                    // SQLite esetén szükséges, hogy az auto increment érték ne növekedjen
                    $imageModel->query("UPDATE SQLITE_SEQUENCE SET SEQ=0 WHERE NAME='images'");
                    // a képek tömeges mentése
                    $imageModel->insertBatch($records); 
                }
            }
            else
            {
                CLI::error('Nincsenek termékek.');
                return;
            }
        }        
        else
        {
            CLI::error('Hiba a termékek lekérésekor: ' . $products, 'red');
            return;
        }

        CLI::write('Termékek sikeresen lekérve.', 'green');

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
