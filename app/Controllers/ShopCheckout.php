<?php

namespace App\Controllers;

use App\Libraries\BuildPage;
use Exception;
use App\Libraries\Mailer;

/**
 * ShopCheckout
 * 
 * a megrendelés oldal és a megrendelés beküldése
 * 
 */
class ShopCheckout extends BaseController
{
    /**
     * index
     * 
     * pénztár oldal
     *
     * @return void
     */
    public function index()
    {
        $session_id = $this->session->get('cart_session_id');

        $cartModel = new \App\Models\ShoppingCartModel();
        $cartItems = $cartModel
            ->select('id, sku, name, price, unit_price_gross, vat_rate, qty, status')
            ->where('session_id', $session_id)
            ->findAll();

        if (!count($cartItems)) {
            return redirect()->to(base_url('kosar'));
        }

        $cartNetTotal = 0;
        foreach ($cartItems as $item) {
            $linePrice = (float)($item->price ?? 0);
            if ($linePrice <= 0 && !empty($item->unit_price_gross)) {
                $lineVatRate = isset($item->vat_rate) ? (float)$item->vat_rate : null;
                $linePrice = shop_net_from_gross((float)$item->unit_price_gross, $lineVatRate);
            }

            if ($linePrice > 0 && $item->qty) {
                $cartNetTotal += $linePrice * (int)$item->qty;
            }
        }

        $cartSummary = shop_price_breakdown($cartNetTotal);
        $cartNetTotal = $cartSummary->net;
        $cartVat = $cartSummary->vat;
        $cartTotal = $cartSummary->gross;
        $vatRatePercent = $cartSummary->vatRatePercent;

        $data = [
            'header' => [
                'title'   => page_title('Megrendelés'),
                'section' => 'shop'
            ],
            'body' => [
                'breadcrumbs' => [
                    (object) [
                        'title' => 'Megrendelés',
                        'url'   => base_url('penztar')
                    ]
                ],
                'cartTotal'    => $cartTotal,
                'cartNetTotal' => $cartNetTotal,
                'cartVat'      => $cartVat,
                'vatRatePercent' => $vatRatePercent
            ]
        ];

        BuildPage::render('shop-checkout', $data);
    }

