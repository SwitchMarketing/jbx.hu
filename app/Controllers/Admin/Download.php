<?php

namespace App\Controllers\Admin;

use Exception;
use CodeIgniter\Files\Exceptions\FileNotFoundException;

class Download extends BaseResourceController
{

    /**
     * show
     * 
     * a dokumentum letöltése kérés feldolgozása
     *     
     */
    public function show($id = null) 
    {

        $path = WRITEPATH . 'uploads/';

        try {


            if( !($file = new \CodeIgniter\Files\File($path . $id, true)) ) {
                throw new Exception('A fájl nem található');
            }

            $type = $file->getMimeType();
            
            return $this->response
                        ->setContentType($type)
                        ->setBody(file_get_contents($file))
                        ->send();

        }
        catch(FileNotFoundException $e) {
            $e->getMessage();
        }
    }

}
