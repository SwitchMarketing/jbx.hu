<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;

abstract class BaseResourceController extends ResourceController
{
    
    use ResponseTrait;
    
    /**
     * model
     *
     * @var mixed
     */
    protected $model;
        
    /**
     * format
     *
     * @var string
     */
    protected $format = 'json';
    
    /**
     * modelName
     *
     * @var mixed
     */
    protected $modelName;
    
    /**
     * resp
     * 
     * a válasz
     *
     * @var mixed
     */
    protected $resp;

    
    /**
     * session
     *
     * @var mixed
     */
    protected $session;
    
        
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->resp = (object) [
            'status'    => 200,
            'data'      => (object) [
                'success' => false,
                'message' => ''
            ]
        ];

        $this->session = \Config\Services::session(); 
    }
    
    /**
     * setResponse
     *
     * @return void
     */
    protected function setResponse():ResponseInterface
    {
        return parent::respond($this->resp->data, $this->resp->status);
    }
    
    /**
     * setStatus
     *
     * @param  mixed $status
     * @return object
     */
    protected function setStatus(int $status):object
    {
        $this->resp->status = $status;
        return $this->resp;
    }


    /**
     * setSuccess
     *
     * @param  mixed $success
     * @return object
     */
    protected function setSuccess(bool $success):object
    {
        $this->resp->data->success = $success;
        if($success)
            $this->setStatus(200);
        return $this->resp;
    }
    
    /**
     * setMessage
     *
     * @param  mixed $message
     * @return object
     */
    protected function setMessage(string $message = ''):object
    {
        $this->resp->data->message = $message;
        return $this->resp;
    }

        
    /**
     * setData
     *
     * @param  mixed $data
     * @return object
     */
    protected function setData($data):object
    {
        $this->resp->data->data = $data;
        return $this->resp;
    }
    
    /**
     * setTotal
     *
     * @param  mixed $total
     * @return object
     */
    protected function setTotal($total):object
    {
        $this->resp->data->total = $total;
        return $this->resp;
    }


    /**
     * setAction
     *
     * @param  string $action
     * @return object
     */
    protected function setAction(string $action):object
    {
        $this->resp->data->action = $action;
        return $this->resp;
    }
    
}
