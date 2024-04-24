<?php namespace App\Controllers;

use App\Libraries\BuildPage;
use Exception;

class Maintenance extends BaseController
{
		
	/**
	 * index
	 *
	 * Karbantartás oldal
	 * 
	 * @return void
	 */
	public function index()
	{
		if(!config( 'Config\\AppConfig' )->maintenanceMode)
        {
            return redirect()->to('/');
        }
		
		BuildPage::maintenance([
			'title' => page_title('Karbantartás')
		]);
	}

}
