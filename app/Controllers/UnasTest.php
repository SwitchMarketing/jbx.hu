<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\Unas;

class UnasTest extends BaseController
{
        
   
    /**
     * index
     *
     * a Unas API tesztelése
     * 
     * @return void
     */
    public function index()
    {        

        die('Unas API tesztelése');

        helper(['text', 'url']);

        
        $token = 'cc14cb3ff317b2d1a15c27e084aeab988b4a8871';

        echo '<pre>';
        
        // ide gyűjtjük az adatokat
        $records = [];

        // 
        $products = Unas::products($token);

        // var_dump($products);
        // die();

        // a termékek XML-ből tömbbé alakítása
        // ha az XML objektum, akkor konvertáljuk tömbbé
        if( is_array($products) && isset($products['Product']) )
        {
            // print_r($products);
            // ha a termékek tömb, akkor végigmegyünk rajta
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

            print_r($records);
           
        }        
        else
        {
            echo 'Error fetching categories: ' . print_r($products, true);
        }

        // a kategóriák kiírása
        print_r($records);

        //print_r(Unas::products($token));
        print_r($this->session->get());
        echo '</pre>';        
        
    }

}
