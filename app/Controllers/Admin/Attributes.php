<?php

namespace App\Controllers\Admin;

use Exception;
use CodeIgniter\RESTful\ResourceController;

class Attributes extends BaseResourceController
{
    protected $modelName = '\App\Models\BaseModel'; // We'll use a generic way or dedicated model

    public function __construct()
    {
        // Custom initialization if needed
    }

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index()
    {
        try {
            $db = \Config\Database::connect('shop');
            $attributes = $db->table('attributes')->orderBy('name', 'ASC')->get()->getResult();
            
            $this->setData($attributes);
            $this->setSuccess(true);
            $this->setMessage('OK');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * create
     *
     * @return ResponseInterface
     */
    public function create()
    {
        try {
            $data = $this->request->getPost();
            if (empty($data['name'])) throw new Exception('Név kötelező');

            $db = \Config\Database::connect('shop');
            $db->table('attributes')->insert(['name' => $data['name']]);
            
            $this->setSuccess(true);
            $this->setMessage('Attribútum létrehozva');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * delete
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        try {
            $db = \Config\Database::connect('shop');
            $db->table('attributes')->where('id', $id)->delete();
            
            $this->setSuccess(true);
            $this->setMessage('Attribútum törölve');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }
}
