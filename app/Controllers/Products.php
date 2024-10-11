<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class Products extends BaseController
{
    /**
	 * xguard
	 * 
	 * Gépbiztonsági kerítés
	 *
	 * @return void
	 */
	public function xguard()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Axelent X-Guard - Gépbiztonsági kerítés'),		
				'section' => 'product'		
			],
			'body' => [ 
				'pdfs' => [
					[
                        'url'   => 'dummy.pdf',
                        'label' => 'X-Guard Brossúra'
                    ],
					[
                        'url'   => 'dummy.pdf',
                        'label' => 'X-Guard Brossúra'
                    ],
					[
                        'url'   => 'dummy.pdf',
                        'label' => 'X-Guard Brossúra'
                    ]
				]
			]
        ];

		BuildPage::render('xguard', $data);
    }

	/**
	 * xtray
	 * 
	 * Kábeltálca megoldások
	 *
	 * @return void
	 */
	public function xtray()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Axelent Wire Tray - Kábeltálca megoldások'),		
				'section' => 'product'		
			]
        ];

		BuildPage::render('xtray', $data);
    }

	/**
	 * xprotect
	 * 
	 * Ütközésvédelem
	 *
	 * @return void
	 */
	public function xprotect()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Axelent X-Protect - Ütközésvédelem'),		
				'section' => 'product'		
			]
            ];

		BuildPage::render('xprotect', $data);
    }

	/**
	 * xstore
	 * 
	 * Raktárbiztonsági megoldások
	 *
	 * @return void
	 */
	public function xstore()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Raktárbiztonsági megoldások'),		
				'section' => 'product'		
			]
        ];

		BuildPage::render('xstore', $data);
    }

	/**
	 * property
	 * 
	 * Ingatlan megoldások
	 *
	 * @return void
	 */
	public function property()
    {

		$data = [
			'header' => [
				'title'	  => page_title('Ingatlan megoldások'),		
				'section' => 'product'		
			]
            ];

		BuildPage::render('property', $data);
    }
}
