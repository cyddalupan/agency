<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__.'/../../third_party/htmltopdf/html2pdf.class.php';

class Applicants extends Employer_Controller {
	
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
		$this->load->model( 'm_applicant' );
	}
    
    public function reserved()
	{
		Pagination::init ( self::PER_PAGE );
		
		$options = [];
		$limit   = $offset = 0;
        
        //Applicant selected
        if ( isset( $_GET['unselect'] ) && is_numeric( $_GET['unselect'] )  ) {
           $applicantId = (int) $_GET['unselect'];
           
           $applicant  = $this->m_applicant->getApplicantById( $applicantId );
           $unReserved = $this->m_applicant->unReserveApplicant( $applicantId, $this->user['employer_id'] );
           
           if ( $unReserved ) {
                Message::addInfo( '<strong>'.$applicant['applicant_first'].' '.$applicant['applicant_last'].'</strong> is unselected and now available.');
                redirect( site_url( 'employer/applicants/reserved' ) );
                exit;
           }
           
           Message::addDanger( '<strong>'.$applicant['applicant_first'].' '.$applicant['applicant_last'].'</strong> is not available right now, please choose another.' );
           redirect( site_url( 'employer/applicants/reserved' ) );
           exit;
        }
        
		$limit  = Pagination::getPerPage();
		$offset = Pagination::getRecordCursor();
		
		$options['where'][] = [
			'applicant_employer' => $_SESSION['employer']['user']['employer_id'],
		];
        
		$applicants      = $this->m_applicant->getReservedApplicants( $options, $limit, $offset );
		$applicantsCount = $this->m_applicant->getReservedApplicantsCount( $options );
        
		Pagination::setOptions([
			'total-records'     => $applicantsCount,
			'html'              => self::$PAGINATION_HTML,
		]);
        
        $this->styles = [
			$this->getPath()['styles'] . 'pages/applicants/reserved.css',
		];
        
		$this->setVariables([
				'applicants'        => $applicants,
				'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('Reserved Applicants')
			->renderPage('applicants/reserved');    		
	}
    
    public function selected()
    {
		Pagination::init ( self::PER_PAGE );
		
		$options = [];
		$limit   = $offset = 0;
        
		$limit  = Pagination::getPerPage();
		$offset = Pagination::getRecordCursor();
        
		$options['where'][] = [
			'applicant_employer' => $_SESSION['employer']['user']['employer_id'],
		];
		
		$applicants      = $this->m_applicant->getSelectedApplicants( $options, $limit, $offset );
		$applicantsCount = $this->m_applicant->getSelectedApplicantsCount( $options );
 
		Pagination::setOptions([
			'total-records'     => $applicantsCount,
			'html'              => self::$PAGINATION_HTML,
		]);
        
        $this->styles = [
			$this->getPath()['styles'] . 'pages/applicants/selected.css',
		];
        
		$this->setVariables([
				'applicants'        => $applicants,
				'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('Selected Applicants')
			->renderPage('applicants/selected');    		
	}

		public function line_up()
	{	
		$options = [];
		$limit   = $offset = 0;
		$sort    = ['applicant_created', 'DESC'];
        
        //Applicant selected
        if ( isset( $_GET['select'] ) && is_numeric( $_GET['select'] )  ) {
           $applicantId = (int) $_GET['select'];
           
           $applicant  = $this->m_applicant->getApplicantById( $applicantId );
           $isReserved = $this->m_applicant->isReserved( $applicantId, $this->user['employer_id'] );

           if ( $isReserved ) {
                Message::addWarning( '<strong>'.$applicant['applicant_first'].' '.$applicant['applicant_last'].'</strong> is already on your reservation list.' );
                redirect( site_url( 'employer/applicants/line-up' ) );
                exit;
           }
           
           $reserved = $this->m_applicant->reserveApplicant( $applicantId, $this->user['employer_id'] );
           
           if ( $reserved ) {
                Message::addSuccess( '<strong>'.$applicant['applicant_first'].' '.$applicant['applicant_last'].'</strong> is now on your reservation list. <a href="'.site_url( 'employer/applicants/reserved' ).'">See reserved applicants</a>.', false, 'Success' );
                redirect( site_url( 'employer/applicants/line-up' ) );
                exit;
           }
           
           Message::addDanger( '<strong>'.$applicant['applicant_first'].' '.$applicant['applicant_last'].'</strong> is not available right now, please choose another.' );
           redirect( site_url( 'employer/applicants/line-up' ) );
           exit;
        }

        $options['where'][] = [
			'applicant_employer' => $_SESSION['employer']['user']['employer_id'],
		];
        
		$applicants      = ( new m_applicant )->getLineUpApplicants( $options, $limit, $offset );
	
		$this->styles = [
			$this->getPath()['styles'] . 'pages/applicants/search.css',
		];
		
		$this->setVariables([
				'applicants'        => $applicants,
			])
			->setTitle('Line Up Applicants')
			->renderPage('applicants/line-up');    		
	}

	public function line_up2()
    {
		Pagination::init ( self::PER_PAGE );
		
		$options = [];
		$limit   = $offset = 0;
        
		$limit  = Pagination::getPerPage();
		$offset = Pagination::getRecordCursor();
        
		$options['where'][] = [
			'applicant_employer' => $_SESSION['employer']['user']['employer_id'],
		];
		
		$applicants      = ( new m_applicant )->getLineUpApplicants( $options, $limit, $offset );
		$applicantsCount = ( new m_applicant )->getLineUpApplicantsCount( $options );
 
		Pagination::setOptions([
			'total-records'     => $applicantsCount,
			'html'              => self::$PAGINATION_HTML,
		]);
        
        $this->styles = [
			$this->getPath()['styles'] . 'pages/applicants/selected.css',
		];
        
		$this->setVariables([
				'applicants'        => $applicants,
				'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('Line Up Applicants')
			->renderPage('applicants/line-up');    		
	}
    
	
	public function search()
	{
		Pagination::init ( self::PER_PAGE );
		
		$this->load->model( 'm_position' );
		$this->load->model( 'm_country' );
		
		$options = [];
		$limit   = $offset = 0;
		$sort    = ['applicant_created', 'DESC'];
        
        //Filter reserved applicant
        $options['where'][] = [
            'applicant_status' => $this->m_applicant->status['Available'],
        ];

        $options['where'][] = "(SELECT COUNT(*)  FROM `employer_reservation` AS `es` WHERE `es`.`reservation_applicant` = `applicant_id`) = 0";
        
        //Applicant selected
        if ( isset( $_GET['select'] ) && is_numeric( $_GET['select'] )  ) {
           $applicantId = (int) $_GET['select'];
           
           $applicant  = $this->m_applicant->getApplicantById( $applicantId );
           $isReserved = $this->m_applicant->isReserved( $applicantId, $this->user['employer_id'] );

           if ( $isReserved ) {
                Message::addWarning( '<strong>'.$applicant['applicant_first'].' '.$applicant['applicant_last'].'</strong> is already on your reservation list.' );
                redirect( site_url( 'employer/applicants/search' ) );
                exit;
           }
           
           $reserved = $this->m_applicant->reserveApplicant( $applicantId, $this->user['employer_id'] );
           
           if ( $reserved ) {
                Message::addSuccess( '<strong>'.$applicant['applicant_first'].' '.$applicant['applicant_last'].'</strong> is now on your reservation list. <a href="'.site_url( 'employer/applicants/reserved' ).'">See reserved applicants</a>.', false, 'Success' );
                redirect( site_url( 'employer/applicants/search' ) );
                exit;
           }
           
           Message::addDanger( '<strong>'.$applicant['applicant_first'].' '.$applicant['applicant_last'].'</strong> is not available right now, please choose another.' );
           redirect( site_url( 'employer/applicants/search' ) );
           exit;
        }
	    
        //Search Applicants
		if ( isset( $_GET['q'], $_GET['position'], $_GET['country'] ) ) {
			list( $query, $position, $country ) = [ $_GET['q'], (int) $_GET['position'], (int) $_GET['country'] ];
			
			if ( $position > 0) {
				$options['where'][] = [
					'applicant_preferred_position' => $position,
				];
			}
			
			if ( $country > 0) {
				$options['where'][] = [
					'applicant_preferred_country' => $country,
				];
			}
            
			if ( ! empty( $query ) ) {
				$query = addslashes( $query );
				$options['where'][] = 
					sprintf( "(`applicant_first` LIKE '%%%s%%' OR `applicant_last` LIKE '%%%s%%' OR CONCAT(`applicant_first`, ' ', `applicant_middle`, ' ', `applicant_last`) LIKE '%%%s%%')",
						$query, $query, $query );
			}
            
            $sort = [ 'applicant_updated', 'DESC' ];
		}
		
		$limit  = Pagination::getPerPage();
		$offset = Pagination::getRecordCursor();
        
		$applicants      = $this->m_applicant->getApplicants( $options, $limit, $offset, $sort );
		
		$applicantsCount = $this->m_applicant->getApplicantsCount( $options );
		$categories      = $this->m_position->getActivePositionsGroupByCategory();
		$positions       = $this->m_position->getPositions();
		$countries       = $this->m_country->getCountries();

		parse_str( $_SERVER['QUERY_STRING'], $queryString);

		Pagination::setOptions([
			'total-records'     => $applicantsCount,
			'html'              => self::$PAGINATION_HTML,
		]);
	
		$this->sidebarTpl = 'applicants/search.sidebar.php';
		
		$this->styles = [
			$this->getPath()['styles'] . 'pages/applicants/search.css',
		];
		
		$this->setVariables([
				'applicants'        => $applicants,
				'categories'        => $categories,
				'positions'         => $positions,
				'countries'         => $countries,
				'queryString'       => $queryString,
				'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('Search Applicants')
			->renderPage('applicants/search');    		
	}
    
    public function resume( $applicantId )
    {
        if ( empty( $applicantId ) ) { 
            show_404(); 
        }
        
        $applicant = $this->m_applicant->getApplicantById( $applicantId );
        $resume    = [];
        
        //Get resume
        $filesOptions['where'][] = [
            'file_type' => 'Resume',
        ];
        
        $files = $this->m_applicant->getApplicantFiles( $applicantId );
        
        if ( isset( $files['Resume'] ) ) {
        	$resume = $files['Resume'];
        }
        
        $this->styles[] = $this->getPath()['styles'] . 'pages/applicants/resume.css';
        
        $this->headerTpl = '';
        $this->menuTpl   = '';
        $this->footerTpl = '';
        
        $this->setVariables([
                'applicant'        => $applicant,
                'resume'           => $resume,
            ])
            ->setTitle( $applicant['applicant_first'].' '.$applicant['applicant_last'] )
            ->renderPage('applicants/resume');
    }

    public function pdf( $applicantId )
    {
    	if ( empty( $applicantId ) ) { 
            show_404(); 
        }
        
        $applicant = ( new m_applicant )->getApplicantById( $applicantId );
        $wholeBody = ( new m_applicant )->getApplicantFileByType( $applicantId, 'Whole Body Picture' );
        
        $this->headerTpl = '';
        $this->menuTpl   = '';
        $this->footerTpl = '';
        
        $this->setVariables([
                'applicant'        => $applicant,
                'wholeBody'        => $wholeBody,
            ]);
        
        ob_start();
        $this->renderPage('applicants/pdf', true);
        $content = ob_get_clean();

		try {
		    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
		    $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
		    $html2pdf->Output('export-pdf-'.time().'.pdf');
		} catch( HTML2PDF_exception $e ) {
		    echo $e;
		    exit;
		}

    }
	
}

/* End of file applicants.php */
/* Location: ./app/controllers/employer/applicants.php */