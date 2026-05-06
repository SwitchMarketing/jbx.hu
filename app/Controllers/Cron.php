<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\Mailer;
use CodeIgniter\CLI\CLI;

class Cron extends Controller
{
        
   
    /**
     * index
     *
     * az ajánlatkérések és megrendelések kiküldése
     * 
     * @return void
     */
    public function index()
    {
        // Send unsent offer requests
        if ( is_array($offers = $this->_get_unsent_offers()) && ($total = count($offers)) > 0 )
        {
            $currStep   = 1;
            CLI::write("{$total} ajánlatkérés email kiküldése" . PHP_EOL);
            
            foreach($offers as $offer)
            {
                //CLI::write("Küldés {$offer->email}");
                // a fájlok
                $files = (new \App\Models\FileModel())->where('offer_id', $offer->id)->findAll();
                $offer->files = $files;
                CLI::showProgress($currStep++, $total);

                if( Mailer::contact((array)$offer) ) {
                    (new \App\Models\OfferRequestModel())->save([
                        'id' => $offer->id,
                        'emailed_at' => date('Y-m-d H:i:s')
                    ]);
                }
                                    
                //sleep(1);
            }

            //CLI::showProgress(false);
            CLI::write("Ajánlatkérés kiküldés VÉGE" . PHP_EOL);
                   
        }
        else
            CLI::write("Nincs kiküldhető ajánlatkérés email!");

        // Send unsent orders
        if ( is_array($orders = $this->_get_unsent_orders()) && ($total = count($orders)) > 0 )
        {
            $currStep   = 1;
            CLI::write("{$total} megrendelés email kiküldése" . PHP_EOL);
            
            foreach($orders as $order)
            {
                $orderItemModel = new \App\Models\OrderItemModel();
                $items = $orderItemModel->where('order_id', $order->id)->findAll();
                foreach ($items as $item) {
                    $item->attributes = (!empty($item->attributes))
                        ? json_decode($item->attributes, true)
                        : [];
                }
                CLI::showProgress($currStep++, $total);

                // Decode address JSONs
                $billingAddress = [];
                $deliveryAddress = [];
                if (!empty($order->billing_address)) {
                    $billingAddress = (array) json_decode($order->billing_address, true);
                }
                if (!empty($order->delivery_address)) {
                    $deliveryAddress = (array) json_decode($order->delivery_address, true);
                }

                // Mailer::order() and email/order.php expect the legacy field names.
                $mailData = [
                    'name'             => $order->customer_name,
                    'email'            => $order->email,
                    'phone'            => $order->phone,
                    'company'          => $order->company,
                    'contact_person'   => $order->contact_person,
                    'tax_number'       => $order->tax_number,
                    'billing_zip'      => $billingAddress['zip'] ?? '',
                    'billing_state'    => $billingAddress['state'] ?? '',
                    'billing_address'  => $billingAddress['address'] ?? '',
                    'delivery_zip'     => $deliveryAddress['zip'] ?? '',
                    'delivery_state'   => $deliveryAddress['state'] ?? '',
                    'delivery_address' => $deliveryAddress['address'] ?? '',
                    'comments'         => $order->notes ?? '',
                    'products'         => $items,
                ];

                if (
                    !empty($mailData['delivery_zip'])
                    || !empty($mailData['delivery_state'])
                    || !empty($mailData['delivery_address'])
                ) {
                    $mailData['diffDeliveryAddress'] = true;
                }

                if( Mailer::order($mailData) ) {
                    (new \App\Models\OrderModel())->save([
                        'id' => $order->id,
                        'emailed_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            CLI::write("Megrendelés kiküldés VÉGE" . PHP_EOL);
                   
        }
        else
            CLI::write("Nincs kiküldhető megrendelés email!");            
    }

    /**
     * _get_unsent_emails
     *
     * @return array
     */
    private function _get_unsent_offers()
    {
        return (new \App\Models\OfferRequestModel())->where('emailed_at IS NULL AND LENGTH(email) > 0')->findAll();
    }

    /**
     * _get_unsent_orders
     *
     * @return array
     */
    private function _get_unsent_orders()
    {
        return (new \App\Models\OrderModel())->where('emailed_at IS NULL AND LENGTH(email) > 0')->findAll();
    }
    
        
}
