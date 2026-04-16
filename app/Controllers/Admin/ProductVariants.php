<?php

namespace App\Controllers\Admin;

use Exception;
use App\Models\ProductVariantModel;

class ProductVariants extends BaseResourceController
{
    protected $modelName = '\App\Models\ProductVariantModel';

    /**
     * update
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        try {
            $data = $this->request->getRawInput();
            
            // For variations, we mainly update price and stock in this phase
            $updateData = [];
            if (isset($data['price'])) $updateData['price'] = $data['price'];
            if (isset($data['stock'])) $updateData['stock'] = $data['stock'];
            if (isset($data['sku']))   $updateData['sku']   = $data['sku'];

            if (!empty($updateData)) {
                if ($this->model->update($id, $updateData)) {
                    $this->setSuccess(true);
                    $this->setMessage('Variáció frissítve');
                } else {
                    throw new Exception(implode(' ', $this->model->errors()));
                }
            } else {
                throw new Exception('Nincs módosítandó adat');
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }
}
