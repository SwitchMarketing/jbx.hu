<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class ShopCart extends BaseController
{
    /**
	 * index
	 * 
	 * kosár oldal
	 *
	 * @return void
	 */
	public function index()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Kosár'),		
				'section' => 'shop'		
			],
			'body'	=> [

                'breadcrumbs' => [

                    (object) [
                        'title' => 'Kosár',
                        'url'   => base_url('kosar')
                    ]

                ]

            ]
        ];

		BuildPage::render('shop-cart', $data);

    }

	
	/**
	 * add
	 * 
	 * termék hozzáadás a kosárhoz (AJAX)
	 *
	 * @return void
	 */
	public function add()
	{
		if(!$this->request->isAJAX()) {
			return $this->response->setStatusCode(400);
		}

		$sku = $this->request->getPost('sku');
		$qty = (int)$this->request->getPost('qty');

		if(empty($sku)) {
			return $this->response->setStatusCode(400);
		}

		if($qty < 1) {
			$qty = 1;
		}

		// hozzáadás a kosárhoz
		$cartModel = new \App\Models\ShoppingCartModel();

		// létezik-e már a termék a kosárban
		$item = $cartModel->where('session_id', session_id())
						  ->where('sku', $sku)
						  ->first();

		if(!empty($item)) {

			// frissítjük a mennyiséget
			$item->qty = (int)$item->qty + $qty;

			$cartModel->update($item->id, (array)$item);

			$productName = $item->name;

		} else {

			// új tétel a kosárba
			$productModel = new \App\Models\ProductModel();
			$product = $productModel->where('sku', $sku)->first();

			if(empty($product)) {
				return $this->response->setStatusCode(400);
			}

			$status = product_status($product);

			$productName = $product->name;

			$data = [
				'session_id' => session_id(),
				'sku'        => $product->sku,
				'name'       => $product->name,
				'price'      => product_price($product, true),
				'qty'        => $qty,
				'status'     => $status->inStock ? 'Raktárról' : $status->btnText
			];

			$cartModel->insert($data);

		}     
		
		return $this->response->setStatusCode(200)->setJSON([
			'success' => true,
			'message' => $productName . ' hozzáadva a kosárhoz.'
		]);

	}
	

}
