<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\Mailer;
use CodeIgniter\CLI\CLI;

class Cron extends Controller
{
        
   
    /**
     * index
     *
     * az ajánlatkérések kiküldése
     * 
     * @return void
     */
    public function index()
    {

        if ( is_array($offers = $this->_get_unsent_offers()) && ($total = count($offers)) > 0 )
        {
            $currStep   = 1;
            CLI::write("{$total} email kiküldése" . PHP_EOL);
            
            foreach($offers as $offer)
            {
                //CLI::write("Küldés {$offer->email}");
                // a fájlok
                $files = (new \App\Models\FileModel())->where('offer_id', $offer->id)->findAll();
                $offer->files = $files;
                CLI::showProgress($currStep++, $total);
                if( Mailer::contact((array)$offer) )
                    (new \App\Models\OfferRequestModel())->save([
                        'id' => $offer->id,
                        'emailed_at' => date('Y-m-d H:i:s')
                    ]);
                //sleep(1);
            }

            //CLI::showProgress(false);
            CLI::write("VÉGE");
                   
        }
        else
            CLI::write("Nincs kiküldhető email!");            
    }

    /**
     * _get_unsent_emails
     *
     * @return array
     */
    private function _get_unsent_offers()
    {
        return (new \App\Models\OfferRequestModel())->where('emailed_at IS NULL AND LENGTH(email) > 0')->findAll();
    }
    
        
}
