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
	

}
