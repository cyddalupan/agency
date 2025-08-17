<?php //-->
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Applicants  extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();

		$this->load->driver('cache');
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		
		$this->load->model( 'm_applicant' );
		$this->load->model( 'cyd_currency' );
		$this->load->model( 'Cyd_Applicants_Alphatomo' );
		$this->load->model( 'Custom_Fields' );
		$this->load->model( 'Custom_Fields_Value' );
			$this->load->model( 'Cyd_Applicants_Alphatomo' );
		$this->load->model( 'Cyd_Survey_Alphatomo' );
	}
	
	public function index()
	{
		show_404();
	}
	

	public function all(){
		//STOP ALL CODES REDIRECT to new APPLICANTS
		redirect( site_url( 'admin/dashboard#/applicants/1' ) );


		if ( isset( $_GET['archive'] ) && is_numeric( $_GET['archive'] ) ) {
        	$applicantId = (int) $_GET['archive'];

        	$applicant = ( new m_applicant )->archiveApplicant( $applicantId );

        	Message::addInfo( 'Applicant#' . str_pad( $applicantId, 7, '0', STR_PAD_LEFT ) .' has been archived successfully.' );

        	redirect( site_url( 'admin/applicants/all' ) );
        	exit;
        }

		$this->styles = [
			$this->getPath()['styles'] . 'pages/applicants.css',
		];

        //Load modal
		$this->modalsTpl = 'applicants.modal.php';

        $this->scripts = [
            $this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			//$this->getPath()['plugins'] . 'bootbox/bootbox.js',
			$this->getPath()['scripts'] . 'pages/applicants.js',
		];

		$this->setVariables([
			'statusText'	    => ( new m_applicant )->statusText,
			'statusColors'	    => ( new m_applicant )->statusColors,
		])
			->setTitle('All applicants')
			->renderPage('all_applicants');
	}
	
