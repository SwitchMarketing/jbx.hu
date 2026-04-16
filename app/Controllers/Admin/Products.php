<?php

namespace App\Controllers\Admin;

use Exception;
use App\Models\ProductMasterModel;
use App\Models\ProductVariantModel;

class Products extends BaseResourceController
{
    protected $modelName = '\App\Models\ProductMasterModel';

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index()
    {
        try {
            $limit  = $this->request->getVar('limit') ?? 25;
            $offset = $this->request->getVar('start') ?? 0;
            $filter = $this->request->getVar('filter');
            $sort   = $this->request->getVar('sort');

            if ($filter) {
                $this->model->setFilters(json_decode($filter));
            }

            if ($sort) {
                $this->model->setSorters(json_decode($sort));
            }

            $result = $this->model->findAll($limit, $offset);

            $this->setData($result->data);
            $this->setTotal($result->total);
            $this->setSuccess(true);
            $this->setMessage('OK');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * show
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        try {
            if (!is_object($product = $this->model->find($id))) {
                throw new Exception('Nincs ilyen rekord!');
            }

            // Include variants
            $variantModel = new ProductVariantModel();
            $product->variants = $variantModel->getWithAttributes($id);

            $this->setData($product);
            $this->setSuccess(true);
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
                $this->setMessage('Sikeres frissítés');
            } else {
                throw new Exception(implode(' ', $this->model->errors()));
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }
}
