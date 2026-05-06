<?php

namespace App\Controllers\Admin;

use Exception;


class Orders extends BaseResourceController
{
    
    protected $modelName = '\App\Models\OrderModel';
    
    /**
     * index
     *
     * Megrendelések listázása
     *
     * @return ResponseInterface
     */
    public function index()
    {
        
        try {

            $orders = $this->model->select('
                id, 
                customer_name, 
                email, 
                phone, 
                company, 
                tax_number, 
                emailed_at,
                created_at')
                ->orderBy('created_at', 'desc')
                ->findAll();
            
            $total = $this->model->countAllResults();
            
            $this->setData($orders);
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
     * show
     *
     * Egyedi megrendelés adatai és tételei
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        
        try {

            if( !is_object($order = $this->model->select('
                id,
                customer_name, 
                email, 
                phone, 
                company,
                contact_person,
                tax_number, 
                billing_address,
                delivery_address,
                notes,
                emailed_at,
                created_at')
                ->find($id)) )
                throw new Exception('Nincs ilyen megrendelés!');
            
            // Fetch order items
            $items = (new \App\Models\OrderItemModel())
                ->select('id, sku, name, price, qty, attributes')
                ->where('order_id', $id)
                ->findAll();

            foreach ($items as $item) {
                $item->attributes = (!empty($item->attributes))
                    ? json_decode($item->attributes, true)
                    : [];
            }

            $order->items = (count($items) > 0) ? $items : [];

            // Decode addresses from JSON
            if (!empty($order->billing_address)) {
                $order->billing_address = json_decode($order->billing_address, true);
            }
            if (!empty($order->delivery_address)) {
                $order->delivery_address = json_decode($order->delivery_address, true);
            }

            $this->setData($order);
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
