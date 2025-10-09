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

        die('Unas API tesztelése...');
        
        $token = 'c107e4bb11c5530555517811c04d61a58e124c54';

        // ide gyűjtjük az adatokat
        $records = [];

        // 
        $products = Unas::products($token);
        $categories = Unas::categories($token);

        echo '<pre>';
        // print_r($categories);
        print_r($products);
        echo '</pre>';

        die();
        
    }

}
