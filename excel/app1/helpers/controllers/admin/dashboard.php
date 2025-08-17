<?php
use \Application\Message as Message;

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Admin_Controller {
	const PAGE_ACCESS = parent::PAGE_PRIVATE;

	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
	}
	
	public function index()
	{ 
        $this->load->model( 'm_applicant' );

        $applicantLogs   = $this->m_applicant->getAllLogs( [], 50);
        $status          = $this->m_applicant->status;
        $statusText      = $this->m_applicant->statusText;
        $statusColors    = $this->m_applicant->statusColors;
        
		$this->scripts = [            
            $this->getPath()['plugins'] . 'charts/morris/raphael-2.0.2.min.js',
            $this->getPath()['plugins'] . 'charts/morris/morris.js"',

            $this->getPath()['plugins'] . 'charts/flot/jquery.flot.js',
			$this->getPath()['plugins'] . 'charts/sparkline/jquery.sparkline.js',
			$this->getPath()['plugins'] . 'charts/sparkline/sparkline-init.js',
			$this->getPath()['scripts'] . 'pages/dashboard.js',
		];

		$this->setVariables([
                'applicantLogs' => $applicantLogs,
                'status'        => $status,
                'statusText'    => $statusText,
                'statusColors'  => $statusColors,
            ])
            ->setTitle('My Dashboard')
			->renderPage('dashboard');    		
	}
}

/* End of file dashboard.php */
/* Location: ./app/controllers/dashboard.php */