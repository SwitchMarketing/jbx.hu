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
        // print_r(Unas::categories($token));
        print_r(Unas::products($token));
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
