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

            $leads = $this->model->orderBy('created_at', 'desc')->findAll();
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
}