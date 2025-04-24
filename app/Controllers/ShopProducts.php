<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

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

                ]

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
