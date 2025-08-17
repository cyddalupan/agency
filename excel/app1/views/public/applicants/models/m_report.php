<?php //-->
/*
 * This file is part a custom application package.
 * (c) 2014 Clemente Quiè´–ones Jr. <clemquinones@gmail.com>
 */

/**
 * Core Knowledge of all pages
 *
 * @author     Clemente Quiè´–ones Jr. <clemquinones@gmail.com>
 * @version    1.0.0
 */
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class m_report extends MY_Model {
 	/* Constants
	-------------------------------*/
	/* Public Properties
	-------------------------------*/
	/* Protected Properties
	-------------------------------*/
	/* Private Properties
	-------------------------------*/
	/* Get
	-------------------------------*/
	/* Magic
	-------------------------------*/ 
	public function __construct() 
	{
		parent::__construct(); 
	}
	
	/* Public Methods
	-------------------------------*/
	public function getApplicants()
	{
		$post = $_POST['applicant'];

		$status     = (int) $post['status'];
		$dateFrom   = date( 'Y-m-d', strtotime( $post['date-from'] ) );
		$dateTo     = date( 'Y-m-d', strtotime( $post['date-to'] ) );
		$employerId = (int) $post['employer'];

		$this->load->model( 'm_applicant' );
		$statuses = ( new m_applicant )->status;

		$this->db->flush_cache();
		$this->db->from( 'applicant_view' );

		if ( $status ) {
			$this->db->where([
				'applicant_status' => $status,
			]);
		}

		if ( $status == $statuses['Selected'] ) {
			$this->db->join( 'employer_selected', 'selected_applicant = applicant_id' );

			if ( isset( $post['selected'] ) ) {
				switch ( $post['selected'] ) {
					case 'medical':
						$this->db->where( '(`certificate_medical_clinic` IS NULL OR `certificate_medical_clinic` = \'\' )', null, false );
						break;
					case 'visa':
						$this->db->where([
							'requirement_visa' => 0,
						]);
						break;
					case 'contract':
						$this->db->where( '(DATE(`requirement_contract`) IS NULL OR DATE(`requirement_contract`) = \'0000-00-00\' )', null, false );
						break;
				}
			}

			if ( $employerId ) {
				$this->db->where([
						'selected_employer' => $employerId,
					])
					->order_by( 'employer_name', 'ASC' );
			} 

			$this->db->order_by( 'applicant_employer', 'ASC' )
				->order_by( 'selected_created', 'DESC' );
		} else if ( $status == $statuses['Reserved'] ) {
			$this->db->join( 'employer_reservation', 'reservation_applicant = applicant_id' );

			$this->db->where(
						"DATE(reservation_date) BETWEEN '".$dateFrom."' AND '".$dateTo."'",
					null, false);

			if ( $employerId ) {
				$this->db->where([
					'reservation_employer' => $employerId,
				])
				->order_by( 'employer_name', 'ASC' );
			}

			$this->db->order_by( 'applicant_employer', 'ASC' )
				->order_by( 'reservation_created', 'DESC' );
		} else if ( $status == $statuses['Deployed'] ) {
			$this->db->join( 'deployed', 'deployed_applicant = applicant_id' );

			$this->db->where(
				"DATE(deployed_date) BETWEEN '".$dateFrom."' AND '".$dateTo."'",
				null, false);

			if ( $employerId ) {
				$this->db->where([
					'deployed_employer' => $employerId,
				])
				->order_by( 'employer_name', 'ASC' );
			}

			$this->db->where("deployed_id = (SELECT deployed_id FROM deployed WHERE deployed_applicant = applicant_id ORDER BY deployed_created DESC LIMIT 1)", null, false);

			$this->db->order_by( 'applicant_employer', 'ASC' )
				->order_by( 'deployed_date', 'DESC' )
				->order_by( 'deployed_created', 'DESC' );
		} else {
			$this->db->where(
				"DATE(applicant_date_applied) BETWEEN '".$dateFrom."' AND '".$dateTo."'",
				null, false)
				->order_by( 'applicant_employer', 'ASC' );
		}

		if ( $post['employer'] > 0 ) {
			$this->db->where([
				'employer_id' => $post['employer'],
			]);
		}
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();

		//dd($this->db->last_query());

		return $applicants;
	}

	public function getApplicantStatusReports( $options )
	{
		$status   = $options['status'];
		$dateFrom = $options['date-from'];
		$dateTo   = $options['date-to'];
		$ageFrom  = $options['age-from'];
		$ageTo    = $options['age-to'];
		$gender   = $options['gender'];
		$position = $options['position'];
		$employer = $options['employer'];
		$cert 	  = $options['cert'];
		$experience	  = $options['experience'];
		$religion	  = $options['religion'];
		$recruitment_agents	  = $options['recruitment_agents'];



		$this->load->model( 'm_applicant' );
		$statuses = ( new m_applicant )->status;

		//getting multiple lineup incase status is lineup
		if ( (int) $employer > 0 ) {
			$employer = (int) $employer;
			$cydLineUp = '';
			if($status == 5){
				$this->load->model('Cyd_Multiple_Lineup');
				$cydLineUp = $this->Cyd_Multiple_Lineup->by_employer($employer);
			}
		}

		$dateRange = $ageBracket = false;
		
		$this->db->from( 'applicant_view');
		$this->db->join('applicant_certificate', 'applicant_view.applicant_id = applicant_certificate.certificate_applicant');
		
		//certificates
		if(isset($cert['prcrating'])){
			$this->db->where('certificate_prc_rating !=',''); 
		}
		if(isset($cert['dhacetificate'])){
			$this->db->where('certificate_dha !=',''); 
		}
		if(isset($cert['saudicounsilid'])){
			$this->db->where('certificate_saudi_id !=',''); 
		}
		if(isset($cert['ksaprometrics'])){
			$this->db->where('certificate_ksa !=',''); 
		}
		if(isset($cert['haadcert'])){
			$this->db->where('certificate_haad !=',''); 
		}
		if(isset($cert['qatarprometrcs'])){
			$this->db->where('certificate_qatar !=',''); 
		}
		if(isset($cert['nclex'])){
			$this->db->where('certificate_nclex !=',''); 
		}
		if(isset($cert['ieltsscore'])){
			$this->db->where('certificate_ielts_overall !=',''); 
		}
		if(isset($cert['cgfnscert'])){
			$this->db->where('certificate_cgfns !=',''); 
		}

		if (  is_numeric( $status ) ) {
			$this->db->where([
				'applicant_status' => (int) $status,
			]);
		}

		if (  ! is_numeric($gender) ) {
			$this->db->where([
				'applicant_gender' => $gender,
			]);
		}

		if (  ! is_numeric($religion) ) {
			$this->db->where([
				'applicant_religion' => $religion,
			]);
		}


		if ( count( explode( '-', $dateFrom ) ) == 3 &&  count( explode( '-', $dateTo ) ) == 3 ) {
			$explodedDateFrom = explode( '-', $dateFrom );
			$explodedDateTo   = explode( '-', $dateTo );

			if ( checkdate( $explodedDateFrom[1], $explodedDateFrom[2], $explodedDateFrom[0] ) 
				&& checkdate( $explodedDateTo[1], $explodedDateTo[2], $explodedDateTo[0] )
				) {

				$dateRange = true;

				$dateFrom     = date( 'Y-m-d', strtotime( $dateFrom ) );
				$dateTo       = date( 'Y-m-d', strtotime( $dateTo ) );

				$this->db->where( "applicant_date_applied BETWEEN '".$dateFrom."' AND '".$dateTo."'", null, false );
			}
		}

		if ( is_numeric( $ageFrom ) && is_numeric( $ageTo ) && (int) $ageFrom > 0 &&  (int) $ageTo > 0 ) {
			$ageBracket = true;

			$ageFrom = (int) $ageFrom;
			$ageTo   = (int) $ageTo;

			$this->db->where( "applicant_age BETWEEN '".$ageFrom."' AND '".$ageTo."'", null, false );
		}

		if ( (int) $position > 0 ) {
			$position = (int) $position;

			$this->db->where([
				'applicant_preferred_position' => $position,
			]);
		}

		
		if ( (int) $recruitment_agents > 0 ) {
			$recruitment_agents = (int) $recruitment_agents;

			$this->db->where([
				'agent_id' => $recruitment_agents,
			]);
		}

		if ( (int) $employer > 0 ) {
			$employer = (int) $employer;
			if($cydLineUp == ''){	
				$this->db->where([
					'applicant_employer' => $employer,
				]);
			}else{
				$cyd_ap_id_ar[] = 0;
				foreach ($cydLineUp as $per_cydLineUp) {
					$cyd_ap_id_ar[] = $per_cydLineUp->applicant_id;
				}
				$this->db->where_in('applicant_id',$cyd_ap_id_ar);
			}
		}

		$this->db->order_by( 'applicant_status', 'ASC' )
			->order_by( 'applicant_updated', 'ASC' )
			->order_by( 'applicant_id', 'DESC' )
			->order_by( 'applicant_last', 'ASC' )
			->order_by( 'applicant_first', 'ASC' );
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicantsNoExp = $this->db->get()->result_array();

		if($experience == 0){
			$applicants = $applicantsNoExp;
		}else{
			$applicants = $this->get_experience($applicantsNoExp,$experience);
		}

		if($applicants == TRUE){
			return $this->indexArray( $applicants, 'applicant_id' );
		}
		else{
			echo 'No Record';
			die();
		}
	}

	function get_experience($applicants,$experience){
		if($applicants == TRUE)
		{
			foreach($applicants as $key => $row)
			{
				$query = $this->db->get_where('applicant_experiences', array('experience_applicant' => $row['applicant_id']));
				$daysOfExperience = $query->result_array();
				
				//get days of experience
				$daysofExp = 0;
				foreach ($daysOfExperience as $key => $exp) {
					if((bool)strtotime($exp['experience_from'])){
						$datetime1 = new DateTime($exp['experience_from']);
						if((bool)strtotime($exp['experience_to'])){
							$datetime2 = new DateTime($exp['experience_to']);
						}else{
							$datetime2 = new DateTime(date('Y-m-d H:i:s'));
						}
						$interval = $datetime1->diff($datetime2);
						$daysofExp = $daysofExp + $interval->format('%a');
					}
				}

				$row['daysofExp'] = $daysofExp;
				
				if($experience == 1){
					if(
						($daysofExp >= 180) &&
						($daysofExp <= 330)
					){
						$converted[] = $row;
					}
				}elseif($experience == 2){
					if(
						($daysofExp >= 365) &&
						($daysofExp <= 690)
					){
						$converted[] = $row;
					}
				}elseif($experience == 3){
					if(
						($daysofExp >= 730) &&
						($daysofExp <= 1060)
					){
						$converted[] = $row;
					}
				}elseif($experience == 4){
					if(
						($daysofExp >= 1095) &&
						($daysofExp <= 1425)
					){
						$converted[] = $row;
					}
				}elseif($experience == 5){
					if(
						($daysofExp >= 1460) &&
						($daysofExp <= 1790)
					){
						$converted[] = $row;
					}
				}elseif($experience == 5){
					if($daysofExp >= 1825){
						$converted[] = $row;
					}
				}

			}

			return $converted;
		}

		function validateDate($date)
		{
		    $d = DateTime::createFromFormat('Y-m-d', $date);
		    return $d && $d->format('Y-m-d') == $date;
		}
	}

	public function getEmployers( $withJobOffers = false, $dateFrom = null, $dateTo = null  )
	{
		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();

		if ( $withJobOffers ) {
			foreach ($employers as $employerId => $employer) {
				$this->db->flush_cache();
				$this->db->from( 'job' )
					->where([
						'job_employer' => $employerId,
					])
					->order_by('job_created', 'ASC');

				if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
					$this->db->where(
						sprintf("DATE(`job_created`) BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
						null, false);
				}
				$this->db->query('SET SQL_BIG_SELECTS=1');		
				$jobOffers = $this->db->get()->result_array();

				$employers[$employerId]['job-offers'] = $jobOffers;
			}
		}

		return $employers;
	}

	public function getEmployerById( $employerId )
	{
		$this->load->model( 'm_employer' );
		$employer = ( new m_employer )->getEmployerById( $employerId );

		if ( empty( $employer ) ) {
			return [];
		}

		$this->db->flush_cache();
		$this->db->from( 'job' )
			->where([
				'job_employer' => $employerId,
			])
			->order_by( 'job_created', 'aSC' );
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$employer['job-offers'] = $this->db->get()->result_array();

		return $employer;
	}

	public function getMarketingAgencies( $withEmployers = false )
	{
		$this->load->model( 'm_marketing_agency' );
		$agencies = ( new m_marketing_agency )->getMarketingAgencies();

		if ( $withEmployers ) {
			foreach ($agencies as $agencyId => $agency) {
				$this->db->flush_cache();
				$this->db->from( 'employer' )
					->join( 'country', 'country_id = employer_country', 'inner' )
					->where([
						'employer_source_agency' => $agencyId,
					])
					->order_by('employer_name', 'ASC');

				$employers = $this->db->get()->result_array();
				$this->indexArray( $employers, 'employer_id' );

				$agencies[$agencyId]['employers'] = $employers;
			}
		}

		return $agencies;
	}

	public function getMarketingAgency( $agencyId )
	{
		$this->load->model( 'm_marketing_agency' );
		$agency = ( new m_marketing_agency )->getMarketingAgencyById( $agencyId );
		
		$this->db->flush_cache();
		$this->db->from( 'employer' )
			->join( 'country', 'country_id = employer_country', 'inner' )
			->where([
				'employer_source_agency' => $agencyId,
			])
			->order_by('employer_name', 'ASC');

		$employers = $this->db->get()->result_array();
		$this->indexArray( $employers, 'employer_id' );

		$agency['employers'] = $employers;

		return $agency;
	}

	public function getMarketingAgents( $withEmployers = false )
	{
		$this->load->model( 'm_marketing_agent' );
		$agents = ( new m_marketing_agent )->getMarketingAgents();

		if ( $withEmployers ) {
			foreach ($agents as $agentId => $agent) {
				$this->db->flush_cache();
				$this->db->from( 'employer' )
					->join( 'country', 'country_id = employer_country', 'inner' )
					->where([
						'employer_source_agent' => $agentId,
					])
					->order_by('employer_name', 'ASC');

				$employers = $this->db->get()->result_array();
				$this->indexArray( $employers, 'employer_id' );

				$agents[$agentId]['employers'] = $employers;
			}
		}

		return $agents;
	} 

	public function getMarketingAgent( $agentId )
	{
		$this->load->model( 'm_marketing_agent' );
		$agent = ( new m_marketing_agent )->getMarketingAgentById( $agentId );
		
		$this->db->flush_cache();
		$this->db->from( 'employer' )
			->join( 'country', 'country_id = employer_country', 'inner' )
			->where([
				'employer_source_agent' => $agentId,
			])
			->order_by('employer_name', 'ASC');

		$employers = $this->db->get()->result_array();
		$this->indexArray( $employers, 'employer_id' );

		$agent['employers'] = $employers;

		return $agent;
	}

	public function getRecruitmentAgents( $withApplicants = false )
	{
		$this->load->model( 'm_recruitment_agent' );
		$agents = ( new m_recruitment_agent )->getRecruitmentAgents();

		if ( $withApplicants ) {
			foreach ($agents as $agentId => $agent) {
				$this->db->flush_cache();
				$this->db->from( 'applicant' )
					->where([
						'applicant_source' => $agentId,
					])
					->order_by('applicant_first', 'ASC')
					->order_by('applicant_last', 'ASC');

				$applicants = $this->db->get()->result_array();
				$this->indexArray( $applicants, 'applicant_id' );

				$agents[$agentId]['applicants'] = $applicants;
			}
		}

		return $agents;
	}

	public function getRecruitmentAgent( $agentId )
	{
		$this->load->model( 'm_recruitment_agent' );
		$agent = ( new m_recruitment_agent )->getRecruitmentAgentById( $agentId );

		return $agent;
	}

	public function getDeployedApplicantsGroupByMarketingAgencies( $employerId = null, $dateFrom = null, $dateTo = null )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$agencies = $this->getMarketingAgencies();

		foreach ($agencies as $agencyId => $agency) {
			$this->db->flush_cache();
			$this->db->from( 'applicant' )
				->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
				->join( 'employer', 'employer_id = applicant_employer', 'inner' )
				->join( 'deployed', 'deployed_applicant = applicant_id AND deployed_employer = employer_id', 'inner')
				->join( 'job', 'job_id = deployed_job', 'inner')
				->join( 'country', 'country_id = deployed_country', 'inner')
				->where([
					'employer_source_agency' => $agencyId,
					'applicant_status'       => $status['Deployed'],
				]);

			if ( $employerId ) {
				$this->db->where([
					'deployed_employer' => $employerId,
				]);
			}

			if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
				$this->db->where(
					sprintf("`deployed_date` BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
					null, false);
			}

			$this->db->order_by('deployed_date', 'ASC')
				->order_by('employer_name', 'ASC');
			$this->db->query('SET SQL_BIG_SELECTS=1');	
			$applicants = $this->db->get()->result_array();
			$this->indexArray( $applicants, 'applicant_id' );

			$agencies[$agencyId]['applicants'] = $applicants;
		}
		
		return $agencies;
	}

	public function getDeployedApplicantsByMarketingAgency( $agencyId, $employerId = null, $dateFrom = null, $dateTo = null )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$agency = $this->getMarketingAgency( $agencyId );

		$this->db->flush_cache();
		$this->db->from( 'applicant' )
			->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
			->join( 'employer', 'employer_id = applicant_employer', 'inner' )
			->join( 'deployed', 'deployed_applicant = applicant_id AND deployed_employer = employer_id', 'inner')
			->join( 'job', 'job_id = deployed_job', 'inner')
			->join( 'country', 'country_id = deployed_country', 'inner')
			->where([
				'employer_source_agency' => $agencyId,
				'applicant_status'       => $status['Deployed'],
			]);

		if ( $employerId ) {
			$this->db->where([
				'deployed_employer' => $employerId,
			]);
		}

		if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
			$this->db->where(
				sprintf("`deployed_date` BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
				null, false);
		}

		$this->db->order_by('deployed_date', 'ASC')
			->order_by('employer_name', 'ASC');

		$applicants = $this->db->get()->result_array();
		$this->indexArray( $applicants, 'applicant_id' );

		$agency['applicants'] = $applicants;
		
		return $agency;
	}

	public function getDeployedApplicants( $options = [], $sort = [ 'deployed_date', 'ASC' ] )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$this->db->flush_cache();
		$this->db->from( 'applicant' )
			->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
			->join( 'employer', 'employer_id = applicant_employer', 'inner' )
			->join( 'deployed', 'deployed_applicant = applicant_id AND deployed_employer = employer_id', 'inner')
			->join( 'job', 'job_id = deployed_job', 'inner')
			->join( 'country', 'country_id = deployed_country', 'inner')
			->where([
				'applicant_status'       => $status['Deployed'],
			]);

		$this->setDBQueryOptions( $options );
		$this->setDBQueryOrders( $sort );

		$this->db->order_by('employer_name', 'ASC');
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();

		return $this->indexArray( $applicants, 'applicant_id' );
	}

	public function getDeployedApplicantsGroupByMarketingAgents( $employerId = null, $dateFrom = null, $dateTo = null )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$agents = $this->getMarketingAgents();

		foreach ($agents as $agentId => $agent) {
			$this->db->flush_cache();
			$this->db->from( 'applicant' )
				->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
				->join( 'employer', 'employer_id = applicant_employer', 'inner' )
				->join( 'deployed', 'deployed_applicant = applicant_id AND deployed_employer = employer_id', 'inner')
				->join( 'job', 'job_id = deployed_job', 'inner')
				->join( 'country', 'country_id = deployed_country', 'inner')
				->where([
					'employer_source_agent' => $agentId,
					'applicant_status'       => $status['Deployed'],
				]);

			if ( $employerId ) {
				$this->db->where([
					'deployed_employer' => $employerId,
				]);
			}

			if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
				$this->db->where(
					sprintf("`deployed_date` BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
					null, false);
			}

			$this->db->order_by('deployed_date', 'ASC')
				->order_by('employer_name', 'ASC');

			$applicants = $this->db->get()->result_array();
			$this->indexArray( $applicants, 'applicant_id' );

			$agents[$agentId]['applicants'] = $applicants;
		}
		
		return $agents;
	}

	public function getDeployedApplicantsByMarketingAgent( $agentId, $employerId = null, $dateFrom = null, $dateTo = null )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$agent = $this->getMarketingAgent( $agentId );

		$this->db->flush_cache();
		$this->db->from( 'applicant' )
			->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
			->join( 'employer', 'employer_id = applicant_employer', 'inner' )
			->join( 'deployed', 'deployed_applicant = applicant_id AND deployed_employer = employer_id', 'inner')
			->join( 'job', 'job_id = deployed_job', 'inner')
			->join( 'country', 'country_id = deployed_country', 'inner')
			->where([
				'employer_source_agent' => $agentId,
				'applicant_status'       => $status['Deployed'],
			]);

		if ( $employerId ) {
			$this->db->where([
				'deployed_employer' => $employerId,
			]);
		}

		if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
			$this->db->where(
				sprintf("`deployed_date` BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
				null, false);
		}

		$this->db->order_by('deployed_date', 'ASC')
			->order_by('employer_name', 'ASC');
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();
		$this->indexArray( $applicants, 'applicant_id' );

		$agent['applicants'] = $applicants;
		
		
		return $agent;
	}

	public function getDeployedApplicantsGroupByRecruitmentAgents( $employerId = null, $dateFrom = null, $dateTo = null )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$agents = $this->getRecruitmentAgents();

		foreach ($agents as $agentId => $agent) {
			$this->db->flush_cache();
			$this->db->from( 'applicant' )
				->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
				->join( 'employer', 'employer_id = applicant_employer', 'inner' )
				->join( 'deployed', 'deployed_applicant = applicant_id AND deployed_employer = employer_id', 'inner')
				->join( 'job', 'job_id = deployed_job', 'inner')
				->join( 'country', 'country_id = deployed_country', 'inner')
				->where([
					'employer_source_agent' => $agentId,
					'applicant_status'       => $status['Deployed'],
				]);

			if ( $employerId ) {
				$this->db->where([
					'deployed_employer' => $employerId,
				]);
			}

			if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
				$this->db->where(
					sprintf("`deployed_date` BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
					null, false);
			}

			$this->db->order_by('deployed_date', 'ASC')
				->order_by('employer_name', 'ASC');
			$this->db->query('SET SQL_BIG_SELECTS=1');	
			$applicants = $this->db->get()->result_array();
			$this->indexArray( $applicants, 'applicant_id' );

			$agents[$agentId]['applicants'] = $applicants;
		}
		
		return $agents;
	}

	public function getDeployedApplicantsByRecruitmentAgent( $agentId,  $employerId = null, $dateFrom = null, $dateTo = null )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$agent = $this->getRecruitmentAgent( $agentId );
		
		$this->db->flush_cache();
		$this->db->from( 'applicant' )
			->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
			->join( 'employer', 'employer_id = applicant_employer', 'inner' )
			->join( 'deployed', 'deployed_applicant = applicant_id AND deployed_employer = employer_id', 'inner')
			->join( 'job', 'job_id = deployed_job', 'inner')
			->join( 'country', 'country_id = deployed_country', 'inner')
			->where([
				'applicant_source'    => $agentId,
				'applicant_status'    => $status['Deployed'],
			]);

		if ( $employerId ) {
			$this->db->where([
				'deployed_employer' => $employerId,
			]);
		}

		if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
			$this->db->where(
				sprintf("`deployed_date` BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
				null, false);
		}

		$this->db->order_by('deployed_date', 'ASC')
			->order_by('employer_name', 'ASC');

		$applicants = $this->db->get()->result_array();
		$this->indexArray( $applicants, 'applicant_id' );

		$agent['applicants'] = $applicants;
		
		return $agent;
	}

	public function getSelectedApplicantsGroupByMarketingAgencies( $employerId = null, $dateFrom = null, $dateTo = null )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$agencies = $this->getMarketingAgencies();

		foreach ($agencies as $agencyId => $agency) {
			$this->db->flush_cache();
			$this->db->from( 'applicant' )
				->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
				->join( 'employer', 'employer_id = applicant_employer', 'inner' )
				->join( 'employer_selected', 'selected_applicant = applicant_id AND selected_employer = employer_id', 'inner')
				->join( 'job', 'job_id = applicant_job', 'left')
				->where([
					'employer_source_agency' => $agencyId,
					'applicant_status'       => $status['Selected'],
				]);

			if ( $employerId ) {
				$this->db->where([
					'selected_employer' => $employerId,
				]);
			}

			if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
				$this->db->where(
					sprintf("`selected_date` BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
					null, false);
			}

			$this->db->order_by('selected_date', 'ASC')
				->order_by('employer_name', 'ASC');
			$this->db->query('SET SQL_BIG_SELECTS=1');
			$applicants = $this->db->get()->result_array();
			$this->indexArray( $applicants, 'applicant_id' );

			$agencies[$agencyId]['applicants'] = $applicants;
		}
		
		return $agencies;
	}

	public function getSelectedApplicantsGroupByMarketingAgents( $employerId = null, $dateFrom = null, $dateTo = null )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$agents = $this->getMarketingAgents();

		foreach ($agents as $agentId => $agent) {
			$this->db->flush_cache();
			$this->db->from( 'applicant' )
				->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
				->join( 'employer', 'employer_id = applicant_employer', 'inner' )
				->join( 'employer_selected', 'selected_applicant = applicant_id AND selected_employer = employer_id', 'inner')
				->join( 'job', 'job_id = applicant_job', 'left')
				->where([
					'employer_source_agent'  => $agentId,
					'applicant_status'       => $status['Selected'],
				]);

			if ( $employerId ) {
				$this->db->where([
					'selected_employer' => $employerId,
				]);
			}

			if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
				$this->db->where(
					sprintf("`selected_date` BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
					null, false);
			}

			$this->db->order_by('selected_date', 'ASC')
				->order_by('employer_name', 'ASC');
			$this->db->query('SET SQL_BIG_SELECTS=1');
			$applicants = $this->db->get()->result_array();
			$this->indexArray( $applicants, 'applicant_id' );

			$agents[$agentId]['applicants'] = $applicants;
		}
		
		return $agents;
	}

	public function getSelectedApplicantsGroupByRecruitmentAgents( $employerId = null, $dateFrom = null, $dateTo = null )
	{
		$this->load->model( 'm_applicant' );
		$status = ( new m_applicant )->status;

		$agents = $this->getRecruitmentAgents();

		foreach ($agents as $agentId => $agent) {
			$this->db->flush_cache();
			$this->db->from( 'applicant' )
				->join( 'applicant_requirement', 'requirement_applicant = applicant_id', 'inner' )
				->join( 'employer', 'employer_id = applicant_employer', 'inner' )
				->join( 'employer_selected', 'selected_applicant = applicant_id AND selected_employer = employer_id', 'inner')
				->join( 'job', 'job_id = applicant_job', 'left')
				->where([
					'employer_source_agent'  => $agentId,
					'applicant_status'       => $status['Selected'],
				]);

			if ( $employerId ) {
				$this->db->where([
					'selected_employer' => $employerId,
				]);
			}

			if ( ! is_null( $dateFrom ) && ! is_null( $dateTo ) ) {
				$this->db->where(
					sprintf("`selected_date` BETWEEN '%s' AND '%s'", $dateFrom, $dateTo), 
					null, false);
			}

			$this->db->order_by('selected_date', 'ASC')
				->order_by('employer_name', 'ASC');
			$this->db->query('SET SQL_BIG_SELECTS=1');
			$applicants = $this->db->get()->result_array();
			$this->indexArray( $applicants, 'applicant_id' );

			$agents[$agentId]['applicants'] = $applicants;
		}
		
		return $agents;
	}

	public function getAppRepat($employer,$dateFrom,$dateTo){

		$this->db->where('is_repat', 1);
		$this->db->where('repat_date >=', $dateFrom);
		$this->db->where('repat_date <=', $dateTo);

		$query = $this->db->get('applicant');
		return $query->result();
	}

	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
