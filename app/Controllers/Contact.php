<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class Contact extends BaseController
{
    /**
	 * index
	 * 
	 * Kapcsolat
	 *
	 * @return void
	 */
	public function index()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Kapcsolat'),		
				'section' => 'contact'		
			],
			'body'	=> [
				'products' => product_options()
			]
        ];

		BuildPage::render('contact', $data);
    }
}
