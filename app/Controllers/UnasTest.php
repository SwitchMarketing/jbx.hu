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

        $this->checkLogin();

        $token = $this->session->get('Token');

        echo '<pre>';
        
        // ide gyűjtjük az adatokat
        $records = [];

        // simple xml object
        $categories_xml = Unas::categories($token);

        // a kategóriák XML-ből tömbbé alakítása
        // ha az XML objektum, akkor konvertáljuk tömbbé
        if( is_object($categories_xml) )
        {
            $categories = json_decode(json_encode($categories_xml), true);

            print_r($categories);
            // ha a kategóriák tömb, akkor végigmegyünk rajta

            // a kategóriák kiírása
            if(isset($categories['Category']))
            {
                foreach($categories['Category'] as $category)
                {
                    $record = [
                        'unas_id' => $category['Id'],
                        'name' => $category['Name'],
                        'parent_id' => $category['Parent']['Id'] ?? 0,
                        'order' => $category['Order'] ?? 0
                    ];
                    $records[] = $record;                    
                }
            }
            else
            {
                echo 'No categories found.';
            }
        }        
        else
        {
            echo 'Error fetching categories: ' . $categories_xml;
        }

        // a kategóriák kiírása
        print_r($records);

        //print_r(Unas::products($token));
        print_r($this->session->get());
        echo '</pre>';        
        
    }


    private function checkLogin() {

        if( !$this->session->get('Token') || !$this->session->get('Expire') )
            return $this->unasLogin();

        $dt = \DateTime::createFromFormat("Y.m.d H:i:s", $this->session->get('Expire'));
        if($dt->getTimestamp() < time())
            return $this->unasLogin();

        return true;        
    }

    private function unasLogin() {

        if( is_object($result = Unas::login()) )
        {
            $this->session->set([
                'Token' => (string) $result->{'Token'},
                'Expire' => (string) $result->{'Expire'}
            ]);
            return true;
        }
        return false;

    }
}
