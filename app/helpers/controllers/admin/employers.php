<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Employers extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		
		$this->load->model( 'm_employer' );
	}
	
	public function index()
	{
		show_404();
	}
	
	public function all()
	{
		Pagination::init( 50 );
        
		$options = [];
		$limit = $offset = 0; 
		
		$employers      = $this->m_employer->getEmployers( $options, Pagination::getPerPage(), Pagination::getRecordCursor() );
		$employersCount = $this->m_employer->getEmployersCount( $options ); 
		
		Pagination::setOptions([
			'total-records' => $employersCount,
		]);
		
		$this->styles = [
			$this->getPath()['styles'] . 'pages/employers.css',
		];
		
		$this->scripts = [
			$this->getPath()['scripts'] . 'pages/employers.js',
		];
		//Load modal
		//$this->modalsTpl = 'employers.modal.php';
		
		$this->setVariables([
				'employers'	        => $employers,
				'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('All Employers')
			->renderPage('employers');
	}

	public function add()
	{
		//Form Submitted
		if ( isset( $_POST['employer'], $_POST['employer']['user'] ) ) {
			$_SESSION['post']['admin']['employers/add'] = $_POST;
		
			self::checkDataAdd();
			
			$employer = $this->m_employer->addEmployer();
			
			if ( ! empty( $employer ) ) {
				Message::addSuccess('New employer record has been added successfully!', false, 'Success');
				
				redirect( site_url( 'admin/employers/all?ref_emp='.$employer['employer_id'] ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', 'Oops!');
			redirect( site_url( 'admin/employers/all' ) );
			exit;
		}
		//endOf: Form Submitted

		//Get countries
		$this->load->model( 'm_country' );
		$countries = $this->m_country->getCountries();

		//Get marketing agencies
		$this->load->model( 'm_marketing_agency' );
		$agencies       = $this->m_marketing_agency->getMarketingAgencies();

		//Get marketing agencies
		$this->load->model( 'm_marketing_agent' );
        $agents         = $this->m_marketing_agent->getMarketingAgents();        

		$this->setVariables([
				'countries'	        => $countries,
                'agencies'          => $agencies,
                'agents'            => $agents,
			])
			->renderPage('employers/add', true);
	}

	public function review( $employerId )
	{	
		$employer = ( new m_employer )->getEmployerById( $employerId );

		//Form Submitted
		if ( isset( $_POST['employer'], $_POST['employer']['user'] ) ) {
			$employer = $_POST['employer'];
		
			$_SESSION['post']['admin']['employers/review'] = $_POST;
			
			$employer = $this->m_employer->updateEmployer( $employerId );
				
			if ( ! empty( $employer ) ) {
				Message::addSuccess( '<strong>'.$employer['employer_name'] .'</strong> has been updated', false, 'Success');
				
				redirect( site_url( 'admin/employers/all?ref_emp='.$employer['employer_id'] ) );
				exit;
			}
			
			//Message::addWarning('An unknown error has occur. Server not available. Please try again.', 'Oops!');
			redirect( site_url( 'admin/employers/all' ) );
			exit;	
		}
		//endOf: Form Submitted

		//Get countries
		$this->load->model( 'm_country' );
		$countries = $this->m_country->getCountries();

		//Get marketing agencies
		$this->load->model( 'm_marketing_agency' );
		$agencies       = $this->m_marketing_agency->getMarketingAgencies();

		//Get marketing agencies
		$this->load->model( 'm_marketing_agent' );
        $agents         = $this->m_marketing_agent->getMarketingAgents();        

		$this->setVariables([
				'employer'          => $employer,
				'countries'	        => $countries,
                'agencies'          => $agencies,
                'agents'            => $agents,
			])
			->renderPage('employers/review', true);
	}

	public function preview( $employerId )
	{
		$options = [];

		$this->load->model( 'm_employer');
        $employer  = ( new m_employer )->getEmployerById( $employerId );

		$options['where'][] = [
    		'job_employer' => (int) $employerId,
    	];

    	$this->load->model( 'm_job' );
		$jobs      = $this->m_job->getJobs( $options, ['employer_name', 'ASC'] );

		$this->setVariables([
                'employer'    => $employer,
				'jobs'        => $jobs,
			])
			->renderPage('employers/preview', true);
	}

	protected static function checkDataAdd()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/employers/all' );
		$employer 	= $_POST['employer'];
 
		if ( empty( $employer['name'] ) ) {
			$errors[] = '* <strong>Employer name</strong> is required.';
		}

		if ( empty( $employer['contact_person'] ) ) {
			$errors[] = '* <strong>Contact person</strong> is required.';
		}

		if ( empty( $employer['user']['name'] ) ) {
			$errors[] = '* <strong>Username</strong> is required.';
		}

		if ( empty( $employer['user']['password'] ) || empty( $employer['user']['password2'] ) ) {
			$errors[] = '* <strong>Password</strong> is required.';
		} else if ( $employer['user']['password'] != $employer['user']['password2']  ) {
			$errors[] = '* <strong>Password and confirmation password</strong> did not match.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
	
	protected static function checkDataEdit()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/employers/all' );
		$employer 	= $_POST['employer'];
 
		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
	
}

/* End of file applicants.php */
/* Location: ./app/controllers/admin/applicants.php */