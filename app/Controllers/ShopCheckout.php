<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

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

		$data = [
			'header' => [
				'title'	  => page_title('Pénztár'),
				'section' => 'shop'
			],
			'body'	=> [

                'breadcrumbs' => [

                    (object) [
                        'title' => 'Pénztár',
                        'url'   => base_url('penztar')
                    ]

                ]

            ]
        ];

		BuildPage::render('shop-checkout', $data);

    }
	

}
