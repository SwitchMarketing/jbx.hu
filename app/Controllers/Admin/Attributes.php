<?php

namespace App\Controllers\Admin;

use Exception;

class Attributes extends BaseResourceController
{
    protected $modelName = '\App\Models\AttributeModel';

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index()
    {
        try {
            $attributes = $this->model->orderBy('name', 'ASC')->findAll();
            
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
            if ($this->model->insert($data)) {
                $this->setSuccess(true);
                $this->setMessage('Attribútum létrehozva');
            } else {
                throw new Exception(implode(' ', $this->model->errors()));
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * update
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        try {
            $data = $this->request->getRawInput();

            if ($this->model->update($id, $data)) {
                $this->setSuccess(true);
                $this->setMessage('Attribútum frissítve');
            } else {
                throw new Exception(implode(' ', $this->model->errors()));
            }
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
            if ($this->model->delete($id)) {
                $this->setSuccess(true);
                $this->setMessage('Attribútum törölve');
            } else {
                throw new Exception('Törlési hiba');
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }
}