public function quick_search(){

	$keyword = $_GET['keyword'];
	
	
	$route = site_url().'page/applicants/quick_search/'.$keyword;
	
	$this->setVariables([
		'route'	=> $route
	])
		->setTitle('Quick Search')
		->renderPage('laravelframe');
}

	public function review_single($applicant_id){
    	$data['pageTitle'] = 'Applicant Review';
		$data['applicant_id'] = $applicant_id;

		$this->load->view('header',$data);
		$this->load->view('applicant-review-no-logout',$data);
		$this->load->view('footer',$data);
	}
	
	public function all2()
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
        
        $searchEmployer = '';
        if ( isset( $_GET['search'] ) ) {
        	$search = $_GET['search'];
        	if ( $search['employer'] > 0 ) {
        		$searchEmployer = $search['employer'];
        	}
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
        
        //inserting multiple employer to applicants array
        foreach ($applicants as $applicant_key => $applicant) {
        	$applicants[$applicant_key]["employer_names"] = ( new m_applicant )->cyd_get_multiple_employer( $applicant['applicant_id'] );
        	if($applicants[$applicant_key]["employer_names"] == ''){
        		$applicants[$applicant_key]["employer_names"] = $applicants[$applicant_key]["employer_name"];
        	}
        	if(
        		($applicant['applicant_status'] == 5) &&
        		($applicant['applicant_employer'] != 0)
        	){
	        	$applicants[$applicant_key]["remove_employer_link"] =  '<br/><a href="'.site_url('admin/applicants/cyd_remove_lineups/'.$applicant['applicant_id']).'" style="cursor:pointer;" class="label label-danger">Remove Employer</a>';
	        }else{
	        	$applicants[$applicant_key]["remove_employer_link"] =  '';
	        }
        }
        $appTotalExp = array();
        //get total working experience
		foreach ($applicants as $key => $applicantValue) {	
			$workExp = ( new m_applicant )->getApplicantWorkExperiences( $applicantValue['applicant_id'] );
			$appTotalExp[$applicantValue['applicant_id']] = 0;
			//work experience total
			foreach ($workExp as $key => $ExpValue) {
				$date1 = date_parse($ExpValue['experience_from']);
				$date2 = date_parse($ExpValue['experience_to']);
				if (
					($date1["error_count"] == 0 && checkdate($date1["month"], $date1["day"], $date1["year"])) &&
					($date2["error_count"] == 0 && checkdate($date2["month"], $date2["day"], $date2["year"]))
				){
					$date1 = new DateTime($ExpValue['experience_from']);
					$date2 = new DateTime($ExpValue['experience_to']);
					$diff = $date1->diff($date2);
					$appTotalExp[$applicantValue['applicant_id']] = $appTotalExp[$applicantValue['applicant_id']] + (($diff->format('%y') * 12) + $diff->format('%m'));
				}
			}
		}       

		$subpositions = ( new m_applicant )->cyd_get_all_sub_position();
		$applicants_raw = ( new m_applicant )->cyd_get_applicants_raw();
		$applicant_certificate_raw = ( new m_applicant )->cyd_get_applicant_certificate_raw();
		
		$this->setVariables([
                'recordScope'       => $recordScope,
                'appTotalExp'		=> $appTotalExp,
				'applicants'	    => $applicants,
				'applicants_raw'	=> $applicants_raw,
				'searchEmployer'	=> $searchEmployer,
				'applicant_certificate_raw'	=> $applicant_certificate_raw,
				'employers'         => $employers,
				'categories'        => $categories,
				'countries'         => $countries,
				'subpositions'      => $subpositions,
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

	public function cyd_remove_lineups($applicant_id){
		$applicant_data['applicant_employer'] = 0;
		$applicant_data['applicant_status'] = 0;
		$this->db->where('applicant_id', $applicant_id);
		$this->db->update('applicant', $applicant_data);
		
		( new m_applicant )->addLog( 'Applicant was Removed', $applicant_id, 0, 0, date( 'Y-m-d', time() ) ); 
		
		$this->db->delete('multiple_lineups', array('applicant_id' => $applicant_id)); 
		redirect('admin/applicants/all', 'refresh');
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
		$subpositions = ( new m_applicant )->cyd_get_all_sub_position();
		$applicants_raw = ( new m_applicant )->cyd_get_applicants_raw();
		$applicant_certificate_raw = ( new m_applicant )->cyd_get_applicant_certificate_raw();
		
		$this->setVariables([
				'applicants'	    => $applicants,
				'applicants_raw'	=> $applicants_raw,
				'applicant_certificate_raw'	=> $applicant_certificate_raw,
				'subpositions'      => $subpositions,
				'categories'        => $categories,
				'countries'         => $countries,
				'post'              => $post,
				'get'               => $get,
				'statusText'	    => ( new m_applicant )->statusText,
				'statusColors'	    => ( new m_applicant )->statusColors,
                'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('Deployed Applicants')
			->renderPage('applicants/deployed');
	}
    
    public function expired_reservations()
	{
        Pagination::init( 50 );
        
        $options = [];
        $limit   = Pagination::getPerPage();
        $offset  = Pagination::getRecordCursor();
        
		$applicants       = ( new m_applicant )->getExpiredReservedApplicants( $options, $limit, $offset );
		$applicantsCount  = ( new m_applicant )->getExpiredReservedApplicantsCount( $options );
        
		if ( isset( $_POST['select'] ) ) {
			$applicantIds = [];

            if ( isset( $_POST['select']['all'] ) ) {
            	foreach ( $applicants as $applicant) {
            		$applicantIds[] = $applicant['applicant_id'];
            	}
            } else {
            	foreach ( $_POST['select'] as $key => $id ) {
	            	if ( isset( $_POST['select']['all'] ) ) {
	            		continue;
	            	}

	            	$applicantIds[] = $id;
	            }
            }

			$options['where'][] = [
				'applicant_status' => ( new m_applicant )->status['Reserved'],
			];

            $movedCount = ( new m_applicant )->moveToAvailable( $applicantIds, $options );

            Message::addSuccess( '<strong>'.number_format( $movedCount ) . '</strong> applicants has been moved back to list of available.');
            redirect( site_url( 'admin/applicants/expired-reservations' ) );
            exit;
        }

        if ( isset( $_POST['reservation']['reservation_id'] ) && $_POST['reservation']['reservation_id'] > 0 ) {

        	$reservationId  = $_POST['reservation']['reservation_id'];
        	$daysToExtend = $_POST['reservation']['extend'];
        	$remarks      = $_POST['reservation']['remarks'];

        	$extended =  ( new m_applicant )->extendReserveApplicant( $reservationId, $daysToExtend, $remarks );

        	Message::addSuccess( 'Extended of <strong>'.number_format( $daysToExtend ).'</strong> days.', false, 'Success');
            redirect( site_url( 'admin/applicants/expired-reservations' ) );
            exit;
        }

        Pagination::setOptions([
			'total-records'   => $applicantsCount,
		]);

		$this->load->model('m_employer');
		$employers = ( new m_employer )->all();
        
		$this->styles = [
			$this->getPath()['styles'] . 'pages/applicants/expired-reservations.css',
			$this->getPath()['styles'] . 'pages/applicants.css',
		];
        
        $this->scripts = [
            $this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/applicants/expired-reservations.js',
			$this->getPath()['scripts'] . 'pages/applicants.js',
		];
		
		//Load modal
		$this->modalsTpl = 'applicants.modal.php';

		$this->setVariables([
				'applicants'	    => $applicants,
				'employers'         => $employers,
                'paginationHTML'    => Pagination::generateHTML(),
			    'paginationCounter' => Pagination::getCounters(),
				'status'            => ( new m_applicant )->status,
				'statusText'	    => ( new m_applicant )->statusText,
				'statusColors'	    => ( new m_applicant )->statusColors,
			])
			->setTitle('Expired Reservations')
			->renderPage('applicants/expired-reservations');
	}

	public function expired_medical()
	{
        Pagination::init( 50 );
        
        $options = [];
        $limit   = Pagination::getPerPage();
        $offset  = Pagination::getRecordCursor();
        
		$applicants       = ( new m_applicant )->getExpiredMedicalApplicants( $options, $limit, $offset );

		$applicantsCount  = ( new m_applicant )->getExpiredMedicalApplicantsCount( $options );
       
        Pagination::setOptions([
			'total-records'   => $applicantsCount,
		]);

		$this->load->model('m_employer');
		$employers = ( new m_employer )->all();
  $this->scripts = [
            $this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			//$this->getPath()['plugins'] . 'bootbox/bootbox.js',
			$this->getPath()['scripts'] . 'pages/applicants.js',
		];

		//Load modal
		$this->modalsTpl = 'applicants.modal.php';

		$this->setVariables([
				'applicants'	    => $applicants,
				'employers'         => $employers,
                'paginationHTML'    => Pagination::generateHTML(),
			    'paginationCounter' => Pagination::getCounters(),
				'status'            => ( new m_applicant )->status,
				'statusText'	    => ( new m_applicant )->statusText,
				'statusColors'	    => ( new m_applicant )->statusColors,
			])
			->setTitle('Expired Medical')
			->renderPage('applicants/expired-medical');
	}

	public function expired_visa()
	{
        Pagination::init( 50 );
        
        $options = [];
        $limit   = Pagination::getPerPage();
        $offset  = Pagination::getRecordCursor();
        
		$applicants       = ( new m_applicant )->getExpiredVisaApplicants( $options, $limit, $offset );

		$applicantsCount  = ( new m_applicant )->getExpiredVisaApplicantsCount( $options );
       
        Pagination::setOptions([
			'total-records'   => $applicantsCount,
		]);

		$this->load->model('m_employer');
		$employers = ( new m_employer )->all();
  $this->scripts = [
            $this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			//$this->getPath()['plugins'] . 'bootbox/bootbox.js',
			$this->getPath()['scripts'] . 'pages/applicants.js',
		];

		//Load modal
		$this->modalsTpl = 'applicants.modal.php';

		$this->setVariables([
				'applicants'	    => $applicants,
				'employers'         => $employers,
                'paginationHTML'    => Pagination::generateHTML(),
			    'paginationCounter' => Pagination::getCounters(),
				'status'            => ( new m_applicant )->status,
				'statusText'	    => ( new m_applicant )->statusText,
				'statusColors'	    => ( new m_applicant )->statusColors,
			])
			->setTitle('Expired Medical')
			->renderPage('applicants/expired-visa');
	}

	public function expired_passports()
	{
         Pagination::init( 50 );
        
        $options = [];
        $limit   = Pagination::getPerPage();
        $offset  = Pagination::getRecordCursor();
        
		$applicants       = ( new m_applicant )->getExpiredPassportsApplicants( $options, $limit, $offset );

		$applicantsCount  = ( new m_applicant )->getExpiredPassportsApplicantsCount( $options );
       
        Pagination::setOptions([
			'total-records'   => $applicantsCount,
		]);

		$this->load->model('m_employer');
		$employers = ( new m_employer )->all();

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

		//Load modal
		$this->modalsTpl = 'applicants.modal.php';

		$this->setVariables([
				'applicants'	    => $applicants,
				'employers'         => $employers,
                'paginationHTML'    => Pagination::generateHTML(),
			    'paginationCounter' => Pagination::getCounters(),
				'status'            => ( new m_applicant )->status,
				'statusText'	    => ( new m_applicant )->statusText,
				'statusColors'	    => ( new m_applicant )->statusColors,
			])
			->setTitle('Expired Passports')
			->renderPage('applicants/expired-passports');
	}

	public function add()
	{
		$this->load->model( 'm_position');
		$this->load->model( 'm_country');
        $this->load->model( 'm_recruitment_agent');
		
		$customFields = ( new Custom_Fields )->get_all();

		//Form Submitted
		if ( isset( $_POST['applicant'], $_POST['applicant']['basic'], $_POST['applicant']['education'] ) ) {

			$_SESSION['post']['admin']['applicants/add'] = $_POST;
		
			self::checkDataAdd();
			
			$applicant = ( new m_applicant )->addApplicant();
			$this->Cyd_Applicants_Alphatomo->updateApplicantProfile( $applicant['applicant_id'] );
			$this->Cyd_Survey_Alphatomo->updateApplicantProfile( $applicant['applicant_id']  );	
			
			//check if applicant already exist
			$this->db->flush_cache();
			$this->db->where('applicant_first', $_POST['applicant']['basic']['first']);
			$this->db->where('applicant_last', $_POST['applicant']['basic']['last']);
			$this->db->where('applicant_birthdate', $_POST['applicant']['basic']['birthdate']);
			$this->db->from('applicant');
			//$existApplicant = $this->db->count_all_results();
			//if($existApplicant > 0){
			//	$this->session->set_flashdata('cyd_error_msg', 'Applicant Already Exist!');
			//	redirect( site_url( 'admin/applicants/add' ) );
			//}


			//update custom fields
			$this->Custom_Fields_Value->update_value( $applicant['applicant_id'], $customFields  );
			
			if ( !empty( $applicant ) ) {
				Message::addModalSuccess('New applicant record has been added successfully!', 'Success');
                Message::addSuccess('New applicant record has been added successfully. <a href="'.site_url( 'admin/applicants/all/' ).'">See list of applicants</a>.', false, 'Success');
				
				//redirect( site_url( 'admin/applicants/detail/{$applicant[applicant_id]}/' . strSlug($applicant['applicant_first'].' '.$applicant['applicant_middle'].' '.$applicant['applicant_last']) ) );
				redirect( site_url( 'admin/applicants/add' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', 'Oops!');
			redirect( site_url( $this->controllerDir . 'applicants/add' ) );
			exit;
		}
		//endOf: Form Submitted
		
		$categories = $this->m_position->getActivePositionsGroupByCategory();
		$countries  = $this->m_country->getCountries();
        $agents     = $this->m_recruitment_agent->getRecruitmentAgents();
        $this->load->model( 'm_employer');
        $employers  = ( new m_employer )->getEmployers();
        
		
		$post = isset( $_SESSION['post']['admin']['applicants/add'] ) ? $_SESSION['post']['admin']['applicants/add'] : [];

		$trainingBranches = ( new m_applicant )->allTrainingBranches();
		
		$currencies = ( new Cyd_Currency )->get_all();

		$this->styles[] = $this->getPath()['styles'] . 'pages/applicants/add.css';
		$this->scripts  = [			
			$this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/applicants/add.js',
		];



		$this->setVariables([
				'categories' => $categories,
				'countries'  => $countries,
				'currencies' => $currencies,
                'agents'     => $agents,
				'post'       => $post,
				'customFields' => $customFields,
				'trainingBranches' => $trainingBranches,
				'employers'       => $employers,
			])
			->setTitle('Add New Applicant')
			->renderPage('applicants/add');
	}

	public function review( $applicantId ) 
	{
		$applicantId = (int) $applicantId;

		//cache option status
		if ( ! $optionalStatus = $this->cache->file->get('optionalStatus'))
		{
			$query = $this->db->get('optional_statuses');
			$optionalStatus = $query->result();
			$this->cache->file->save('optionalStatus', $optionalStatus, 1300);
		}

		if ( isset( $_POST['applicant'], $_FILES['applicant']['name']['photo'] ) ) {
			$uploaded = (  new m_applicant )->changePhoto( $_POST['applicant']['applicant_id'] );  

			if ( $uploaded === true ) {
				Message::addSuccess( 'Applicant photo has been saved.' );
			}

				if ( in_array( $_SESSION['admin']['user']['user_type'], [9,10]) ){ 
				redirect( site_url( 'admin/applicants/all2' ) );
				}
				else { 
				redirect( site_url( 'admin/applicants/all' ) );
				}
			exit;
		}

		$customFields = ( new Custom_Fields )->get_all();
        
        //Update Applicant
		if ( isset( $_POST['applicant'], $_POST['flag'], $_POST['applicant_id'] ) ) {
            
			//update custom fields
			$this->Custom_Fields_Value->update_value( $applicantId, $customFields  );
			
            $ajaxResponse = [
            	'status'  => false,
            	'message' => '',
            	'scope'   => '',
            ];

			$_SESSION['post']['admin']['applicants/all'] = $_POST;

			switch ( $_POST['flag'] ) {
					case 'profile':
					$this->Cyd_Applicants_Alphatomo->updateApplicantProfile( $applicantId  );
					$this->Cyd_Survey_Alphatomo->updateApplicantProfile( $applicantId  );
					$applicant = ( new m_applicant )->updateApplicantProfile( $applicantId );
					
                    $ajaxResponse['status']  = ! empty( $applicant );
                    $ajaxResponse['message'] = ! empty( $applicant ) ? "Saved!\n\nPlease reload the window." : '';
                    $ajaxResponse['scope']   = 'profile';

					break;
				
                case 'certificate':
					$applicant = ( new m_applicant )->updateApplicantCertificates( $applicantId );
					
					if ( ! empty( $applicant ) ) {
						$ajaxResponse['status']  = true;
						$ajaxResponse['message'] = "Saved!\n\nPlease reload the window.";
					}

					$ajaxResponse['scope']   = 'certificate';
					
					break;
                
                case 'requirement':

					$applicant = ( new m_applicant )->updateApplicantRequirements( $applicantId );
					
					$ajaxResponse['status']  = ! empty( $applicant );
					$ajaxResponse['message'] = ! empty( $applicant ) ? "Saved!\n\nPlease reload the window." : '';
					$ajaxResponse['scope']   = 'requirement';

					break;
                    
                case 'file':
                    $files = $_FILES['applicant'];

                    $file = [
                        'name'     => $files['name']['file']['file'],
                        'type'     => $files['type']['file']['file'],
                        'tmp_name' => $files['tmp_name']['file']['file'],
                        'error'    => $files['error']['file']['file'],
                        'size'     => $files['size']['file']['file'],
                    ];
                    $cydoutput = ( new m_applicant )->uploadApplicantFile( $applicantId, $file );
                    
                    $ajaxResponse['status']  = ! empty( $cydoutput['file_path'] );
                    $ajaxResponse['message'] = "File successfully uploaded!\n\nPlease reload the window.";
                    $ajaxResponse['scope']   = 'file';
                    $ajaxResponse['name']   = $files['name']['file']['file'];
                    $ajaxResponse['path']   = $cydoutput['file_path'];
                    
                    break;
                                       
                case 'status':
					$applicant = ( new m_applicant )->updateApplicantStatus( $applicantId );
					
					$ajaxResponse['status']  = ! empty( $applicant );
					$ajaxResponse['message'] = "Saved!\n\nPlease reload the window.";
					$ajaxResponse['scope']   = 'status';
					
					break;
			}

			echo json_encode( $ajaxResponse );
            exit;
		}
		
		$this->load->model( 'm_position');
		$this->load->model( 'm_country');
        $this->load->model( 'm_recruitment_agent');
        $this->load->model( 'm_employer');
        $this->load->model( 'm_job');
        
		$applicant             = ( new m_applicant )->getApplicantById( $applicantId );	
		$applicant_certificate_direct = ( new m_applicant )->getApplicantCertificateById( $applicantId );		
		$applicant_requirements_direct = ( new m_applicant )->getApplicantRequirementsById( $applicantId );
		$applicant_raw		   = ( new m_applicant )->getApplicantRawById( $applicantId ); 		
        $applicant_alphatomo   = ( new Cyd_Applicants_Alphatomo )->getApplicantsAlphatomoById( $applicantId );		
			$survey_alphatomo   	= ( new Cyd_Survey_Alphatomo )->getSurveyAlphatomoById( $applicantId );	
        $applicant['files']    = ( new m_applicant )->getApplicantFiles( $applicantId );
        $applicant['logs']     = ( new m_applicant )->getApplicantLogs( $applicantId );
        $applicant['balance']  = ( new m_applicant )->getBalance( $applicantId );
        $applicant['status']  = ( new m_applicant )->getStatus( $applicantId );
		$categories            = ( new m_position )->getActivePositionsGroupByCategory();
		$countries             = ( new m_country )->getCountries();
        $agents                = ( new m_recruitment_agent )->getRecruitmentAgents();
        $employers             = ( new m_employer )->getEmployers();
        $documentTypes         = ( new m_applicant )->fileTypes;
        $trainingBranches      = ( new m_applicant )->allTrainingBranches();
        $skill_cyd      		= ( new m_applicant )->get_skill_cyd( $applicantId );
		

        $jobOffers = [];
        
        if ( $applicant['applicant_employer'] ) {
            $jobOffersOptions['where'][] = ['job_employer' => $applicant['applicant_employer']];
            $jobOffers          = ( new m_job )->getJobs( $jobOffersOptions );
        }
 
		$post = isset( $_SESSION['post']['admin']['applicants/review'] ) ? $_SESSION['post']['admin']['applicants/review'] : [];
        
        //Remain the available document types to upload
        foreach ( $applicant['files'] as $file ) {
            if ( ! isset( $documentTypes[ $file['file_type'] ] ) ) {
                continue;
            }
            
            unset( $documentTypes[ $file['file_type'] ] );
        }
        
      //  if ( $applicant['applicant_position_type'] != 'Household' ) {
       //     unset( $documentTypes['Whole Body Picture'] );
       // }
        
        ini_set('memory_limit', '100M');
        ini_set('post_max_size', '100M');
        ini_set('upload_max_filesize', '100M');

        $this->styles = [
        	//$this->getPath()['plugins'] . 'lightbox-2.7.1/css/lightbox.css',
			$this->getPath()['styles'] . 'pages/applicants/review.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'jquery.form/jquery.form.min.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			//$this->getPath()['plugins'] . 'lightbox-2.7.1/js/lightbox.min.js',
			$this->getPath()['scripts'] . 'pages/applicants/review.js',
		];

		$currencies = ( new Cyd_Currency )->get_all();

		//clean the date
		$applicant_requirements_direct->flight_date = $this->cydCleanDate($applicant_requirements_direct->flight_date);
		
		$this->setVariables([
				'applicant'    => $applicant,
				'applicant_raw' => $applicant_raw,
				'applicant_alphatomo' 	=> $applicant_alphatomo,
				'survey_alphatomo' 		=> $survey_alphatomo,
				'applicant_certificate_direct'    => $applicant_certificate_direct,
				'applicant_requirements_direct'    => $applicant_requirements_direct,
				'currencies'    => $currencies,
				'categories'   => $categories,
				'countries'    => $countries,
                'agents'       => $agents,
                'employers'    => $employers,
                'jobOffers'    => $jobOffers,
                'documentTypes'=> $documentTypes,
				'status'       => ( new m_applicant )->status,
				'statusText'   => ( new m_applicant )->statusText,
				'statusColors' => ( new m_applicant )->statusColors,
				'post'         => $post,
				'customFields' => $customFields,
				'trainingBranches'	=> $trainingBranches,
				'optionalStatus' => $optionalStatus,
				'skill_cyd'		=> $skill_cyd,
				

			])
			->setTitle( $applicant['applicant_name'] )
			->renderPage('applicants/review', true); 
	}

	private	function cydCleanDate($testdate){
		if(isset($testdate) && (date('ymd',strtotime($testdate)) < 0)){
			return '';
		}else{
			return $testdate;
		}
	}

	public function for_deployment()
	{   
        Pagination::init( 50 );
          
        $options     = [];
        $sort        = ['applicant_created', 'DESC'];

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
            'applicant_status' => ( new m_applicant )->status['For Deployment'],
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
                'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('For Deployed Applicants')
			->renderPage('applicants/for-deployment');
	}

	public function selected()
	{   
        Pagination::init( 50 );
          
        $options     = [];
        $sort        = ['applicant_created', 'DESC'];

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
            'applicant_status' => ( new m_applicant )->status['Selected'],
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
        	$subpositions = ( new m_applicant )->cyd_get_all_sub_position();
		$applicants_raw = ( new m_applicant )->cyd_get_applicants_raw();
		$applicant_certificate_raw = ( new m_applicant )->cyd_get_applicant_certificate_raw();	
		
		$this->setVariables([
				'applicants'	    => $applicants,
				'applicants_raw'	=> $applicants_raw,
				'applicant_certificate_raw'	=> $applicant_certificate_raw,
				'subpositions'      => $subpositions,
				'categories'        => $categories,
				'countries'         => $countries,
				'post'              => $post,
				'get'               => $get,
				'statusText'	    => ( new m_applicant )->statusText,
				'statusColors'	    => ( new m_applicant )->statusColors,
                'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('Selected Applicants')
			->renderPage('applicants/selected');
			
			
	}

	public function search()
	{
		$this->load->model( 'm_position');
		$this->load->model( 'm_country');
        $this->load->model( 'm_recruitment_agent');
		
		$categories = $this->m_position->getActivePositionsGroupByCategory();
		$countries  = $this->m_country->getCountries();
        $agents     = $this->m_recruitment_agent->getRecruitmentAgents();
		
		$this->styles[] = $this->getPath()['styles'] . 'pages/applicants/add.css';
		$this->scripts  = [			
			$this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/applicants/add.js',
		];

		$this->setVariables([
				'categories' => $categories,
				'countries'  => $countries,
                'agents'     => $agents,
				'post'       => [],
			])
			->setTitle('Avanced Search of applicants')
			->renderPage('applicants/search');
	}

	public function send_applicants()
	{
		if ( ! isset( $_POST['app-id'], $_POST['emp-id'], $_POST['return-url'] ) ) {
			show_404();
			exit;
		}

		$post = $_POST;

		$applicantIds = $post['app-id'];
		$employerId   = $post['emp-id'];
		$returnUrl  = ! empty( $post['return-url'] ) ? $post['return-url'] : null;

		$this->load->model( 'm_employer' );
		$employer = ( new m_employer )->find( $employerId );

		$applicants = ( new m_applicant )->getApplicantsByIds( $post['app-id'] );

		( new m_applicant )->lineUpApplicants( $applicantIds, $employerId );

		$message = "The following applicants has been lined up to '".$employer['employer_name']."'.<br>";

		foreach ( $applicants as $applicant ) {
			$message .= '&bull;&nbsp;'.$applicant['applicant_first'].' '.$applicant['applicant_last'].'<br>';
		}

		Message::addSuccess( $message, false, 'Success!' );

		if ( ! is_null( $returnUrl ) ) {
			redirect( $returnUrl );
			exit;
		}

		redirect( site_url( 'admin/applicants/all' ) );
		exit;
	}
	
	protected static function checkDataAdd()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/applicants/add' );
		$applicant 	= $_POST['applicant'];
 
		if ( empty( $applicant['preferred-position']  ) ) {
			$errors[] = '* <strong>Preferred position</strong> is required.';
		}
		
		if ( empty( $applicant['preferred-country']  ) ) {
			$errors[] = '* <strong>Preferred country</strong> is required.';
		}

		if ( empty( $applicant['expected-salary']  ) ) {
			$errors[] = '* <strong>Expected salary</strong> is required.';
		}

		if ( empty( $applicant['basic']['first']  ) || empty( $applicant['basic']['last']  ) ) {
			$errors[] = '* <strong>First</strong> and <strong>last name</strong> is required.';
		}

		if ( empty( $applicant['basic']['birthdate']  ) ) {
			$errors[] = '* <strong>Date of birth</strong> is required.';
		} else {

			//Birthdate format: mm-dd-yyyy
			list( $year, $month, $day ) = explode( '-', $applicant['basic']['birthdate'] );

			if ( ! checkdate( $month, $day, $year) ) {
				$errors[] = '* <strong>Date of birth</strong> format is invalid.';
			}
		}
		
		if ( empty( $applicant['date-applied']  ) ) {
			$errors[] = '* <strong>Date applied</strong> is required.';
		} else {
			//Birthdate format: mm-dd-yyyy
			list( $year, $month, $day ) = explode( '-', $applicant['date-applied'] );

			if ( ! checkdate( $month, $day, $year) ) {
				$errors[] = '* <strong>Date applied</strong> format is invalid.';
			}
		}

		if ( empty( $applicant['basic']['address']  ) || empty( $applicant['basic']['address']  ) ) {
			$errors[] = '* <strong>Address</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
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
	
	function deny_hit($applicantId){

		echo ( new m_applicant )->denyHit($applicantId);

		echo "<script>window.close();</script>";
	}
	
}

/* End of file applicants.php */
/* Location: ./app/controllers/admin/applicants.php */