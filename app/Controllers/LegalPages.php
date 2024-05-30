<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class LegalPages extends BaseController
{

	/**
	 * privacy
	 * 
	 * adatkezelés
	 *
	 * @return void
	 */
	public function privacy()
    {
		$data = [
			'header' => [
				'title'	  => page_title('Adatkezelés'),		
				'section' => 'home'		
			]
		];

		BuildPage::render('privacy-policy', $data);
    }

	/**
	 * cookies
	 * 
	 * Sütikezelés
	 *
	 * @return void
	 */
	public function cookies()
    {
		$data = [
			'header' => [
				'title'	  => page_title('Sütikezelés'),		
				'section' => 'cookies'		
			]
		];

		BuildPage::render('cookie-policy', $data);
    }

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

	/**
	 * impressum
	 * 
	 * Impresszum
	 *
	 * @return void
	 */
	public function impressum()
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
