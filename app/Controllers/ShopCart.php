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
						->select('cart.id, cart.master_id, cart.variant_id, cart.sku, cart.name, cart.price, cart.unit_price_gross, cart.qty, cart.status')
						->where('session_id', $session_id)						
						->findAll();

		$variantModel = new \App\Models\ProductVariantModel();
		$variantIds = [];
		$legacySkuMap = [];
		foreach ($cartItems as $item) {
			if (!empty($item->variant_id)) {
				$variantIds[] = (int)$item->variant_id;
			} elseif (!empty($item->sku)) {
				$legacySkuMap[$item->sku] = true;
			}
		}

		if (!empty($legacySkuMap)) {
			$missingVariants = $variantModel->whereIn('sku', array_keys($legacySkuMap))->findAll();
			foreach ($missingVariants as $v) {
				$variantIds[] = (int)$v->id;
			}
		}

		$variantIds = array_values(array_unique($variantIds));
		$variantsById = [];
		$variantsBySku = [];
		if (!empty($variantIds)) {
			$variants = $variantModel
				->select('id, master_id, unas_id, sku, name, price, state')
				->whereIn('id', $variantIds)
				->findAll();
			foreach ($variants as $variant) {
				$variantsById[(int)$variant->id] = $variant;
				$variantsBySku[$variant->sku] = $variant;
			}
		}

		foreach ($cartItems as $item) {
			if (empty($item->variant_id) && !empty($item->sku) && isset($variantsBySku[$item->sku])) {
				$resolvedVariant = $variantsBySku[$item->sku];
				$item->variant_id = $resolvedVariant->id;
				$item->master_id = $resolvedVariant->master_id;
				$item->unit_price_gross = $item->unit_price_gross ?? $resolvedVariant->price;
				$cartModel->update($item->id, [
					'variant_id' => $resolvedVariant->id,
					'master_id' => $resolvedVariant->master_id,
					'unit_price_gross' => $item->unit_price_gross,
				]);
			}

			if (!$item->price && !empty($item->unit_price_gross)) {
				$item->price = $item->unit_price_gross;
			}
		}

		// termék fotó
		if(count($cartItems)) {
			$productIds = [];

			foreach($cartItems as $item) {
				if (!empty($item->variant_id) && isset($variantsById[(int)$item->variant_id])) {
					$productId = $variantsById[(int)$item->variant_id]->unas_id;
					if (!empty($productId)) {
						$productIds[] = $productId;
					}
				}
			}

			$productIds = array_values(array_unique($productIds));

			// a termékfotók lekérése
			$images = [];
			if (!empty($productIds)) {
				$imageModel = new \App\Models\ImageModel();
				$images = $imageModel
							->whereIn('product_id', $productIds)
							->findAll();
			}

			$imgMap = [];
			foreach($images as $img) {
				if(!isset($imgMap[$img->product_id])) {
					$imgMap[$img->product_id] = $img->filename;
				}
			}

			foreach($cartItems as $k => $item) {
				$imageKey = null;
				if (!empty($item->variant_id) && isset($variantsById[(int)$item->variant_id])) {
					$imageKey = $variantsById[(int)$item->variant_id]->unas_id;
				}
				$cartItems[$k]->image = ($imageKey && isset($imgMap[$imageKey])) ? $imgMap[$imageKey] : null;
			}			
			
		}

		// a kosár összesen
		$cartTotal = 0;
		if(count($cartItems)) {
			foreach($cartItems as $item) {
				if($item->price && $item->qty) {
					$cartTotal += (float)$item->price * (int)$item->qty;
				}
			}
		}
		$cartTotal = round($cartTotal, 2);

		// nettó ár
		$cartNetTotal = round($cartTotal / 1.27, 2);

		// áfa
		$cartVat = round($cartTotal - $cartNetTotal, 2);
		
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
		$variantId = (int)$this->request->getPost('variant_id');
		$qty = (int)$this->request->getPost('qty');

		$session_id = $this->session->get('cart_session_id');

		if(empty($sku) && $variantId < 1) {
			return $this->response->setStatusCode(400);
		}

		if($qty < 1) {
			$qty = 1;
		}

		// hozzáadás a kosárhoz
		$cartModel = new \App\Models\ShoppingCartModel();
		$variantModel = new \App\Models\ProductVariantModel();

		$variant = null;
		if ($variantId > 0) {
			$variant = $variantModel->find($variantId);
		}

		if (!$variant && !empty($sku)) {
			$variant = $variantModel->where('sku', $sku)->first();
		}

		if (!$variant) {
			return $this->response->setStatusCode(400)->setJSON([
				'success' => false,
				'message' => 'A kiválasztott termékváltozat nem található.'
			]);
		}

		if (!in_array($variant->state, [
			\App\Models\ProductVariantModel::STATE_INSTOCK,
			\App\Models\ProductVariantModel::STATE_BACKORDER,
			\App\Models\ProductVariantModel::STATE_INQUIRE,
		], true)) {
			return $this->response->setStatusCode(400)->setJSON([
				'success' => false,
				'message' => 'A kiválasztott termékváltozat jelenleg nem rendelhető.'
			]);
		}

		$variantId = (int)$variant->id;

		// létezik-e már a termék a kosárban
		$item = $cartModel->where('session_id', $session_id)
					  ->where('variant_id', $variantId)
						  ->first();

		if(!empty($item)) {

			// frissítjük a mennyiséget
			$item->qty = (int)$item->qty + $qty;

			$cartModel->update($item->id, (array)$item);

			$productName = $item->name;

		} else {
			$productName = $variant->name;
			$stateMap = [
				\App\Models\ProductVariantModel::STATE_INSTOCK => 'Raktárról',
				\App\Models\ProductVariantModel::STATE_BACKORDER => 'Rendelés',
				\App\Models\ProductVariantModel::STATE_INQUIRE => 'Ajánlatkérés',
				\App\Models\ProductVariantModel::STATE_INACTIVE => 'Inaktív',
			];

			$data = [
				'session_id' => $session_id,
				'master_id'  => $variant->master_id,
				'variant_id' => $variantId,
				'sku'        => $variant->sku,
				'legacy_sku' => $sku ?: null,
				'name'       => $variant->name,
				'price'      => (float)$variant->price,
				'unit_price_gross' => (float)$variant->price,
				'vat_rate'   => 27.00,
				'qty'        => $qty,
				'status'     => $stateMap[$variant->state] ?? 'Rendelés',
				'selected_options_json' => null,
			];

			$cartModel->insert($data);
			$item = $cartModel->find($cartModel->getInsertID());

		}     
		
		return $this->response->setStatusCode(200)->setJSON([
			'success' => true,
			'lineId' => $item->id ?? null,
			'message' => $productName . ' hozzáadva a kosárhoz.'
		]);

	}
	
	public function updateQty()
	{
		if(!$this->request->isAJAX()) {
			return $this->response->setStatusCode(400);
		}

		$lineId = (int)$this->request->getPost('line_id');
		$qty = (int)$this->request->getPost('qty');

		$session_id = $this->session->get('cart_session_id');

		if($lineId < 1 || $qty < 1) {
			return $this->response->setStatusCode(400);
		}

		$cartModel = new \App\Models\ShoppingCartModel();
		$item = $cartModel
					->where('session_id', $session_id)
					->where('id', $lineId)
					->first();

		if(empty($item)) {
			return $this->response->setStatusCode(400);
		}

		$cartModel->update($item->id, [
			'qty' => $qty
		]);

		return $this->response->setStatusCode(200)->setJSON([
			'success' => true,
			'message' => $item->name . ' mennyisége frissítve.'
		]);

	}

	public function remove()
	{
		if(!$this->request->isAJAX()) {
			return $this->response->setStatusCode(400);
		}

		$lineId = (int)$this->request->getPost('line_id');
		$variantId = (int)$this->request->getPost('variant_id');
		$sku = $this->request->getPost('sku');

		$session_id = $this->session->get('cart_session_id');

		if($lineId < 1 && $variantId < 1 && empty($sku)) {
			return $this->response->setStatusCode(400);
		}

		// tétel eltávolítása a kosárból
		$cartModel = new \App\Models\ShoppingCartModel();
		$query = $cartModel->where('session_id', $session_id);
		if ($lineId > 0) {
			$query->where('id', $lineId);
		} elseif ($variantId > 0) {
			$query->where('variant_id', $variantId);
		} else {
			$query->where('sku', $sku);
		}

		$item = $query->first();

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
