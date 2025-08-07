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

        // die('Unas API tesztelése...');
        
        $token = 'bbdbde36d5a03b4c9bb6daaf3bf05abf98ed2095';

        echo '<pre>';
        
        // ide gyűjtjük az adatokat
        $records = [];

        // 
        $products = Unas::products($token);

        print_r($products);
        
        die();



        if( is_array($products) && isset($products['Product']) )
        {
            // print_r($products);
            // ha a termékek tömb, akkor végigmegyünk rajta
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
                                            'url' => $img['Url'],
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

            // ha vannak rekordok, akkor végimegyünk a tömbön
            echo 'Found ' . count($records) . ' images for products:';

            print_r($records);

        }
        else
        {
            echo 'Error fetching products: ' . print_r($products, true);
        }

        echo '</pre>';        
        
    }

}
