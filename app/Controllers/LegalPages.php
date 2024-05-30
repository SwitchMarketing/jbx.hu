<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class LegalPages extends BaseController
{
    /**
	 * exposure
	 * 
	 * érintettségi tájékoztató
	 *
	 * @return void
	 */
	public function exposure()
    {
		$data = [
			'header' => [
				'title'	  => page_title('Érintettségi tájékoztató'),		
				'section' => 'exposure'		
			]
		];

		BuildPage::render('exposure-note', $data);
    }

}
