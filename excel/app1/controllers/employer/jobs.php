<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs extends Employer_Controller {
	
	const PAGE_ACCESS     = parent::PAGE_PRIVATE;
	const PER_PAGE        = 16;
	
	protected static $PAGINATION_HTML = [
		'pagination_open_tag'   => '<div class="paging">',
		'pagination_close_tag'  => '</div>',
		'first_open_tag'        => '<a href="{link}">',
		'first_close_tag'       => '</a>',
		'previous_open_tag'     => '<a href="{link}">',
		'previous_close_tag'    => '</a>',
		'digit_open_tag'        => '<a href="{link}">',
		'digit_close_tag'       => '</a>',
		'active_open_tag'       => '<span class="current">',
		'active_close_tag'      => '</span>',
		'next_open_tag'         => '<a href="{link}">',
		'next_close_tag'        => '</a>',
		'last_open_tag'         => '<a href="{link}">',
		'last_close_tag'        => '</a>',
	];
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		$this->load->model( 'm_job' );
	}
	
	public function all()
	{
		Pagination::init ( self::PER_PAGE );
		
		$this->load->model( 'm_job' );
		
		$options = [];
		$limit   = $offset = 0;
		$sort    = ['job_created', 'DESC'];
        
        //Filter reserved applicant
        $options['where'][] = [
            'job_employer =' => $_SESSION['employer']['user']['employer_id'],
        ];
		
		$limit  = Pagination::getPerPage();
		$offset = Pagination::getRecordCursor();
        
		$jobs      = ( new m_job )->getJobs( $options, $limit, $offset, $sort );
		$jobsCount = ( new m_job )->getJobsCount( $options );

		foreach ( $jobs as $jobId => $job ) {
			$counter      = ( new m_job )->getJobCounter( $jobId );
			$jobs[$jobId] = array_merge( $jobs[$jobId], $counter );
		}
		
		Pagination::setOptions([
			'total-records'     => $jobsCount,
			'html'              => self::$PAGINATION_HTML,
		]);
		
		$this->styles = [
			$this->getPath()['styles'] . 'pages/jobs.css',
		];
		
		$this->setVariables([
				'jobs'              => $jobs,
				'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('Job Orders')
			->renderPage('jobs');    		
	} 
	
}

/* End of file dashboard.php */
/* Location: ./app/controllers/employer/dashboard.php */