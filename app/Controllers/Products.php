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
				'types' => $this->xGuardTypes(),
				'products' => product_options(1),
				'pdfs' => [
					[
                        'url'   => 'Axelent_X-Guard_catalog.pdf',
                        'label' => 'Axelent X-Guard - Gépbiztonsági kerítés'
                    ],
					[
                        'url'   => 'Axelent.pdf',
                        'label' => 'Axelent - Ipari gépbiztonság'
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
			],
			'body' => [ 
				'products' => product_options(2),
				'pdfs' => [
					[
                        'url'   => 'Axelent_Wire_Tray.pdf',
                        'label' => 'Axelent Wire Tray - Kábeltálca rendszerek'
                    ],
					[
                        'url'   => 'Axelent.pdf',
                        'label' => 'Axelent - Ipari gépbiztonság'
                    ]
				]
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
			],
			'body' => [ 
				'products' => product_options(3),
				'pdfs' => [
					[
                        'url'   => 'Axelent_X-Protect_catalog.pdf',
                        'label' => 'Axelent X-Protect - Ütközésvédelem'
                    ],
					[
                        'url'   => 'Axelent_X-Protect_sales.pdf',
                        'label' => 'Axelent X-Protect Rugalmas Ütkozésvédők'
                    ],
					[
                        'url'   => 'Axelent.pdf',
                        'label' => 'Axelent - Ipari gépbiztonság'
                    ]
				]
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
			],
			'body' => [ 
				'products' => product_options(4),
				'pdfs' => [
					[
                        'url'   => 'Axelent_X-Store.pdf',
                        'label' => 'Axelent X-Store - Raktárelválasztó megoldások'
                    ],
					[
                        'url'   => 'Axelent_X-Rail_fall_protection.pdf',
                        'label' => 'Axelent X-Rail - Leesésvédelmi megoldások'
                    ],
					[
                        'url'   => 'Axelent.pdf',
                        'label' => 'Axelent - Ipari gépbiztonság'
                    ]
				]
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
			],
			'body' => [ 
				'products' => product_options(5),
				'pdfs' => [
					[
                        'url'   => 'Axelent_BikeRacks.pdf',
                        'label' => 'Axelent - Kerékpártárolási megoldások'
                    ],
					[
                        'url'   => 'Axelent_Storerooms.pdf',
                        'label' => 'Axelent - Rácsos és lemezes tárolók'
                    ],
					[
                        'url'   => 'Axelent.pdf',
                        'label' => 'Axelent - Ipari gépbiztonság'
                    ]
				]
			]
        ];

		BuildPage::render('property', $data);
    }

	/**
	 * xGuardTypes
	 *
	 * @return object
	 */
	private function xGuardTypes() {

		return (object) [

			'legend' => [
				'title' => '',
				'description' => '',
				'rows' => [
					'Rácsméret',
					'Biztonsági távolság',
					'Padló rögzítési pontok (oszloponként)',
					'Panel szélesség változatok',
					'Kerítés magasság változatok',
					'Panel keret / profil méret',
					'Oszlop méretei'
				]
			],
			'lite' => [
				'title' => 'Lite',
				'description' => 'Könnyűipari biztonsági rácspanel rendszer',
				'img' => img_src('x-guard/lite-uj.webp'),
				'rows' => [
					[
						'label' => 'Rácsméret',
						'value' => '50x30 mm'
					],
					[
						'label' => 'Biztonsági távolság',
						'value' => '200 mm'
					],
					[
						'label' => 'Padló rögzítési pontok (oszloponként)',
						'value' => '2'
					],
					[
						'label' => 'Panel szélesség változatok',
						'value' => '13'
					],
					[
						'label' => 'Kerítés magasság változatok',
						'value' => '3'
					],
					[
						'label' => 'Panel keret / profil méret',
						'value' => 'függőleges 19x19 mm, vízszintes 15x15 mm'
					],
					[
						'label' => 'Oszlop méretei',
						'value' => '50 x 50 x 1 mm'
					]
				]
			],
			'classic' => [
				'title' => 'Classic',
				'description' => 'Széleskörűen használható, rugalmasan alakítható ipari biztonsági kerítés',
				'img' => img_src('x-guard/classic.webp'),
				'rows' => [
					[
						'label' => 'Rácsméret',
						'value' => '50x30 mm'
					],
					[
						'label' => 'Biztonsági távolság',
						'value' => '200 mm'
					],
					[
						'label' => 'Padló rögzítési pontok (oszloponként)',
						'value' => '2'
					],
					[
						'label' => 'Panel szélesség változatok',
						'value' => '13'
					],
					[
						'label' => 'Kerítés magasság változatok',
						'value' => '5'
					],
					[
						'label' => 'Panel keret / profil méret',
						'value' => 'függőleges 30x20 mm, vízszintes 25x15 mm'
					],
					[
						'label' => 'Oszlop méretei',
						'value' => '50 x 50 x 1,5 mm'
					]
				]
			],
			'premium' => [
				'title' => 'Premium',
				'description' => 'Robotkarok és gyorsan mozgó elemekhez ideális megoldás, ahol számít a kis alapterület',
				'img' => img_src('x-guard/premium.webp'),
				'rows' => [
					[
						'label' => 'Rácsméret',
						'value' => '50x20 mm'
					],
					[
						'label' => 'Biztonsági távolság',
						'value' => '120 mm'
					],
					[
						'label' => 'Padló rögzítési pontok (oszloponként)',
						'value' => '4'
					],
					[
						'label' => 'Panel szélesség változatok',
						'value' => '6'
					],
					[
						'label' => 'Kerítés magasság változatok',
						'value' => '2'
					],
					[
						'label' => 'Panel keret / profil méret',
						'value' => 'függőleges 30x20 mm, vízszintes 25x15 mm'
					],
					[
						'label' => 'Oszlop méretei',
						'value' => '70 x 70 x 2 mm'
					]
				]
			]

		];

	}
}
