<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class UserLoggedIn implements FilterInterface
{

    public function before(RequestInterface $request, $arguments = null)
    {
        $session  = service('session');

        if(in_array(uri_string(), ['login', 'sessiondata', 'logout'])) return;

        if(!$session->userLoggedIn) {
            $response = service('response');
            $response->setStatusCode(401);
            return $response->setJSON([
                'success' => false,
                'message' => 'Nem hitelesített felhasználó!'                    
            ]);
        }
    }

    //--------------------------------------------------------------------

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}