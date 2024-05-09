<?php

namespace App\Controllers\Admin;

use Exception;


class SessionData extends BaseResourceController
{
    
    
    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index()
    {
        
        try {

            $sessionData = $this->session->get();
            if( !isset($sessionData['userLoggedIn']) || !($sessionData['userLoggedIn'] === true) )
                throw new Exception('Nincs bejelentkezve');

            $this->setSuccess(true);
            $this->setMessage('OK');
        }
        catch(Exception $e) {
            $this->setMessage($e->getMessage());
        }
        finally {

            return $this->setResponse();

        }        

    }
}