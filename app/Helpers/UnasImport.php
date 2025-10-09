<?php

namespace App\Helpers;

use App\Libraries\Unas;
use App\Models\UnasLoginModel;

use CodeIgniter\CLI\CLI;

class UnasImport
{
    
    /**
     * $token
     * 
     * UNAS API token
     */
    public static $token = null;
    
    /**
     * $expires
     * 
     * UNAS API token lejárati időpontja
     */
    public static $expires = null;

    
    /**
     * $error
     * 
     * UNAS API hibaüzenet
     */
    public static $error = null;


    /**
     * categories
     *
     * a kategóriák lekérése az UNAS API-ból
     * és mentése a helyi adatbázisba
     *
     * @return void
     */
    public static function categories()
    {

        // bejelentkezés ellenőrzése
        self::checkLogin();
        
        // ellenőrizzük a bejelentkezést
        if( !self::$token || !self::$expires )
        {
            throw new \Exception('UNAS belépés sikertelen. Kérjük, ellenőrizze a hitelesítő adatait.');
        }

        // ide gyűjtjük az adatokat
        $records = [];

        // simple xml object
        CLI::write('➡️ Kategóriák lekérése az UNAS API-tól...');
        $categories = Unas::categories(self::$token, false);

         // a kategóriák feldolgozása
        if( is_array($categories) )
        {

            // helper függvények meghívása
            helper(['text', 'url']);

            // ha a kategóriák tömb, akkor végigmegyünk rajta
            if(isset($categories['Category']))
            {
                foreach($categories['Category'] as $category)
                {
                    // csak azokat importáljuk ahol vannak termékek
                    if( !isset($category['Products']) || 
                        !isset($category['Products']['Active']) || 
                        !($category['Products']['Active'] > 0) )
                        continue;

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
                CLI::write('➡️ Kategóriák mentése az adatbázisba...');
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
                    throw new \Exception('Nincsenek kategóriák az UNAS API-ban.');
                }
            }
            else
            {
                throw new \Exception('Nincsenek kategóriák az UNAS API-ban.');
            }   
        }        
        else
        {
            throw new \Exception('Hiba történt a kategóriák lekérésekor: ' . Unas::getError());
        }

        
    }

    
    /**
     * products
     * 
     * UNAS termékek lekérése és mentése
     * az adatbázisba.
     *
     * @return void
     */
    public static function products()
    {

        // bejelentkezés ellenőrzése
        self::checkLogin();

        // ellenőrizzük a bejelentkezést
        if( !self::$token || !self::$expires )
        {
            throw new \Exception('UNAS belépés sikertelen. Kérjük, ellenőrizze a hitelesítő adatait.');
        }

        // ide gyűjtjük az adatokat
        $records = [];

        // simple xml object
        CLI::write('➡️ Termékek lekérése az UNAS API-tól...');
        $products = Unas::products(self::$token, false);

        // a termékek feldolgozása
        if( is_array($products) )
        {

            // helper függvények meghívása
            helper(['text', 'url']);

            // ha a termékek tömb, akkor végigmegyünk rajta
            if(isset($products['Product']))
            {
                foreach($products['Product'] as $product)
                {

                    // a termék adatok kiírása                
                    $rec = [
                        'product_id' => $product['Id'],
                        'sku' => $product['Sku'],
                        'state' => $product['State'] ?? null,
                        'inquire' => $product['Inquire'] ?? null
                    ];

                    // ha van kategória, akkor hozzáadjuk
                    $rec['category_id'] = null;
                    if( isset($product['Categories']) && is_array($product['Categories']) )
                    {   
                        // ha a kategóriák tömb, akkor végigmegyünk rajta
                        foreach($product['Categories'] as $category)
                        {                            
                            // ha a kategória egy többdimenziós tömb akkor megkeressük azt az element ahol van 'Type' = 'base'
                            if( is_array($category) && isset($category['Type']) && $category['Type'] == 'base' )
                            {
                                $rec['category_id'] = $category['Id'];
                                break; // kilépünk a ciklusból, mert megtaláltuk a kategóriát
                            } else {
                                foreach($category as $cat)
                                {
                                    if( is_array($cat) && isset($cat['Type']) && $cat['Type'] == 'base' )
                                    {
                                        $rec['category_id'] = $cat['Id'];
                                        break 2; // kilépünk a külső ciklusból is, mert megtaláltuk a kategóriát
                                    }
                                }
                            }                          
                        }
                    }               
                    // ha nincs kategória, akkor alapértelmezett kategória ID-t állítunk be
                    if( !$rec['category_id'] )
                    {
                        $rec['category_id'] = 0;
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

                    // stock
                    $rec['stock'] = null;
                    if( isset($product['Stocks']) )
                    {
                        $rec['stock'] = json_encode($product['Stocks']);
                    }

                    $records[] = $rec;    
                    
                }

                // a termékek mentése az adatbázisba
                CLI::write('➡️ Termékek mentése az adatbázisba...');
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
                    return true;               
                }
            }
            else
            {
                throw new \Exception('Nincsenek termékek az UNAS API-ban.');
            }
        }        
        else
        {
            // hiba történt a termékek lekérésekor
            throw new \Exception('Hiba történt a termékek lekérésekor: ' . Unas::getError());
        }

    }


