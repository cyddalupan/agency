<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
        $this->load->model( 'm_job' );
	}
	
	public function index()
	{
		show_404();
	}
	
	public function all()
	{
		Pagination::init( 50 );
        
		$options['where'][] = [
			'job_status' => 1,
		];
        
        if ( isset( $_GET['filter']['eid'] ) ) {
        	$options['where'][] = [
        		'job_employer' => (int) $_GET['filter']['eid'],
        	];
        }

		$jobs      = $this->m_job->getJobs( $options, Pagination::getPerPage(), Pagination::getRecordCursor(), ['employer_name', 'ASC'] );
		$jobsCount = $this->m_job->getJobsCount( $options ); 

		$this->load->model( 'm_fee' );
		$dollar     = ( new m_fee )->getDollarExchange();

		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();
		
		Pagination::setOptions([
			'total-records' => $jobsCount,
		]);
		
		$this->styles = [
			$this->getPath()['styles'] . 'pages/jobs.css',
		];
		
		$this->scripts = [
			$this->getPath()['scripts'] . 'pages/jobs.js',
		];
       
         //Load modal
		$this->modalsTpl = 'jobs.modal.php';
        
		$this->setVariables([
				'jobs'	            => $jobs,
				'employers'         => $employers,
				'dollar'            => $dollar,
				'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('Jobs')
			->renderPage('jobs');
	}
	
    public function add()
	{
		$this->load->model( 'm_employer');
        $this->load->model( 'm_position');
        $this->load->model( 'm_fee' );
        
        //Form submitted
        if ( isset( $_POST['job'] ) ) {
            
            //self::checkDataAdd();

            $job = $this->m_job->addJob();
            
            if ( !empty( $job ) ) {
				Message::addSuccess('New job record has been added successfully. <a href="'.site_url( 'admin/jobs/all' ).'">See job list</a>.', false, 'Success');
				
				redirect( site_url( 'admin/jobs/all' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', 'Oops!');
			redirect( site_url( 'admin/jobs/all' ) );
			exit;
        } 

        $employers  = ( new m_employer )->getEmployers();
        $categories = ( new m_position )->getActivePositionsGroupByCategory();
        $fees       = ( new m_fee )->getFees();
        $dollar     = ( new m_fee )->getDollarExchange();

		$this->scripts = [			
			$this->getPath()['scripts'] . 'pages/jobs/add.js',
		];
        
		$this->setVariables([
				'employers'   => $employers,
				'categories'  => $categories,
                'fees'        => $fees,
                'dollar'      => $dollar,
			])
			->setTitle('Post new jobs')
			->renderPage('jobs/add', true);
	}
    
    public function review( $jobId )
	{
		//Form submitted
        if ( isset( $_POST['job'] ) ) {
            
            //self::checkDataAdd();

            $job = $this->m_job->updateJob( $jobId );
            
            if ( !empty( $job ) ) {
				Message::addSuccess('Job #'.str_pad( $jobId, 6, '0', STR_PAD_LEFT ).' record has been updated.', false, 'Success');
				
				redirect( site_url( 'admin/jobs/all' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', 'Oops!');
			redirect( site_url( 'admin/jobs/all' ) );
			exit;
        } 
        
        $job        = ( new m_job )->getJobById( $jobId );

        $this->load->model( 'm_employer');
        $employers  = ( new m_employer )->getEmployers();

        $this->load->model( 'm_position');
        $categories = ( new m_position )->getActivePositionsGroupByCategory();

        $this->load->model( 'm_fee' ); 
        $fees       = ( new m_fee )->getFees();

		$this->setVariables([
                'job'         => $job,
				'employers'   => $employers,
				'categories'  => $categories,
                'fees'        => $fees,
			])
			->renderPage('jobs/review', true);
	} 

	public function archive()
	{
		$jobId = intval( $_GET['job_id'] );

		if ( ( new m_job )->archive( $jobId ) ) {
			Message::addSuccess( 'Successfully deleted.' );

			redirect( site_url( 'admin/jobs/all' ) );
			exit;
		}

		Message::addWarning( 'There was an error while trying to process your request. Please try again.', 'Oops!' );

		redirect( site_url( 'admin/jobs/all' ) );
		exit;
	}
	
}

/* End of file jobs.php */
/* Location: ./app/controllers/admin/jobs.php */