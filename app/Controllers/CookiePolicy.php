<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class CookiePolicy extends BaseController
{
    /**
	 * index
	 * 
	 * Impresszum
	 *
	 * @return void
	 */
	public function index()
    {
		$data = [
			'header' => [
				'title'	  => page_title('Sütikezelés'),		
				'section' => 'cookies'		
			]
		];

		BuildPage::render('cookie-policy', $data);
    }

}
