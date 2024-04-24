<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class PrivacyPolicy extends BaseController
{
    /**
	 * index
	 * 
	 * adatkezelés
	 *
	 * @return void
	 */
	public function index()
    {
		$data = [
			'header' => [
				'title'	  => page_title('Adatkezelés'),		
				'section' => 'home'		
			]
		];

		BuildPage::render('privacy-policy', $data);
    }

}
