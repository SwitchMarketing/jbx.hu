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
			],
			'body'	=> [
				'products' => product_options()
			]
		];

		BuildPage::render('privacy-policy', $data);
    }

	/**
	 * terms
	 *
	 * Általános szerződési feltételek
	 *
	 * @return void
	 */
	public function terms()
	{
		$data = [
			'header' => [
				'title'   => page_title('Általános szerződési feltételek'),
				'section' => 'terms'
			]
		];

		BuildPage::render('general-terms', $data);
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
			],
			'body'	=> [
				'products' => product_options()
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
			],
			'body'	=> [
				'products' => product_options()
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
			],
			'body'	=> [
				'products' => product_options()
			]
		];

		BuildPage::render('impressum', $data);
    }

}