    /**
     * submit
     *
     * a megrendelés beküldése
     * 
     * @return void
     */
    public function submit()
    {
        if ($this->request->isAJAX()) {
            $response = (object) [
                'success' => false,
                'message' => '',
                'token'   => csrf_hash()
            ];

            try {
                $post = $this->request->getPost();
                $validation = \Config\Services::validation();
                $this->_set_rules($validation);

                $errors = [];
                if (!$validation->run($post)) {
                    $errors = array_merge($errors, $validation->getErrors());
                }

                if (count($errors)) {
                    throw new Exception(view('validation_errors_list', ['errors' => $errors]));
                }

                $session_id = $this->session->get('cart_session_id');
                $cartModel = new \App\Models\ShoppingCartModel();
                $cartItems = $cartModel
                    ->select('id, sku, name, price, unit_price_gross, vat_rate, qty, status, variant_id')
                    ->where('session_id', $session_id)
                    ->findAll();

                foreach ($cartItems as $item) {
                    $linePrice = (float)($item->price ?? 0);
                    if ($linePrice <= 0 && !empty($item->unit_price_gross)) {
                        $linePrice = (float)$item->unit_price_gross;
                    }
                    $item->price = $linePrice;
                }

                // Variant attribútumok lekérése a kosár tételekhez
                $variantIds = [];
                foreach ($cartItems as $item) {
                    if (!empty($item->variant_id)) {
                        $variantIds[] = (int)$item->variant_id;
                    }
                }
                $variantAttributes = [];
                if (!empty($variantIds)) {
                    $db = \Config\Database::connect('shop');
                    $attributeRows = $db->table('variant_attribute_values')
                        ->select('variant_attribute_values.variant_id, attributes.name, variant_attribute_values.value')
                        ->join('attributes', 'attributes.id = variant_attribute_values.attribute_id', 'left')
                        ->whereIn('variant_attribute_values.variant_id', $variantIds)
                        ->get()
                        ->getResultArray();
                    foreach ($attributeRows as $row) {
                        $vid = (int)$row['variant_id'];
                        if (!isset($variantAttributes[$vid])) {
                            $variantAttributes[$vid] = [];
                        }
                        $variantAttributes[$vid][] = ['name' => $row['name'], 'value' => $row['value']];
                    }
                }

                // Prepare billing and delivery address as JSON
                $billingAddress = [
                    'address' => $post['billing_address'] ?? '',
                    'zip'     => $post['billing_zip'] ?? '',
                    'state'   => $post['billing_state'] ?? ''
                ];

                $deliveryAddress = $billingAddress;
                if (isset($post['diffDeliveryAddress']) && $post['diffDeliveryAddress'] == 'on') {
                    $deliveryAddress = [
                        'address' => $post['delivery_address'] ?? '',
                        'zip'     => $post['delivery_zip'] ?? '',
                        'state'   => $post['delivery_state'] ?? ''
                    ];
                }

                // Create order record
                $orderModel = new \App\Models\OrderModel();
                $orderData = [
                    'customer_name'    => $post['name'],
                    'email'            => $post['email'] ?? '',
                    'phone'            => $post['phone'] ?? '',
                    'company'          => $post['company'] ?? '',
                    'contact_person'   => $post['contact_person'] ?? '',
                    'tax_number'       => $post['tax_number'] ?? '',
                    'billing_address'  => json_encode($billingAddress),
                    'delivery_address' => json_encode($deliveryAddress),
                    'notes'            => $post['comments'] ?? '',
                    'emailed_at'       => null
                ];

                $order_id = $orderModel->insert($orderData, true);
                if (!$order_id) {
                    throw new Exception('Megrendelés rögzítése sikertelen.');
                }

                // Create order items from cart
                $orderItemModel = new \App\Models\OrderItemModel();
                foreach ($cartItems as $item) {
                    $attrs = (!empty($item->variant_id) && isset($variantAttributes[(int)$item->variant_id]))
                        ? json_encode($variantAttributes[(int)$item->variant_id], JSON_UNESCAPED_UNICODE)
                        : null;
                    $itemData = [
                        'order_id'   => $order_id,
                        'sku'        => $item->sku,
                        'name'       => $item->name,
                        'price'      => (float)$item->price,
                        'qty'        => (int)$item->qty,
                        'attributes' => $attrs,
                    ];
                    $orderItemModel->insert($itemData);
                }

                // Clear cart
                $cartModel->where('session_id', $session_id)->delete();

                $this->session->setFlashdata('orderSuccess', '1');

                $response->success = true;
                $response->title = 'Sikeres megrendelés!';
                $response->message = 'Hamarosan keresni fogjuk a megadott elérhetőségeken.';
                $response->redirect = '/sikeres-megrendeles';
            } catch (Exception $e) {
                $response->message = $e->getMessage();
            }

            return $this->response
                ->setStatusCode($response->success ? 200 : 500)
                ->setJSON($response);
        }

        throw new Exception('Nem AJAX kérés!');
    }

    /**
     * success
     *
     * sikeres jelentkezés
     * 
     * @return void
     */
    public function success()
    {
        if (!$this->session->has('orderSuccess')) {
            return redirect()->to('/');
        }

        $session_id = $this->session->get('cart_session_id');
        $cartModel = new \App\Models\ShoppingCartModel();
        $cartModel->where('session_id', $session_id)->delete();
        $this->session->remove('cart_session_id');

        $data = [
            'header' => [
                'section'  => 'contact',
                'title'    => page_title('Sikeres megrendelés'),
                'og_title' => page_title('Sikeres megrendelés'),
            ]
        ];

        BuildPage::render('order-success', $data);
    }

