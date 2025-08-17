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
		$this->setVariables([
            ])
            ->setTitle('My Dashboard')
			->renderPage('dashboard');    		
	}

	public function updatesboard()
	{ 
		
		$this->output->cache(1);
        $this->load->model( 'm_applicant' );

        $applicantLogs   = $this->m_applicant->getAllLogs( [], 10);
        $status          = $this->m_applicant->status;
        $statusText      = $this->m_applicant->statusText;
        $statusColors    = $this->m_applicant->statusColors;
        
		$data['applicantLogs'] 	= $applicantLogs;
		$data['status'] 		= $status;
		$data['statusText'] 	= $statusText;
		$data['statusColors'] 	= $statusColors;
		$this->load->view('admin/updatesboard',$data);    		
	}

	function stats(){
		$this->output->cache(5);
        $this->load->model( 'm_applicant' );
		$this->load->view('admin/stats');
	}
}

/* End of file dashboard.php */
/* Location: ./app/controllers/dashboard.php */