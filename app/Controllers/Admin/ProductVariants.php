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

    /**
     * saveAttributes
     *
     * @param  int $id Variant ID
     * @return ResponseInterface
     */
    public function saveAttributes($id = null)
    {
        try {
            $data = $this->request->getPost();
            $attributes = $data['attributes'] ?? [];

            $db = \Config\Database::connect('shop');
            
            // Clear existing
            $db->table('variant_attribute_values')->where('variant_id', $id)->delete();

            // Insert new
            if (!empty($attributes) && is_array($attributes)) {
                foreach ($attributes as $attr) {
                    if (!empty($attr['attribute_id']) && !empty($attr['value'])) {
                        $db->table('variant_attribute_values')->insert([
                            'variant_id'   => $id,
                            'attribute_id' => $attr['attribute_id'],
                            'value'        => $attr['value']
                        ]);
                    }
                }
            }

            $this->setSuccess(true);
            $this->setMessage('Jellemzők mentve');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }
}
