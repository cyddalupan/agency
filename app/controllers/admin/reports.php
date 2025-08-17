<?php //-->
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports  extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );

		$this->load->model( 'm_report' );
	}
	
	public function index()
	{
		show_404();
	}

	public function applicants_search()
	{
		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/reports/applicants-search.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports/applicants-search.js',
		];

		$this->load->model( 'm_applicant' );

		if($_SESSION['admin']['user']['user_type'] == 10){
        	$employers_query = $this->db->get_where('employer', array('rs_id' => $_SESSION['admin']['user']['user_id']));
			$employers = $employers_query->result_array();
		}

		if($_SESSION['admin']['user']['user_type'] == 9){
        	$user_rs = $this->db->get_where('user', array('team_lead_id' => $_SESSION['admin']['user']['user_id']));
        	foreach ($user_rs->result() as $key => $value) {
        		$users_rs_id[] = $value->user_id;
        	}
        	$this->db->flush_cache();
			$this->db->where_in('employer_user',$users_rs_id);
			$employers = $this->db->get('employer')->result_array();
		}

		$this->setVariables([
				'employers'  => $employers,
				'status'	 => ( new m_applicant )->status,
			])
			->renderPage('reports/applicants/search', true);
	}
	
	public function applicants()
	{   
		if ( ! isset( $_POST['applicant'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$post = $_POST['applicant'];

		//adding substatus
    	$applicants = ( new m_report )->getApplicants();
    	foreach ($applicants as $key => $applicant) {
    		$applicantsRaw = ( new m_applicant )->getApplicantRawById( $applicant['applicant_id'] );
    		$applicants[$key]['sub_status'] = $applicantsRaw->sub_status;
    	}
	   	$employer   = [];

    	$this->load->model( 'm_applicant' );
    	$status = ( new m_applicant )->status;

    	if ( $post['employer'] > 0 ) {
    		$this->load->model( 'm_employer' );
    		$employer = ( new m_employer )->getEmployerById( $post['employer'] );
    	}

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/applicants.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];
			$applicants_raw = ( new m_applicant )->cyd_get_applicants_raw();
			$applicant_certificate_raw = ( new m_applicant )->cyd_get_applicant_certificate_raw();
			$applicant_requirement_raw = ( new m_applicant )->cyd_applicant_requirement_raw();
			$substatus = $post['substatus'];

		$this->setVariables([
			'applicants'	    => $applicants,
			'employer'          => $employer,
			'applicants_raw'	=> $applicants_raw,
			'applicant_certificate_raw'	=> $applicant_certificate_raw,
			'applicant_requirement_raw'	=> $applicant_requirement_raw,
			'groupByEmployer'   => ! $post['employer'] > 0,
			'dateFrom'          => $post['date-from'],
			'dateTo'            => $post['date-to'],
			'substatus'         => $substatus,
			'statusText'        => ( new m_applicant )->statusText,
		]);

		if($_POST['submit'] == 'generate'){
			if ( $post['status'] == $status['Reserved'] ) {
				$this->setTitle('RESERVED APPLICANTS', false)
					->renderPage('reports/applicants/reserved', true);
			} else if ( $post['status'] == $status['Selected'] ) {
				
				if ( $post['format_reports'] ==1 ) {
					$this->setTitle('SELECTED '.( isset( $post['selected'] ) ? 'but NO '.$post['selected'].' ' : ' ' ).'APPLICANTS', false)
					->renderPage('reports/applicants/selected', true);
					}
				else if ( $post['format_reports'] ==2 ) {
				$this->setTitle('SELECTED APPLICANTS', false)
					->renderPage('reports/applicants/selected1', true);
				}
				
				else if ( $post['format_reports'] ==3 ) {
				$this->setTitle('SELECTED APPLICANTS', false)
					->renderPage('reports/applicants/rsr', true);
				}
				
				else if ( $post['format_reports'] ==4 ) {
				$this->setTitle('SELECTED APPLICANTS', false)
					->renderPage('reports/applicants/rsr_saudi', true);
				}
				
		
				
					
			} else if ( $post['status'] == $status['Deployed'] ) {
				$this->setTitle('DEPLOYED APPLICANTS', false)
					->renderPage('reports/applicants/deployed', true);
			} else {
				$this->setTitle('ALL APPLICANTS', false)
					->renderPage('reports/applicants', true);
			}
		}//if generate to a web page

		if($_POST['submit'] == 'csv'){
			$statusText = ( new m_applicant )->statusText;
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=selected_and_deployed_'.date('Y-m-d').'.csv');

			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'w');

			// output the column headings
			fputcsv(
				$output, 
				array(
					'#',
					'Applicant #', 
					'Date applied',
					'Applicant',
					'Recruitment Agent',
					'Job Offer',
					'Position',
					'Country',
					'Status',
					'Employer',
					'Remarks',
					'Last modified',
				)
			);

			$ctr = 0;
			$currentGroup = '';
			foreach ( $applicants as $applicant ):
				$ctr ++;
			
				fputcsv(
					$output, 
					array(
						$ctr,
						$_SESSION["settings"]['client_short'].'-'.str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT), 
						fdate( 'Y-m-d', $applicant['applicant_date_applied'] ),
						$applicant['applicant_name'],
						$applicant['agent_first'].' '.$applicant['agent_last'],
						$applicant['job_name'],
						$applicant['position_name'],
						$applicant['country_name'],
						$statusText[$applicant['applicant_status']],
						$applicant['employer_name'],
						$applicant['applicant_remarks'],
						fdate( 'Y-m-d h:ia', $applicant['applicant_updated'] ),
					)
				);
			endforeach;
		}//if submit button as csv
	}


	public function applicants_status_search()
	{
		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/reports/applicants-status-search.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports/applicants-status-search.js',
		];

		$this->load->model( 'm_applicant' );
		$this->load->model( 'm_position' );

		$positions = ( new m_position )->getActivePositionsGroupByCategory();
		
		if($_SESSION['admin']['user']['user_type'] == 10){
        	$employers_query = $this->db->get_where('employer', array('employer_user' => $_SESSION['admin']['user']['user_id']));
			$employers = $employers_query->result_array();
		}
		if($_SESSION['admin']['user']['user_type'] == 9){
        	$user_rs = $this->db->get_where('user', array('team_lead_id' => $_SESSION['admin']['user']['user_id']));
        	foreach ($user_rs->result() as $key => $value) {
        		$users_rs_id[] = $value->user_id;
        	}
        	$this->db->flush_cache();
			$this->db->where_in('employer_user',$users_rs_id);
			$employers = $this->db->get('employer')->result_array();
		}

		$recruitment_agents = $this->db->get('recruitment_agent')->result_array();

		$applicants_raw = ( new m_applicant )->cyd_get_applicants_raw();
		$applicant_certificate_raw = ( new m_applicant )->cyd_get_applicant_certificate_raw();
		$applicant_requirement_raw = ( new m_applicant )->cyd_applicant_requirement_raw();

		$this->setVariables([
				'employers'  => $employers,
				'applicants_raw'	=> $applicants_raw,
				'recruitment_agents' => $recruitment_agents,
				'applicant_certificate_raw'	=> $applicant_certificate_raw,
				'applicant_requirement_raw'	=> $applicant_requirement_raw,
				'status'	 => ( new m_applicant )->status,
				'positions'  => $positions,
			])
			->renderPage('reports/applicants/status-search', true);
	}

	public function applicants_status()
	{   
		if ( ! isset( $_POST['applicant'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$post = $_POST['applicant'];
		$cert = $_POST['cert'];
    	$applicants = ( new m_report )->getApplicantStatusReports([
    		'status'    => $post['status'],
    		'date-from' => $post['date-from'],
    		'date-to'   => $post['date-to'],
    		'age-from'  => $post['age-from'],
    		'age-to'    => $post['age-to'],
    		'gender' 	=> $post['gender'],
    		'position'  => $post['position'],
    		'employer'  => $post['employer'],
    		'experience'  => $post['experience'],
    		'religion'  => $post['religion'],
    		'recruitment_agents'  => $post['recruitment_agents'],
    		'cert'		=> $cert,
    	]); 

    	$employer   = [];

    	$this->load->model( 'm_applicant' );
    	$status     = ( new m_applicant )->status;
    	$statusText = ( new m_applicant )->statusText;

    	if ( $post['employer'] > 0 ) {
    		$this->load->model( 'm_employer' );
    		$employer = ( new m_employer )->getEmployerById( $post['employer'] );
    	}

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/applicants.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];
			
		$applicants_raw = ( new m_applicant )->cyd_get_applicants_raw();
		$applicant_certificate_raw = ( new m_applicant )->cyd_get_applicant_certificate_raw();
		$applicant_requirement_raw = ( new m_applicant )->cyd_applicant_requirement_raw();
		$subpositions = ( new m_applicant )->cyd_get_all_sub_position();

		if($_POST['submit'] == 'generate'){
			$this->setVariables([
					'applicants'	    => $applicants,
					'applicants_raw'	=> $applicants_raw,
					'applicant_certificate_raw'	=> $applicant_certificate_raw,
					'applicant_requirement_raw'	=> $applicant_requirement_raw,
					'subpositions'		=> $subpositions,
					'employer'          => $employer,
					'dateFrom'          => $post['date-from'],
					'dateTo'            => $post['date-to'],
					'status'            => $post['status'],
					'statusText'        => $statusText,
				]);

			if ( is_numeric( $post['status'] ) ) {
				$this->setTitle( strtoupper( $statusText[ $post['status'] ] ).' APPLICANTS', false);
			} else {
				$this->setTitle('APPLICANT STATUS REPORT', false);
			}

			$this->renderPage('reports/applicants-status', true);
		}//if generate to a web page

		$statusText = ( new m_applicant )->statusText;

		if($_POST['submit'] == 'csv'){
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=Applicant_status_report_'.date('Y-m-d').'.csv');

			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'w');

			// output the column headings
			fputcsv(
				$output, 
				array(
					'#',
					'Applicant #', 
					'STATUS', 
					'Code',
					'Date Applied',
					'Recruitment Agent',
					'Name',
					'Age',
					'Gender',
					'Religion',
					'Contact #',
					'Passport',
					'Medical',
					'Result',
					'VISA',
					'OEC',
					'OWWA',
					'Ticket',
					'Pref. Position',
					'Remarks',
				)
			);

			$ctr = 0;
			$currentGroup = '';
			foreach ( $applicants as $applicant ):
				$ctr ++;
				
				$aplicantNumberStat = '';
				if($applicants_raw[$applicant['applicant_id']]->applicantNumber != '')
					$aplicantNumberStat = $applicants_raw[$applicant['applicant_id']]->applicantNumber;

				fputcsv(
					$output, 
					array(
						$ctr,
						str_pad( $applicant['applicant_id'], 7, '0', STR_PAD_LEFT ), 
						$statusText[ $applicant['applicant_status'] ],
						$aplicantNumberStat,
						$applicant['applicant_date_applied'],
						$applicant['agent_first'].' '.$applicant['agent_last'],
						$applicant['applicant_name'],
						$applicant['applicant_age'],
						$applicant['applicant_gender'],
						$applicant['applicant_religion'],
						$applicant['applicant_contacts'],
						$applicant['passport_number'],
						$applicant['certificate_medical_clinic'],
						$applicant['certificate_medical_result'],
						$applicant['requirement_visa_release_date'],
						$applicant['requirement_oec_number'],
						$applicant['requirement_owwa_certificate'],
						$applicant['requirement_ticket'],
						$applicant['position_name'],
						$applicant['applicant_remarks'],
					)
				);
			endforeach;
		}//if submit button as csv
	}

	//same as applicant_status but only shows total numbers
	public function applicants_status_summary()
	{   

		$this->load->model( 'm_position' );

		if ( ! isset( $_POST['applicant'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$post = $_POST['applicant'];

    	$post = $_POST['applicant'];
		$cert = $_POST['cert'];
    	$applicants = ( new m_report )->getApplicantStatusReports([
    		'status'    => $post['status'],
    		'date-from' => $post['date-from'],
    		'date-to'   => $post['date-to'],
    		'age-from'  => $post['age-from'],
    		'age-to'    => $post['age-to'],
    		'gender' 	=> $post['gender'],
    		'position'  => $post['position'],
    		'employer'  => $post['employer'],
    		'experience'  => $post['experience'],
    		'cert'		=> $cert,
    	]);

    	$employer   = [];

    	$this->load->model( 'm_applicant' );
    	$status     = ( new m_applicant )->status;
    	$statusText = ( new m_applicant )->statusText;

    	if ( $post['employer'] > 0 ) {
    		$this->load->model( 'm_employer' );
    		$employer = ( new m_employer )->getEmployerById( $post['employer'] );
    	}

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/applicants.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		if($post['position'] != 0){
			$getPosResult = ( new m_position )->getPositionById($post['position']);
			$podByID = $getPosResult[0]['position_name'];
		}else{
			$podByID = '';
		}


		$applicants_raw = ( new m_applicant )->cyd_get_applicants_raw();
		$applicant_certificate_raw = ( new m_applicant )->cyd_get_applicant_certificate_raw();
		$subpositions = ( new m_applicant )->cyd_get_all_sub_position();

		$this->setVariables([
				'applicants'	    => $applicants,
				'applicants_raw'	=> $applicants_raw,
				'applicant_certificate_raw'	=> $applicant_certificate_raw,
				'subpositions'		=> $subpositions,
				'employer'          => $employer,
				'dateFrom'          => $post['date-from'],
				'dateTo'            => $post['date-to'],
				'status'            => $post['status'],
				'statusText'        => $statusText,
				'post'				=> $post,
				'podByID'			=> $podByID,
			]);

		if ( is_numeric( $post['status'] ) ) {
			$this->setTitle( strtoupper( $statusText[ $post['status'] ] ).' APPLICANTS', false);
		} else {
			$this->setTitle('APPLICANT STATUS REPORT', false);
		}

		$this->renderPage('reports/applicants-status-summary', true);
	}

	public function summary_reports_modal()
	{
		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/reports/applicants-status-search.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports/applicants-status-search.js',
		];
		
		$employers = $this->db->get('employer')->result_array();

		$this->setVariables([
				'employers'  => $employers,
			])
			->renderPage('reports/applicants/summary-reports-modal', true);
	}

	//same as applicant_status but only shows total numbers
	public function summary_reports()
	{   
		$data['employer_id'] = $this->input->post('employer');
		if($data['employer_id'] != 0){
			$query = $this->db->get_where('employer', array('employer_id' => $data['employer_id']));
			$data['employer_contact_person'] = $query->result()[0]->employer_contact_person;		
		}else{
			$data['employer_name'] = 'All';
		}


		$data['pageTitle'] = 'Summary Reports';
		$this->load->view('header',$data);
		$this->load->view('admin/reports/summary-reports',$data);
		$this->load->view('footer');
	}

	public function summary_applied_online_modal()
	{

		$this->styles = [
			$this->getPath()['styles'] . 'pages/reports/applicants-status-search.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports/applicants-status-search.js',
		];
		
		$recruitment_agents = $this->db->get('recruitment_agent')->result_array();

		$this->setVariables([
				'recruitment_agents'  => $recruitment_agents,
			])
			->renderPage('reports/applicants/summary-applied-online-modal', true);
	}

	//same as applicant_status but only shows total numbers
	public function summary_applied_online()
	{   
		$data['agent_id'] = $this->input->post('agent_id');
		if($data['agent_id'] != 0){
			$query = $this->db->get_where('recruitment_agent', array('agent_id' => $data['agent_id']));
			$recruitment_agent = $query->result()[0];
			$data['recruitment_agent_name'] = $recruitment_agent->agent_first.' '.$recruitment_agent->agent_last;		
		}else{
			$data['recruitment_agent_name'] = 'All RECRUITMENT';
		}
		$data['pageTitle'] = 'Summary Applied Online';
		$this->load->view('header',$data);
		$this->load->view('admin/reports/summary_applied_online',$data);
		$this->load->view('footer');
	}

	public function employers()
	{
		if ( ! isset( $_POST['employer'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$post     = $_POST['employer'];
		$dateFrom = $dateTo = null;

		if ( isset( $post['date-from'], $post['date-to'] ) ) {
			$dateFrom   = fdate( 'Y-m-d', $post['date-from'] );
			$dateTo     = fdate( 'Y-m-d', $post['date-to'] );
		}

		if ( $post['employer'] > 0 ) {

			$this->employer( $post['employer'], $dateFrom, $dateTo );
			return;
		}

		$employers = ( new m_report )->getEmployers( true, $dateFrom, $dateTo );


		if ($_POST['submit'] == 'generate') {

			$this->styles = [
				$this->getPath()['plugins'] . 'print-area/PrintArea.css',
				$this->getPath()['styles'] . 'pages/reports/employers.css',
			];

			$this->scripts = [
				$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
				$this->getPath()['scripts'] . 'pages/reports.js',
			];

			$this->setVariables([
					'employers' => $employers,
					'dateFrom'  => $dateFrom,
					'dateTo'    => $dateTo,
				])
				->setTitle( 'All Employers' , false)
				->renderPage('reports/employers/job-offers', true);
		}//generate to web

		if ($_POST['submit'] == 'csv') {
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=employers_reports_'.date('Y-m-d').'.csv');

			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'w');

			foreach ( $employers as $employerId => $employer ):
				if ( count( $employer['job-offers'] ) == 0 ) continue;

				fputcsv(
					$output, 
					array(
						'---',
						$employer['employer_name'], 
						'---',
						'---',
						'---',
						'---',
						'---',
						'---',
					)
				);

				// output the column headings
				fputcsv(
					$output, 
					array(
						'#',
						'Job title', 
						'Workers needed',
						'Remaining slot',
						'Offer salary',
						'Total revenue',
						'Remarks',
						'Created',
					)
				);

				$ctr = 0;
				foreach ( $employer['job-offers'] as $job ):
					$ctr ++;
					
					fputcsv(
						$output,
						array(
							$ctr,
							$job['job_name'], 
							$job['job_total'],
							( $job['job_total'] - $job['job_occupied'] ) <= 0 ? 0 : $job['job_total'] - $job['job_occupied'],
							'P'.number_format( $job['job_salary_from'],2).' - P'.number_format( $job['job_salary_to'], 2),
							'P'.number_format( $job['job_revenue'], 2),
							$job['job_remarks'],
							fdate( 'Y-m-d h:ia', $job['job_created'] ),
						)
					);
				endforeach;
			endforeach;
		}//generate to excel

	}

	public function employer()
	{
		if ( ! isset( $_POST['employer'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$post = $_POST['employer'];

		if ( ! $post['employer'] ) {
			echo 'You didn\'t specify an employer.';
			exit;	
		}

		$employer = ( new m_report )->getEmployerById( $post['employer'] );

		if ($_POST['submit'] == 'generate') {

			$this->styles = [
				$this->getPath()['plugins'] . 'print-area/PrintArea.css',
				$this->getPath()['styles'] . 'pages/reports/employers.css',
			];

			$this->scripts = [
				$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
				$this->getPath()['scripts'] . 'pages/reports.js',
			];

			$this->setVariables([
					'employer' => $employer,
				])
				->setTitle( $employer['employer_name'] , false)
				->renderPage('reports/employers/employer-job-offers', true);
		}//generate to web

		if ($_POST['submit'] == 'csv') {
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=employers_reports_'.date('Y-m-d').'.csv');

			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'w');

			// output the column headings
			fputcsv(
				$output, 
				array(
					'#',
					'Job title', 
					'Workers needed',
					'Remaining slot',
					'Offer salary',
					'Total revenue',
					'Remarks',
					'Created',
				)
			);

			$ctr = 0;
			foreach ( $employer['job-offers'] as $job ):
				$ctr ++;
				// output for each row
				fputcsv(
					$output, 
					array(
						$ctr,
						$job['job_name'], 
						$job['job_total'],
						$job['job_total'] - $job['job_occupied'],
						'P'.number_format( $job['job_salary_from'], 2).'-P'.number_format( $job['job_salary_to'], 2),
						'P'.number_format( $job['job_revenue'], 2),
						$job['job_remarks'],
						fdate( 'Y-m-d h:ia', $job['job_created'] ),
					)
				);
			endforeach;

		}//generate to excel
	}

	public function employers_search()
	{
		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers'  => $employers,
			])
			->renderPage('reports/employers/search', true);
	}

	//report modal for getting repat reports
	public function repat_modal(){
		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers'  => $employers,
			])
			->renderPage('reports/repat-modal', true);
	}

	//report result for repat, html and csv
	public function repat(){

		if ( ! isset( $_POST['employer'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$post = $_POST['employer'];

		$dateFrom = 0;
		$dateTo = 0;
		if ( isset( $post['date-from'], $post['date-to'] ) ) {
			$dateFrom   = fdate( 'Y-m-d', $post['date-from'] );
			$dateTo     = fdate( 'Y-m-d', $post['date-to'] );
		}

		$employer = ( new m_report )->getEmployerById( $post['employer'] );

		$app_repat = ( new m_report )->getAppRepat( $post['employer'],$dateFrom,$dateTo );


		if(!isset($employer['employer_name']))
			$employer['employer_name'] = 'ALL EMPLOYERS';

		$this->load->model( 'm_applicant');
		$statusText = ( new m_applicant )->statusText;

		if ($_POST['submit'] == 'generate') {

			$this->styles = [
				$this->getPath()['plugins'] . 'print-area/PrintArea.css',
				$this->getPath()['styles'] . 'pages/reports/employers.css',
			];

			$this->scripts = [
				$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
				$this->getPath()['scripts'] . 'pages/reports.js',
			];

			$this->setVariables([
					'app_repat' => $app_repat,
					'employer' => $employer,
					'dateFrom'	=> $dateFrom,
					'dateTo' 	=> $dateTo,
					'statusText' => $statusText,
				])
				->setTitle( $employer['employer_name'] , false)
				->renderPage('reports/repat', true);
		}//generate to web

		if ($_POST['submit'] == 'csv') {
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=repat_reports_'.date('Y-m-d').'.csv');

			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'w');

			// output the column headings
			fputcsv(
				$output, 
				array(
					'#',
					'Applicant #', 
					'Repat Date',
					'Employer',
					'Applicant',
					'Status',
					'Gender',
					'Remarks',
				)
			);

			$ctr = 0;
			foreach ($app_repat as $app):
				$ctr ++;
				// output for each row
				fputcsv(
					$output, 
					array(
						$ctr,
						$_SESSION["settings"]['client_short'].'-'.str_pad( $app->applicant_id , 7, '0', STR_PAD_LEFT), 
						$app->repat_date,
						$employer['employer_name'],
						$app->applicant_first.' '.$app->applicant_middle.' '.$app->applicant_last,
						$statusText[$app->applicant_status],
						$app->applicant_gender,
						$app->applicant_remarks,
					)
				);
			endforeach;

		}//generate to excel
	}

	public function marketing_agencies_employers_search()
	{
		$agencies = ( new m_report )->getMarketingAgencies();

		$this->setVariables([
				'agencies' => $agencies,
			])
			->renderPage('reports/marketing-agencies/employers-search', true);
	}

	public function marketing_agencies_employers()
	{
		if ( ! isset( $_POST['employer']['agency'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['employer']['agency'] > 0 ) {
			$this->marketing_agency_employers( $_POST['employer']['agency'] );
			return;
		}

		$agencies = ( new m_report )->getMarketingAgencies( true );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agencies' => $agencies,
			])
			->setTitle('ALL EMPLOYERS', false)
			->renderPage('reports/marketing-agencies/agencies-employers', true);
	}

	public function marketing_agency_employers( $agencyId )
	{
		if ( ! isset( $_POST['employer']['agency'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['employer']['agency'] == 0 ) {
			echo 'You didn\'t specify an agency.';
			exit;
		}

		$agency = ( new m_report )->getMarketingAgency( $agencyId );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agency' => $agency,
			])
			->setTitle( $agency['agency_name'], false)
			->renderPage('reports/marketing-agencies/agency-employers', true);
	}

	public function marketing_agencies_deployed_search()
	{
		$this->load->model( 'm_marketing_agency' );
		$agencies  = ( new m_marketing_agency )->getMarketingAgencies();

		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'agencies'  => $agencies,
				'employers' => $employers,
			])
			->renderPage('reports/marketing-agencies/deployed-search', true);
	}

	public function marketing_agencies_deployed_applicants()
	{
		if ( ! isset( $_POST['applicant']['agency'], $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$agencyId   = (int) $_POST['applicant']['agency'];

		if ( $agencyId > 0 ) {
			$this->marketing_agency_deployed_applicants( $agencyId );
			return;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agencies = ( new m_report )->getDeployedApplicantsGroupByMarketingAgencies( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agencies' => $agencies,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Deployed Applicants grouped by Marketing Agencies', false)
			->renderPage('reports/marketing-agencies/deployed-applicants', true);
	}

	public function marketing_agency_deployed_applicants( $agencyId )
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agency = ( new m_report )->getDeployedApplicantsByMarketingAgency( $agencyId, $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agency'   => $agency,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( $agency['agency_name'].' : Deployed Applicants', false)
			->renderPage('reports/marketing-agencies/agency-deployed-applicants', true);
	}

	public function marketing_agencies_selected_search()
	{
		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers' => $employers,
			])
			->renderPage('reports/marketing-agencies/selected-search', true);
	}

	public function marketing_agencies_selected_applicants()
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agencies = ( new m_report )->getSelectedApplicantsGroupByMarketingAgencies( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agencies' => $agencies,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Selected Applicants grouped by Marketing Agencies', false)
			->renderPage('reports/marketing-agencies/selected-applicants', true);
	}

	public function marketing_agents_employers_search()
	{
		$agents = ( new m_report )->getMarketingAgents();

		$this->setVariables([
				'agents' => $agents,
			])
			->renderPage('reports/marketing-agents/employers-search', true);
	}

	public function marketing_agents_employers()
	{
		if ( ! isset( $_POST['employer']['agent'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['employer']['agent'] > 0 ) {
			$this->marketing_agent_employers( $_POST['employer']['agent'] );
			return;
		}

		$agents = ( new m_report )->getMarketingAgents( true );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agents' => $agents,
			])
			->setTitle('ALL EMPLOYERS', false)
			->renderPage('reports/marketing-agents/agents-employers', true);
	}

	public function marketing_agent_employers( $agentId )
	{
		if ( ! isset( $_POST['employer']['agent'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['employer']['agent'] == 0 ) {
			echo 'You didn\'t specify an agent.';
			exit;
		}

		$agent = ( new m_report )->getMarketingAgent( $agentId );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agent' => $agent,
			])
			->setTitle( $agent['agent_first'].' '.$agent['agent_last'], false)
			->renderPage('reports/marketing-agents/agent-employers', true);
	}

	public function marketing_agents_deployed_search()
	{
		$this->load->model( 'm_marketing_agent' );
		$agents  = ( new m_marketing_agent )->getMarketingAgents();

		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'agents'    => $agents,
				'employers' => $employers,
			])
			->renderPage('reports/marketing-agents/deployed-search', true);
	}

	public function marketing_agents_deployed_applicants()
	{
		if ( ! isset( $_POST['applicant']['agent'], $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$agentId   = (int) $_POST['applicant']['agent'];

		if ( $agentId > 0 ) {
			$this->marketing_agent_deployed_applicants( $agentId );
			return;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agents = ( new m_report )->getDeployedApplicantsGroupByMarketingAgents( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agents' => $agents,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Deployed Applicants grouped by Marketing Agents', false)
			->renderPage('reports/marketing-agents/deployed-applicants', true);
	}

	public function marketing_agent_deployed_applicants( $agentId )
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agent = ( new m_report )->getDeployedApplicantsByMarketingAgent( $agentId, $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agent'    => $agent,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( $agent['agent_first'].' '.$agent['agent_last'].' : Deployed Applicants', false)
			->renderPage('reports/marketing-agents/agent-deployed-applicants', true);
	}

	public function marketing_agents_selected_search()
	{
		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers' => $employers,
			])
			->renderPage('reports/marketing-agents/selected-search', true);
	}

	public function marketing_agents_selected_applicants()
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agents = ( new m_report )->getSelectedApplicantsGroupByMarketingAgents( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agents' => $agents,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Selected Applicants grouped by Marketing Agents', false)
			->renderPage('reports/marketing-agents/selected-applicants', true);
	}

    public function recruitment_agents_deployed_search()
	{
		$this->load->model( 'm_recruitment_agent' );
		$agents  = ( new m_recruitment_agent )->getRecruitmentAgents();

		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'agents'    => $agents,
				'employers' => $employers,
			])
			->renderPage('reports/recruitment-agents/deployed-search', true);
	}

	public function recruitment_agents_deployed_applicants()
	{
		if ( ! isset( $_POST['applicant']['agent'], $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$agentId = $_POST['applicant']['agent'];

		if ( $agentId > 0 ) {
			$this->recruitment_agent_deployed_applicants( $agentId );
			return;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agents = ( new m_report )->getDeployedApplicantsGroupByRecruitmentAgents( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agents'   => $agents,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Deployed Applicants grouped by Recruitment Agents', false)
			->renderPage('reports/recruitment-agents/deployed-applicants', true);
	}

	public function recruitment_agent_deployed_applicants( $agentId )
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agent = ( new m_report )->getDeployedApplicantsByRecruitmentAgent( $agentId, $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agent'    => $agent,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( $agent['agent_first'].' '.$agent['agent_last'].' : Deployed Applicants', false)
			->renderPage('reports/recruitment-agents/agent-deployed-applicants', true);
	}

	public function recruitment_agents_selected_search()
	{
		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers' => $employers,
			])
			->renderPage('reports/recruitment-agents/selected-search', true);
	}

	public function recruitment_agents_selected_applicants()
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agents = ( new m_report )->getSelectedApplicantsGroupByRecruitmentAgents( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agents'   => $agents,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Selected Applicants grouped by Recruitment Agents', false)
			->renderPage('reports/recruitment-agents/selected-applicants', true);
	}

	public function voucher( $voucherId )
	{   
		$this->load->model('m_voucher');
		$voucher = ( new m_voucher )->getVoucherbyId( $voucherId, true );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/voucher.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports/voucher.js',
		];

		$this->setVariables([
				'voucher'	    => $voucher,
			]);

		$this->setTitle('Voucher# '.str_pad( $voucherId, 5, '0', STR_PAD_LEFT ), false)
			->renderPage('reports/commissions/voucher', true);
	}

	public function OReceipt($orId )
	{   
		$this->load->model('m_or');
		$or = ( new m_or )->getORbyId( $orId );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/or.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports/or.js',
		];

		$this->setVariables([
				'or'	    => $or,
			]);

		$this->setTitle('OR# '.str_pad( $orId, 5, '0', STR_PAD_LEFT ), false)
			->renderPage('reports/billing/or', true);
	}

	public function training_reports_modal(){
		$this->load->model( 'm_position');
		$this->load->model( 'm_applicant');

        $categories = ( new m_position )->getActivePositionsGroupByCategory();
        $trainingBranches  = ( new m_applicant )->allTrainingBranches();
        
        $this->setVariables([
			'categories'  => $categories,
			'trainingBranches'  => $trainingBranches,
		])->renderPage('reports/applicants/training-reports-modal', true);
	}

	public function training_reports(){
		error_reporting(0);
		$applicant_status 	= $this->input->post('applicant_status');
		$agestart 			= $this->input->post('agestart');
		$ageend 			= $this->input->post('ageend');
		$position 			= $this->input->post('category');
		$training_branch 	= $this->input->post('training_branch');

		//models
		$this->load->model( 'm_applicant');
		$this->load->model( 'm_position');

		$this->db->where('applicant_status', $applicant_status);

		if($position != 0){
			$this->db->where('applicant_preferred_position', $position); 
		}

		if($training_branch != 0){
			$this->db->where('training_branches_id', $training_branch); 
		}
		
		if($agestart != 0){
			$dateFromRaw = strtotime(date('Y-m-d').' -'.$agestart.' year');
			$dateFrom = date('Y-m-d', $dateFromRaw);
			$this->db->where('applicant_birthdate <=', $dateFrom);
		}

		if($ageend != 0){
			$dateToRaw = strtotime(date('Y-m-d').' -'.$ageend.' year');
			$dateTo = date('Y-m-d', $dateToRaw);
			$this->db->where('applicant_birthdate >=', $dateTo);
		}

		$query = $this->db->get('applicant');
		$reportResult = $query->result();

		$categories = ( new m_position )->getPositions();
		$statusText = ( new m_applicant )->statusText;
		$trainingBranches = ( new m_applicant )->allTrainingBranches();

        $this->setVariables([
        	'statusText'	=> $statusText,
        	'trainingBranches' => $trainingBranches,
        	'categories'	=> $categories,
			'reportResult'  => $reportResult,
		])->renderPage('reports/training-reports', true);

	}
    
}

/* End of file reports.php */
/* Location: ./app/controllers/admin/reports.php */