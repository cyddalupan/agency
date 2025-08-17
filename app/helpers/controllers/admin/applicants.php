<?php //-->
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Applicants  extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		
		$this->load->model( 'm_applicant' );
	}
	
	public function index()
	{
		show_404();
	}
	
	public function all()
	{   
        Pagination::init( 20 );
          
        $recordScope = 'All';
        $options     = [];
        $sort        = ['applicant_created', 'DESC'];

        if ( isset( $_GET['archive'] ) && is_numeric( $_GET['archive'] ) ) {
        	$applicantId = (int) $_GET['archive'];

        	$applicant = ( new m_applicant )->archiveApplicant( $applicantId );

        	Message::addInfo( 'Applicant#' . str_pad( $applicantId, 7, '0', STR_PAD_LEFT ) .' has been archived successfully.' );

        	redirect( site_url( 'admin/applicants/all' ) );
        	exit;
        }

        if ( isset( $_GET['unselect'] ) && is_numeric( $_GET['unselect'] ) ) {
        	$applicantId = (int) $_GET['unselect'];

        	$applicant = ( new m_applicant )->moveToAvailable( [ $applicantId ] );

        	Message::addInfo( 'Applicant#' . str_pad( $applicantId, 7, '0', STR_PAD_LEFT ) .' has been unselected.' );

        	redirect( site_url( 'admin/applicants/all' ) );
        	exit;
        }

        //Archive soft copy documents
        if ( isset( $_GET['ref_form'], $_GET['action'], $_GET['fId'] ) 
            && $_GET['ref_form'] == 'documents'
            && $_GET['action'] == 'del_file'
            && is_numeric( $_GET['fId'] )
            ) {
            
            $fileId = $_GET['fId'];
            
            $archived  = ( new m_applicant )->archiveApplicantFile( $applicantId, $fileId );
            $applicant = ( new m_applicant )->getApplicantById( $applicantId );
            
            if ( $archived ) {
                Message::addInfo( 'File #'.str_pad( $fileId, 7, '0', STR_PAD_LEFT).' has been archived.' );
                redirect( site_url( 'admin/applicants/all' ) );
			    exit;
            }
            
            Message::addWarning('An error occur. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/applicants/all' ) );
			exit;
        }
        
        if ( isset( $_GET['status'] ) && isset( $this->m_applicant->status[$_GET['status']] ) ) {
            $recordScope = $_GET['status'];
            $options['where'][] = [
                'applicant_status' => ( new m_applicant )->status[$_GET['status']],
            ];
        }
        
        if ( isset( $_GET['search'] ) ) {
        	$search = $_GET['search'];

        	$applicants      = ( new m_applicant )->searchApplicants( Pagination::getPerPage(), Pagination::getRecordCursor() );
        	$applicantsCount = ( new m_applicant )->searchApplicantsCount();
        } else {
        	$applicants      = ( new m_applicant )->getApplicants( $options, Pagination::getPerPage(), Pagination::getRecordCursor(), $sort );
        	$applicantsCount = ( new m_applicant )->getApplicantsCount( $options );	
        }

        Pagination::setOptions([
			'total-records' => $applicantsCount,
		]);

		$this->load->model('m_employer');
		$employers = ( new m_employer )->all();

		$this->load->model( 'm_position');
        $categories = ( new m_position )->getActivePositionsGroupByCategory();

		$this->load->model( 'm_country');		
		$countries  = ( new m_country )->getCountries();

		$post = isset( $_POST ) ? $_POST : [];
		$get  = isset( $_GET ) ? $_GET : [];
        
		$this->styles = [
			$this->getPath()['styles'] . 'pages/applicants.css',
		];
        
        $this->scripts = [
            $this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			//$this->getPath()['plugins'] . 'bootbox/bootbox.js',
			$this->getPath()['scripts'] . 'pages/applicants.js',
		];
        
        $this->setSideBarClass('menu-compact');
		
        //Load modal
		$this->modalsTpl = 'applicants.modal.php';
        
		$this->setVariables([
                'recordScope'       => $recordScope,
				'applicants'	    => $applicants,
				'employers'         => $employers,
				'categories'        => $categories,
				'countries'         => $countries,
				'post'              => $post,
				'get'               => $get,
				'status'            => ( new m_applicant )->status,
				'statusText'	    => ( new m_applicant )->statusText,
				'statusColors'	    => ( new m_applicant )->statusColors,
                'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('All applicants')
			->renderPage('applicants');
	}

	public function deployed()
	{   
        Pagination::init( 50 );
          
        $options     = [];
        $sort        = ['applicant_updated', 'DESC'];

        if ( isset( $_GET['archive'] ) && is_numeric( $_GET['archive'] ) ) {
        	$applicantId = (int) $_GET['archive'];

        	$applicant = ( new m_applicant )->archiveApplicant( $applicantId );

        	Message::addInfo( 'Applicant#' . str_pad( $applicantId, 7, '0', STR_PAD_LEFT ) .' has been archived successfully.' );

        	redirect( site_url( 'admin/applicants/all' ) );
        	exit;
        }

        //Archive soft copy documents
        if ( isset( $_GET['ref_form'], $_GET['action'], $_GET['fId'] ) 
            && $_GET['ref_form'] == 'documents'
            && $_GET['action'] == 'del_file'
            && is_numeric( $_GET['fId'] )
            ) {
            
            $fileId = $_GET['fId'];
            
            $archived  = ( new m_applicant )->archiveApplicantFile( $applicantId, $fileId );
            $applicant = ( new m_applicant )->getApplicantById( $applicantId );
            
            if ( $archived ) {
                Message::addInfo( 'File #'.str_pad( $fileId, 7, '0', STR_PAD_LEFT).' has been archived.' );
                redirect( site_url( 'admin/applicants/all' ) );
			    exit;
            }
            
            Message::addWarning('An error occur. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/applicants/all' ) );
			exit;
        }
        
        $options['where'][] = [
            'applicant_status' => ( new m_applicant )->status['Deployed'],
        ];
        
        if ( isset( $_GET['search'] ) ) {
        	$search = $_GET['search'];

        	$applicants      = ( new m_applicant )->searchApplicants( Pagination::getPerPage(), Pagination::getRecordCursor() );
        	$applicantsCount = ( new m_applicant )->searchApplicantsCount();
        } else {
        	$applicants      = ( new m_applicant )->getApplicants( $options, Pagination::getPerPage(), Pagination::getRecordCursor(), $sort );
        	$applicantsCount = ( new m_applicant )->getApplicantsCount( $options );	
        }

        Pagination::setOptions([
			'total-records' => $applicantsCount,
		]);

		$this->load->model( 'm_position');
        $categories = ( new m_position )->getActivePositionsGroupByCategory();

		$this->load->model( 'm_country');		
		$countries  = ( new m_country )->getCountries();

		$post = isset( $_POST ) ? $_POST : [];
		$get  = isset( $_GET ) ? $_GET : [];
        
		$this->styles = [
			$this->getPath()['styles'] . 'pages/applicants.css',
		];
        
        $this->scripts = [
            $this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			//$this->getPath()['plugins'] . 'bootbox/bootbox.js',
			$this->getPath()['scripts'] . 'pages/applicants.js',
		];
        
        $this->setSideBarClass('menu-compact');
		
        //Load modal
		$this->modalsTpl = 'applicants.modal.php';
        
		$this->setVariables([
				'applicants'	    => $applicants,
				'categories'        => $categories,
				'countries'         => $countries,
				'post'              => $post,
				'get'               => $get,
				'statusText'	    => ( new m_applicant )->statusText,
				'statusColors'	    => ( new m_applicant )->statusColors,
                'paginationHTML'    => Pagination::gener