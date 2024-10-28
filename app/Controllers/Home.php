<?php

namespace App\Controllers;

use App\Libraries\BuildPage;
use Exception;
use UtmCookie\UtmCookie;
use App\Libraries\Mailer;

class Home extends BaseController
{
    /**
	 * index
	 * 
	 * főoldal
	 *
	 * @return void
	 */
	public function index()
    {

		$data = [
			'header' => [
				'title'	  => page_title(),		
				'section' => 'home'		
			],
			'body'	=> [
				'products' => product_options()
			]
		];

		// UTM paraméterek mentése
		UtmCookie::setLifetime(new \DateInterval('P3M'));
		UtmCookie::init();

		BuildPage::render('home', $data);
    }

	/**
	 * submit
	 *
	 * a kapcsolatfelvétel beküldése
	 * 
	 * @return void
	 */
	public function submit()
	{
		// Check for AJAX request.
		if ($this->request->isAJAX())
		{
			$response = (object) [
				'success'  => false,
				'message'  => '',
				'token'	   => csrf_hash()
			];
			
			try 
			{	

				$post = $this->request->getPost();

				$validation = \Config\Services::validation();
				
				$this->_set_rules($validation);

				//az ürlap adatok mentése munkamenet változóba
				$this->session->set('contactData', $post);

				$errors = [];

				// név vagy email cím ellenőrzése
				if(empty($post['email']) && empty($post['phone_number'])) {
					$errors['emailphone'] = 'Az <span>email cím</span> vagy <span>telefonszám</span> megadása kötelező';
				}

				// az email cím ellenőrzése ha ki van töltve
				if(!empty($post['email']) && !filter_var($post['email'],FILTER_VALIDATE_EMAIL)) {
					$errors['email'] = 'Érvénytelen <span>email cím</span>';
				}

				// a többi úrlap mező ellenőrzése
				if (! $validation->run($post) ) {
					$errors = array_merge($errors, $validation->getErrors());										
				}					

				// hibaüzenet ha vannak úrlap hibák
				if( count($errors) )
					throw new Exception( view('validation_errors_list', ['errors' => $errors]) );
				
				// az adatok mentése
				$rec = [
					'name' 		 => $post['name'],
					'email' 	 => $post['email'] ?? '',
					'phone' 	 => $post['phone_number'] ?? '',
					'products' 	 => implode(', ', array_unique($post['products'])),
                    'message' 	 => $post['message'] ?? '',        
					'utm_source' => UtmCookie::get('utm_source')            
				];

				$offer = new \App\Models\OfferRequestModel();
				if( !$offer->save($rec) )
					throw new Exception( view('validation_errors_list', ['errors' => ['Hiba az adatok mentésekor!']]) );
				
				// a fájlok feltöltése és az adatok mentése
				if ($files = $this->request->getFiles()) {

					$fm = new \App\Models\FileModel();
					$offer_id = $offer->getInsertID();
					
					$path 	= WRITEPATH . 'uploads';

					foreach ($files['photos'] as $file) {

						if ( $file->isValid() ) {
							$fName 	= $file->getRandomName();
							if($file->move($path, $fName)) 
							{
								// a fájl adatok rögzítése az ügyfélhez
								$fm->save([
									'offer_id' 	=> $offer_id,
									'filename'	=> $fName
								]);
							}
						}

					}

				}
					
				// mehet az email
				// * Háttérben megy a feltöltés, így ezt most nem kell
				// Mailer::contact($rec);   

				//menjen egy köszönő email az ügyfélnek
				// ! átmenetileg kikapcsolva, visszapattannak az emailek
				if( !empty($rec['email']) )
					Mailer::thankYou($post);

				//munkamanet változó és átirányítás a köszönő oldalra
				$this->session->setFlashdata('contactSuccess', '1');
				
				$response->success = true;
				$response->title = 'Sikeres kapcsolatfelvétel!';
				$response->message = 'Hamarosan keresni fogjuk a megadott elérhetőségeken.';	
				$response->redirect = '/sikeres-kapcsolatfelvetel';

			}
			catch (Exception $e)
			{
				$response->message = $e->getMessage();
			}
			
			return $this->response
						->setStatusCode($response->success ? 200 : 500)
						->setJSON($response);
		}
		else 
		{
			throw new Exception('Nem AJAX kérés!');
		}
	}

	/**
	 * success
	 *
	 * sikeres jelentkezés
	 * 
	 * @return void
	 */
	public function success()
	{
		//ha nincs munkamenet változó az kapcsolatfelvételről
		//visszairányitjuk a főoldalra
		if(!$this->session->has('contactSuccess'))
			return redirect()->to('/');   
		
		$data = [
			'header' => [
				'section'	 => 'contact',				
				'title'		 => page_title('Sikeres kapcsolatfelvétel'),				
				'og_title'	 => page_title('Sikeres kapcsolatfelvétel')
			]
		];

		BuildPage::render('contact-success', $data);
		
	}

	/**
	 * @param mixed $validation
	 * 
	 * @return object
	 */
	private function _set_rules($validation) {

		$rules = [

			'name' => [
				'label'  => 'név',
				'rules'  => 'required',
				'errors' => [
					'required' => 'A <span>{field}</span> nem lehet üres',
				],
			],

			'email' => [
				'label'  => 'email cím',
				'rules'  => 'permit_empty'				
			],

			'phone_number' => [
				'label'  => 'telefonszám',
				'rules'  => 'permit_empty'				
			],
			
			'products' => [
				'label'  => 'termékcsalád',
				'rules'  => 'required',
				'errors' => [
					'required' => 'Kerjük válassz termékcsaládot',
				],
			],
			
			'photos' => [
				'label'  => 'Fotó a telepítés helyéről',
				'rules'  => 'permit_empty|mime_in[photos,image/png,image/jpeg,image/jpg,image/heif,application/pdf]', //uploaded[photo]|
				'errors' => [
					'uploaded' => 'Kérjük töltse fel a {field}',
					'mime_in' => 'Érvénytelen fájl formátum',
				],
			]
		];

		$rules['privacy'] = [
				'label'  => 'adatvédelmi nyilatkozat',
				'rules'  => 'required',
				'errors' => [
					'required' => 'Nem fogadtad el az <span>{field}</span>-ot',
				]
			];

		$validation->setRules($rules);

		return $validation;

	}


	/**
	 * page404
	 *
	 * 404 hiba oldal
	 * 
	 * @return void
	 */
	public function page404()
    {

		$data = [
			'header' => [
				'section'	=> 'notfound'
			]
		];

		$this->response->setStatusCode(404);
		BuildPage::render('page404', $data);
    }
	
}
