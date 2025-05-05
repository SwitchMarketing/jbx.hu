<?php

namespace App\Controllers\Admin;

use Exception;


class Leads extends BaseResourceController
{
    
    protected $modelName = '\App\Models\OfferRequestModel';
    
    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index()
    {
        
        try {

            $leads = $this->model->select('
                id, 
                name, 
                email, 
                phone, 
                products, 
                utm_source, 
                LENGTH(message) as message_length,
                created_at')->orderBy('created_at', 'desc')->findAll();
            $total = $this->model->countAllResults();
            
            $this->setData($leads);
            $this->setTotal($total);
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

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        
        try {

            if( !is_object($lead = $this->model->select('name, email, phone, message, utm_source, created_at')->find($id)) )
                throw new Exception('Nincs ilyen rekord!');
            
            $files = (new \App\Models\FileModel())->select('filename')->where('offer_id', $id)->findAll();

            $lead->files = (count($files) > 0) ? $files : null;

            $this->setData($lead);
            $this->setSuccess(true);
        }
        catch(Exception $e) {
            $this->setMessage($e->getMessage());
        }
        finally {

            return $this->setResponse();

        }        

    }
}