    /**
     * @param mixed $validation
     * 
     * @return object
     */
    private function _set_rules($validation)
    {
        $rules = [
            'name' => [
                'label'  => 'név',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'A <span>{field}</span> nem lehet üres',
                ],
            ],
            'email' => [
                'label'  => 'email cím',
                'rules'  => 'required|valid_email',
                'errors' => [
                    'required' => 'Az <span>{field}</span> nem lehet üres',
                    'valid_email' => 'Az <span>{field}</span> formátuma érvénytelen',
                ]
            ],
            'phone' => [
                'label'  => 'telefonszám',
                'rules'  => 'required|regex_match[/^(\+36|06|36)?(20|30|70)([0-9]{7})$/]',
                'errors' => [
                    'required' => 'A <span>{field}</span> nem lehet üres',
                    'regex_match' => 'A <span>{field}</span> formátuma érvénytelen',
                ]
            ],
            'company' => [
                'label'  => 'cégnév',
                'rules'  => 'required|max_length[100]',
                'errors' => [
                    'required' => 'A <span>{field}</span> nem lehet üres',
                    'max_length' => 'A <span>{field}</span> legfeljebb 100 karakter hosszú lehet',
                ]
            ],
            'contact_person' => [
                'label'  => 'kapcsolattartó',
                'rules'  => 'required|max_length[100]',
                'errors' => [
                    'required' => 'A <span>{field}</span> nem lehet üres',
                    'max_length' => 'A <span>{field}</span> legfeljebb 100 karakter hosszú lehet',
                ]
            ],
            'tax_number' => [
                'label'  => 'adószám',
                'rules'  => 'required|regex_match[/^[0-9]{8}-?[0-9]-?[0-9]{2}$/]',
                'errors' => [
                    'required' => 'Az <span>{field}</span> nem lehet üres',
                    'regex_match' => 'Az <span>{field}</span> formátuma érvénytelen',
                ]
            ],
            'billing_zip' => [
                'label'  => 'számlázási irányítószám',
                'rules'  => 'required|exact_length[4]|numeric',
                'errors' => [
                    'required' => 'Az <span>{field}</span> nem lehet üres',
                    'exact_length' => 'Az <span>{field}</span> pontosan 4 karakter hosszú legyen',
                    'numeric' => 'Az <span>{field}</span> csak számokat tartalmazhat',
                ]
            ],
            'billing_state' => [
                'label'  => 'számlázási település',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'A <span>{field}</span> nem lehet üres',
                ]
            ],
            'billing_address' => [
                'label'  => 'számlázási cím',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'A <span>{field}</span> nem lehet üres',
                ]
            ],
            'diffDeliveryAddress' => [
                'label'  => 'más szállítási cím',
                'rules'  => 'permit_empty|in_list[on]',
                'errors' => [
                    'in_list' => 'A <span>{field}</span> értéke érvénytelen',
                ]
            ],
            'delivery_zip' => [
                'label'  => 'szállítási irányítószám',
                'rules'  => 'permit_empty|required_with[diffDeliveryAddress,on]',
                'errors' => [
                    'required_with' => 'A <span>{field}</span> nem lehet üres',
                ]
            ],
            'delivery_state' => [
                'label'  => 'szállítási település',
                'rules'  => 'permit_empty|required_with[diffDeliveryAddress,on]',
                'errors' => [
                    'required_with' => 'A <span>{field}</span> nem lehet üres',
                ]
            ],
            'delivery_address' => [
                'label'  => 'szállítási cím',
                'rules'  => 'permit_empty|required_with[diffDeliveryAddress,on]',
                'errors' => [
                    'required_with' => 'A <span>{field}</span> nem lehet üres',
                ]
            ],
            'comments' => [
                'label'  => 'megjegyzés',
                'rules'  => 'max_length[500]',
                'errors' => [
                    'max_length' => 'A <span>{field}</span> legfeljebb 500 karakter hosszú lehet',
                ]
            ],
            'privacy' => [
                'label'  => 'adatkezelési tájékoztató',
                'rules'  => 'required|in_list[1]',
                'errors' => [
                    'required' => 'El kell fogadnia az <span>{field}</span> elfogadását',
                    'in_list' => 'Az <span>{field}</span> elfogadása kötelező',
                ]
            ],
            'terms' => [
                'label'  => 'ÁSZF',
                'rules'  => 'required|in_list[1]',
                'errors' => [
                    'required' => 'El kell fogadnia az <span>{field}</span>-et',
                    'in_list' => 'Az <span>{field}</span> elfogadása kötelező',
                ]
            ],
        ];

        $validation->setRules($rules);

        return $validation;
    }
}
