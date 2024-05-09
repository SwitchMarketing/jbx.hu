<?php 

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class Login extends BaseResourceController
{
    
    /**
     * username
     *
     * @var string
     */
    private $username = 'jbx';

        
    /**
     * password
     *
     * @var string
     */
    private $password = 'jbx#654';

    
    /**
     * create
     * 
     * a POST kérés feldolgozása
     *
     * @return ResponseInterface
     */
    public function create():ResponseInterface 
    {

        try {

            $uname  = $this->request->getPost('uname') ?? '';
            $upass  = $this->request->getPost('upass') ?? '';

            if( (strcmp($uname, $this->username) !== 0) || (strcmp($upass, $this->password) !== 0) )
                throw new Exception('Hibás felhasználó vagy jelszó!');

            $session_data = [
                'username'      => $this->username,
                'userLoggedIn'  => true
            ];

            $this->session->set($session_data);

            $this->setStatus(200);
            $this->setSuccess(true);
            $this->setMessage('Login OK');

        }
        catch(Exception $e) {

            $this->setStatus(401);
            $this->setMessage($e->getMessage());

        }
        finally {

            return $this->respond($this->resp->data, $this->resp->status);

        }    


    }
}