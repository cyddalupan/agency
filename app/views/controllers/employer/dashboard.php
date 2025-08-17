<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Employer_Controller {
	const PAGE_ACCESS = parent::PAGE_PRIVATE;

	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
	}
	
	public function index()
	{ 
		$this->scripts = [
			$this->getPath()['plugins'] . 'charts/sparkline/jquery.sparkline.js',
			$this->getPath()['plugins'] . 'charts/sparkline/sparkline-init.js',
			$this->getPath()['scripts'] . 'pages/dashboard.js',
		];
		
		$this->setTitle('My Dashboard')
			->renderPage('dashboard'); 	
	}
}

/* End of file dashboard.php */
/* Location: ./app/controllers/employer/dashboard.php */