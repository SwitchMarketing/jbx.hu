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

		$session_id = $this->session->get('cart_session_id');

		// kosár tételek
		$cartModel = new \App\Models\ShoppingCartModel();
		$cartItems = $cartModel
						->select('cart.id, cart.sku, cart.name, cart.price, cart.qty, cart.status, product_id')
						->join('products', 'products.sku = cart.sku', 'left')
						->where('session_id', $session_id)						
						->findAll();

		// termék fotó
		if(count($cartItems)) {

			foreach($cartItems as $item) {
				$productIds[] = $item->product_id;
			}

			// a termékfotók lekérése
			$imageModel = new \App\Models\ImageModel();

			$images = $imageModel
						->whereIn('product_id', $productIds)
						->findAll();

			$imgMap = [];
			foreach($images as $img) {
				if(!isset($imgMap[$img->product_id])) {
					$imgMap[$img->product_id] = $img->filename;
				}
			}

			foreach($cartItems as $k => $item) {
				$cartItems[$k]->image = isset($imgMap[$item->product_id]) ? $imgMap[$item->product_id] : null;
			}			
			
		}

		// a kosár összesen
		$cartTotal = 0;
		if(count($cartItems)) {
			foreach($cartItems as $item) {
				if($item->price && $item->qty) {
					$cartTotal += $item->price * $item->qty;
				}
			}
		}

		// nettó ár
		$cartNetTotal = (int)($cartTotal / 1.27);

		// áfa
		$cartVat = $cartTotal - $cartNetTotal;
		
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

				],

				'cartItems' => $cartItems,
				'cartTotal' => $cartTotal,
				'cartNetTotal' => $cartNetTotal,
				'cartVat' => $cartVat

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

		$session_id = $this->session->get('cart_session_id');

		if(empty($sku)) {
			return $this->response->setStatusCode(400);
		}

		if($qty < 1) {
			$qty = 1;
		}

		// hozzáadás a kosárhoz
		$cartModel = new \App\Models\ShoppingCartModel();

		// létezik-e már a termék a kosárban
		$item = $cartModel->where('session_id', $session_id)
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
				'session_id' => $session_id,
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
	
	public function remove()
	{
		if(!$this->request->isAJAX()) {
			return $this->response->setStatusCode(400);
		}

		$sku = $this->request->getPost('sku');

		$session_id = $this->session->get('cart_session_id');

		if(empty($sku)) {
			return $this->response->setStatusCode(400);
		}

		// tétel eltávolítása a kosárból
		$cartModel = new \App\Models\ShoppingCartModel();

		$item = $cartModel->where('session_id', $session_id)
						  ->where('sku', $sku)
						  ->first();

		if(empty($item)) {
			return $this->response->setStatusCode(400);
		}

		$cartModel->delete($item->id);

		return $this->response->setStatusCode(200)->setJSON([
			'success' => true,
			'message' => $item->name . ' eltávolítva a kosárból.'
		]);

	}	

}
