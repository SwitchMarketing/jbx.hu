<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

use App\Models\ProductModel;

class ShopProducts extends BaseController
{
    /**
	 * index
	 * 
	 * termék lista
	 *
	 * @return void
	 */
	public function index()
    {

		// termékek lekérése
		// a lapozóhoz szükséges paraméterek
		$itemsPerPage = 12;

		$page = $this->request->getGet('page') ?? 1;
		$limit = $this->request->getGet('limit') ?? 24;

		$start = ($page * $itemsPerPage) - $itemsPerPage;

		// a termékek lekérése
		// a termékek modelje
		$model = model(ProductModel::class);

		// a termékek lekérése
		$items = $model->findAll($limit, $start);

		// az összes termék lekérése
		$total = $model->countAllResults(false);

		// a lapozó
		$pager = service('pager');

		$shop = (object) [
            'items' => $items->data,
            'total' => $items->total,
			'links' => $pager->makeLinks($page, $limit, $total, 'shop')
        ];

		
		$data = [
			'header' => [
				'title'	  => page_title('Termékek'),		
				'section' => 'shop'		
			],
			'body'	=> [

                'breadcrumbs' => [

                    (object) [
                        'title' => 'Termékek',
                        'url'   => base_url('termekek')
                    ]

				],

				'shop' => $shop							

            ]
        ];

		BuildPage::render('shop-products-grid', $data);

    }

	/**
	 * product
	 * 
	 * termék aloldal
	 *
	 * @return void
	 */
	public function product($id = null)
    {

		$data = [
			'header' => [
				'title'	  => page_title('Fosroc - Termékek'),		
				'section' => 'shop'		
			],
			'body'	=> [

                'breadcrumbs' => [

                    (object) [
                        'title' => 'Termékek',
                        'url'   => base_url('termekek')
					],

					(object) [
                        'title' => 'Fosroc Galvafroid - 400ml',
                        'url'   => base_url('termekek/fosroc')
                    ]

                ]

            ]
        ];

		BuildPage::render('shop-product', $data);

    }

}
