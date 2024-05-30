<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class Impressum extends BaseController
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
				'title'	  => page_title('Impresszum'),		
				'section' => 'impressum'		
			]
		];

		BuildPage::render('impressum', $data);
    }

}
