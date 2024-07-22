<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class About extends BaseController
{
    /**
	 * index
	 * 
	 * Bemutatkozó
	 *
	 * @return void
	 */
	public function index()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Bemutatkozó'),		
				'section' => 'about'		
			]
            ];

		BuildPage::render('about', $data);
    }
}
