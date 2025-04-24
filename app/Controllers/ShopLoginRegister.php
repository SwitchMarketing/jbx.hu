<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class ShopLoginRegister extends BaseController
{
    /**
	 * index
	 * 
	 * bejelentkezés / regisztráció
	 *
	 * @return void
	 */
	public function index()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Belépés'),		
				'section' => 'shop'		
			],
			'body'	=> [

                'breadcrumbs' => [

                    (object) [
                        'title' => 'Belépés',
                        'url'   => base_url('belepes')
                    ]

                ]

            ]
        ];

		BuildPage::render('shop-login-register', $data);

    }

}
