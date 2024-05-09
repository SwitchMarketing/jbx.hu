<?php 

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class Logout extends BaseResourceController
{
    
    /**
     * index
     * 
     * a Session törlése
     *
     * @return ResponseInterface
     */
    public function index():ResponseInterface 
    {

        try {

            $this->session->stop();

            $this->setSuccess(true);
            $this->setMessage('Kijelentkezve');

        }
        catch(Exception $e) {

            $this->setStatus(500);
            $this->setMessage($e->getMessage());

        }
        finally {

            return $this->respond($this->resp->data, $this->resp->status);

        }    

    }
}