    public static function images()
    {

        // bejelentkezés ellenőrzése
        self::checkLogin();

        // ellenőrizzük a bejelentkezést
        if( !self::$token || !self::$expires )
        {
            throw new \Exception('UNAS belépés sikertelen. Kérjük, ellenőrizze a hitelesítő adatait.');
        }

        // ide gyűjtjük az adatokat
        $records = [];

        // products array
        CLI::write('➡️ Termékek lekérése az UNAS API-tól...');
        $products = Unas::products(self::$token, false);

        // a termékek feldolgozása
        if( is_array($products) )
        {
            // ha a termékek tömb, akkor végigmegyünk rajta
            if(isset($products['Product']))
            {
                CLI::write('➡️ Termék képek lekérése az UNAS API-tól...');

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
                CLI::write('➡️ Képek száma: ' . count($records));

                // a képek letöltése helyi mappába
                if( count($records) > 0 )
                {

                    // helper függvények meghívása
                    helper(['filesystem']);

                    $downloadedImages = 0;
                    $imageBasePath = FCPATH . 'imgs/products/';

                    CLI::write('➡️ Képek letöltése...');
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
                            CLI::write('➡️ Kép letöltése: ' . $imageUrl);
                            write_file($imagePath, file_get_contents($imageUrl));  
                            $downloadedImages++;                          
                        }                        
                    }
                    CLI::write('➡️ Képek letöltve: ' . $downloadedImages);

                    // a képek mentése az adatbázisba
                    CLI::write('➡️ Képek mentése az adatbázisba...');
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
                throw new \Exception('Nincsenek termékek az UNAS API-ban.');
            }
        }        
        else
        {
            throw new \Exception('Hiba a termékek lekérésekor: ' . $products);
        }

    }


    public static function blog()
    {
        
        // bejelentkezés ellenőrzése
        self::checkLogin();

        // ellenőrizzük a bejelentkezést
        if( !self::$token || !self::$expires )
        {
            throw new \Exception('UNAS belépés sikertelen. Kérjük, ellenőrizze a hitelesítő adatait.');
        }

        // ide gyűjtjük az adatokat
        $records = [];

        // simple xml object
        CLI::write('➡️ Blogbejegyzések lekérése az UNAS API-tól...');
        $blog = Unas::blog(self::$token, false);

        // a blog bejegyzések feldolgozása
        if( is_array($blog) )
        {

            // helper függvények meghívása
            helper(['text', 'url']);

            // ha a blog tömb, akkor végigmegyünk rajta
            if(isset($blog['PageContent']))
            {
                foreach($blog['PageContent'] as $post)
                {
                    $record = [
                        'id' => $post['Id'],
                        'title' => $post['Title'],
                        'slug' => url_title(convert_accented_characters($post['Title']), '-', true),
                        'published' => $post['Published'] ?? '',
                    ];

                    if(isset($post['BlogContent']) && is_array($post['BlogContent']))
                    {
                        $record['content'] = $post['BlogContent']['Text'] ?? '';
                        $record['excerpt'] = $post['BlogContent']['Lead'] ?? '';
                    } 
                    if(isset($post['Image']) && is_array($post['Image']))
                    {
                        $record['image'] = $post['Image']['Lead'] ?? '';
                    }
                    if(isset($post['Dates']) && isset($post['Dates']['Publication']))
                    {
                        $dt = \DateTime::createFromFormat("Y.m.d H:i", $post['Dates']['Publication']);
                        if($dt)
                        {
                            $record['published_at'] = $dt->format('Y-m-d H:i:s');
                        }                        
                    }
                    $records[] = $record;
                }

                // a blog bejegyzések mentése az adatbázisba
                CLI::write('➡️ Blogbejegyzések mentése az adatbázisba...');
                if( count($records) > 0 )
                {
                    $blogModel = new \App\Models\BlogModel();
                    // töröljük a meglévő blog bejegyzéseket
                    $blogModel->truncate(); 
                    // nullázzuk az auto increment értéket
                    // SQLite esetén szükséges, hogy az auto increment érték ne növekedjen
                    $blogModel->query("UPDATE SQLITE_SEQUENCE SET SEQ=0 WHERE NAME='blog'");
                    // a blog bejegyzések tömeges mentése
                    $blogModel->insertBatch($records);   
                    
                    return true;

                } 
                else
                {
                    throw new \Exception('Nincsenek blog bejegyzések az UNAS API-ban.');
                }
            }
            else
            {
                throw new \Exception('Nincsenek kategóriák az UNAS API-ban.');
            }   
        }        
        else
        {
            throw new \Exception('Hiba történt a kategóriák lekérésekor: ' . Unas::getError());
        }

    }

    /**
     * checkLogin
     * 
     * a bejelentkezés ellenőrzése
     *
     * @return void
     */
    private static function checkLogin() {

        $loginModel = new UnasLoginModel();

        // utolsó bejelentkezés ellenőrzése
        $lastLogin = $loginModel->orderBy('id', 'DESC')->first();

        if( !$lastLogin || !$lastLogin->token || !$lastLogin->expires )
            return self::unasLogin();

        $dt = \DateTime::createFromFormat("Y.m.d H:i:s", $lastLogin->expires);
        if($dt->getTimestamp() < time())
            return self::unasLogin();

        // bejelentkezés sikeres, token és lejárat beállítása
        self::$token = $lastLogin->token;
        self::$expires = $lastLogin->expires;

        return true;        
    }

        
    /**
     * unasLogin
     * 
     * UNAS API bejelentkezés
     *
     * @return void
     */
    private static function unasLogin() {

        if( is_array($result = Unas::login()) )
        {
            
            // bejelentkezés sikeres, token és lejárat beállítása
            $loginModel = new UnasLoginModel();
            $loginModel->insert([
                'token' => (string) $result['Token'],
                'expires' => (string) $result['Expire']
            ]);
            
            self::$token = (string) $result['Token'];
            self::$expires = (string) $result['Expire'];
            
            return true;

        } else {

            // bejelentkezés sikertelen, hibaüzenet kiírása
            throw new \Exception('UNAS bejelentkezés sikertelen: ' . Unas::getError());
        }

        // Bejelentkezés sikertelen, visszatérés false értékkel
        return false;

    }
    
}
