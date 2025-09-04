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
        
        $token = 'fb96306e9db3ce09436e9a4bceddac5938dd7ea8';

        echo '<pre>';
        
        // ide gyűjtjük az adatokat
        $records = [];

        // 
        $blog = Unas::blog($token);

        print_r($blog);

        die();
        
    }

}
