<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class MaintenanceMode implements FilterInterface
{

    public function before(RequestInterface $request, $arguments = null)
    {   

        if(config( 'Config\\AppConfig' )->maintenanceMode && !$request->isCli())
        {

            /*
            $uri = $request->getUri();
            if(stristr($uri->getPath(), 'xhr') || stristr($uri->getPath(), 'pricelist'))
                return false;
            */
            
            $preview = $request->getVar('preview');

            $session = session();
    
            if( !$session->get('preview') && $preview == 'true' )
                $session->set('preview', 1);
    
            if( !$session->get('preview') )
                return redirect()->to('/karbantartas');
        }

    }

    //--------------------------------------------------------------------

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}