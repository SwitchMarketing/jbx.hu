<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class QualityPolicy extends BaseController
{
    /**
	 * index
	 * 
	 * minőségbiztosítási nyilatkozat
	 *
	 * @return void
	 */
	public function index()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Minőségbiztosítási nyilatkozat'),		
				'section' => 'quality-policy'		
			]
        ];

		BuildPage::render('quality-policy', $data);
    }
}
