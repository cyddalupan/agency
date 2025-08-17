<?php //-->
use \Application\Message as Message;

/*
 * This file is part a custom application package.
 * (c) 2014 Clemente Quiè´–ones Jr. <clemquinones@gmail.com>
 * (c) 2015 Cyd Dalupan <cydmdalupan@gmail.com>
 */

/**
 * Core Knowledge of all pages
 *
 * @author     Clemente Quiè´–ones Jr. <clemquinones@gmail.com>
 * @author     Cyd Dalupan <cydmdalupan@gmail.com>
 * @version    1.0.0
 */
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class m_applicant extends MY_Model {
 	/* Constants
	-------------------------------*/
    const RESERVED_DAYS_EXPIRATION = 7; //7-days, update also on the MySQL trigger
    
	/* Public Properties
	-------------------------------*/    
	/* Protected Properties
	-------------------------------*/

    public $status = [
        // 'Cancelled'      => 0,
        // 'Available'      => 1,
        // 'Pre-Selected'   => 2,
        // 'Qualified'      => 3,
        // 'Not Qualified'  => 4,
        // 'Line Up'        => 5,
        // 'Selected'       => 6,
        // 'For Deployment' => 7,
        // 'Deployed'       => 8,
        // 'Reserved'       => 9,
		'For Review'       => 10,
        'Active File'      => 13,
		'Available'      => 0,
		'Passporting'      => 15,
		'For Interview'       => 11,
		'For Booking'       => 12,
        'Cancelled'      => 1,
        'Reserved'       => 2,
        'Pre-Selected'   => 3,
        'Selected'       => 4,
        'Line Up'        => 5, //interview
        'Qualified'      => 6,
        'Not Qualified'  => 7,        
        'For Deployment' => 8,
        'Deployed'       => 9,
		'Blocklist'      =>14,
    ];
    
    public $statusText = [
        // 0 => 'Cancelled',
        // 1 => 'Available',
        // 2 => 'Pre-Selected',
        // 3 => 'Qualified',
        // 4 => 'Not Qualified',
        // 5 => 'Line Up (For interview)',
        // 6 => 'Selected',
        // 7 => 'For Deployment',
        // 8 => 'Deployed',
        // 9 => 'Reserved',
		10 => 'For Review',
		13 => 'Active File',
		0 => 'Available',
		15 => 'Passporting',
		11 => 'For Interview',
		12 => 'For Booking',
        1 => 'Cancelled',
        2 => 'Reserved',
        3 => 'Pre-Selected',
        4 => 'Selected',
        5 => 'Line Up',
        6 => 'Qualified',
        7 => 'Not Qualified',
        8 => 'For Deployment',
        9 => 'Deployed',  
		14 => 'Blocklist',  		
    ];
    
    public $statusColors = [
        // 0 => 'danger',
        // 1 => 'default',
        // 2 => 'default',
        // 3 => 'primary',
        // 4 => 'danger',
        // 5 => 'info',
        // 6 => 'success',
        // 7 => 'info',
        // 8 => 'success',
        // 9 => 'primary',

        0 => 'default',
        1 => 'danger',
        2 => 'primary',
        3 => 'default',
        4 => 'success',
        5 => 'info',
        6 => 'primary',
        7 => 'danger',
        8 => 'warning',
        9 => 'success',    
        10 => 'info',     
        11 => 'primary',     
        12 => 'danger',
		13 => 'info',
		14 => 'default',
		15 => 'danger',		
    ];
    
    public $fileTypes = [   
        'Whole Body Picture' => 'Whole Body Picture',
        'Resume'             => 'Resume/CV',
        'Passport'           => 'Passport',
		'Visa'               => 'Visa',
		'Doc 1'              => 'Docs 1',
		'Doc 2'              => 'Docs 2',
		'Doc 3'              => 'Docs 3',
		'Doc 4'              => 'Docs 4',
		'Doc 5'              => 'Docs 5',
		'Doc 6'              => 'Docs 6',
		'Doc 7'              => 'Docs 7',
		'Doc 8'              => 'Docs 8',
        'Other'              => 'Other',
		'Agency Files 1'      => 'Agency Files 1',
		'Agency Files 2'      => 'Agency Files 2',
		'Agency Files 3'      => 'Agency Files 3',
		'Agency Files 4'      => 'Agency Files 4',
		'Agency Files 5'      => 'Agency Files 5',
		'Agency Files 3'      => 'Agency Files 6'
		
    ];
    
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
	/* Protected Methods
	-------------------------------*/
public function searchApplicants()
	{
		$search = $_GET['search'];

		$all_apid_sub_pos = '';
		if ( $search['position'] > 0 ) {
			//get Subposition
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant_preferred_positions', array('position_position' =>  $search['position']));
			$result = $query->result();
			foreach ($result as $positions_value) {
				$all_apid_sub_pos[] =$positions_value->position_applicant.'"';
			}
		}

		if ( ! empty( $search['q'] ) ) {
			
			//requirement_oec_number search
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant_requirement', array('requirement_oec_number' => $search['q']), 1);
			$result = $query->result();
			if(isset($result[0]->requirement_applicant))
				$requirement_id = $result[0]->requirement_applicant;
			else
				$requirement_id = 0;
			
			//insurance_no search
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant_certificate', array('insurance_no' => $search['q']), 1);
			$result = $query->result();
			if(isset($result[0]->certificate_applicant))
				$certificate_id = $result[0]->certificate_applicant;
			else
				$certificate_id = 0;

			//ticket_no search
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant_requirement', array('ticket_no	' => $search['q']), 1);
			$result = $query->result();
			if(isset($result[0]->requirement_applicant))
				$ticket_no_id = $result[0]->requirement_applicant;
			else
				$ticket_no_id = 0;

			//ticket_no search
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant', array('applicantNumber' => $search['q']), 1);
			$result = $query->result();
			if(isset($result[0]->applicant_id))
				$applicantNumber_id = $result[0]->applicant_id;
			else
				$applicantNumber_id = 0;



		}

		$this->db->flush_cache();
		$this->db->select( 'a.*' )
			->from('applicant_view a')
			->join( 'position p', 'p.position_id = a.applicant_preferred_position', 'left' );

		if ( ! empty( $search['q'] ) ) {
			$this->db->where('(
				a.applicant_first LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.applicant_id LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.applicant_last LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.applicant_middle	LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				CONCAT(a.applicant_first, \' \', a.applicant_last) LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.passport_number LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.position_name LIKE \'%'.addslashes( $search['q'] ).'%\' OR
				a.applicant_remarks LIKE \'%'.addslashes( $search['q'] ).'%\') OR
				a.applicant_id = '.$requirement_id.' OR
				a.applicant_id = '.$ticket_no_id.' OR
				a.applicant_id = '.$applicantNumber_id.' OR
				a.applicant_id = '.$certificate_id.'
			', null, false);
		}

		if ( $search['country'] > 0 ) {
			$this->db->where([
				'applicant_preferred_country' => $search['country']
			]);
		}

		if ( $search['status'] != 111 ) {
			$this->db->where([
				'applicant_status' => $search['status']
			]);
		}

		if ( $search['position'] > 0 ) {
			$this->db->where([
				'applicant_preferred_position' => $search['position']
			]);
			$this->db->or_where_in('a.applicant_id',$all_apid_sub_pos);
		}

		if ( $search['employer'] > 0 ) {
			$this->db->where([
				'employer_id' => $search['employer']
			]);
		}

		if ( ! empty( $search['gender'] ) ) {
			$this->db->where([
				'applicant_gender' => $search['gender']
			]);
		}

		if ( $search['age']['from'] > 0 ) {
			$this->db->where( 'applicant_age BETWEEN '.(int) $search['age']['from'].' AND '.(int) $search['age']['to'],
				null, false );
		}

		if ( $search['salary']['from'] > 0 ) {
			$this->db->where( 'applicant_expected_salary BETWEEN '.
				(float) $search['salary']['from'].' AND '.(float) $search['salary']['to'], 
				null, false );
		}

		if ( isset( $search['date-applied']['from'], $search['date-applied']['to'] ) 
			&& date('Y-m-d', strtotime( $search['date-applied']['from'] )) != date('Y-m-d', strtotime(null))
			&& date('Y-m-d', strtotime( $search['date-applied']['to'] )) != date('Y-m-d', strtotime(null))
			) {

			$dateFrom = date('Y-m-d', strtotime( $search['date-applied']['from'] ));
			$dateTo   = date('Y-m-d', strtotime( $search['date-applied']['to'] ));

			$this->db->where( 'DATE(applicant_date_applied) BETWEEN \''.$dateFrom.'\' AND \''.$dateTo.'\'',
				null, false );
		}
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$this->db->group_by( 'a.applicant_id' );

		$applicants = $this->db->get()->result_array();
		// dd($this->db->last_query());
		
		return $applicants;
	}

	public function searchApplicantsCount()
	{
		$search = $_GET['search'];

		$all_apid_sub_pos = '';
		if ( $search['position'] > 0 ) {
			//get Subposition
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant_preferred_positions', array('position_position' =>  $search['position']));
			$result = $query->result();
			foreach ($result as $positions_value) {
				$all_apid_sub_pos[] =$positions_value->position_applicant.'"';
			}
		}

		if ( ! empty( $search['q'] ) ) {
			
			//requirement_oec_number search
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant_requirement', array('requirement_oec_number' => $search['q']), 1);
			$result = $query->result();
			if(isset($result[0]->requirement_applicant))
				$requirement_id = $result[0]->requirement_applicant;
			else
				$requirement_id = 0;
			
			//insurance_no search
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant_certificate', array('insurance_no' => $search['q']), 1);
			$result = $query->result();
			if(isset($result[0]->certificate_applicant))
				$certificate_id = $result[0]->certificate_applicant;
			else
				$certificate_id = 0;

			//ticket_no search
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant_requirement', array('ticket_no	' => $search['q']), 1);
			$result = $query->result();
			if(isset($result[0]->requirement_applicant))
				$ticket_no_id = $result[0]->requirement_applicant;
			else
				$ticket_no_id = 0;

			//ticket_no search
			$this->db->flush_cache();
			$query = $this->db->get_where('applicant', array('applicantNumber' => $search['q']), 1);
			$result = $query->result();
			if(isset($result[0]->applicant_id))
				$applicantNumber_id = $result[0]->applicant_id;
			else
				$applicantNumber_id = 0;



		}

		$this->db->flush_cache();
		$this->db->select( 'a.*' )
			->from('applicant_view a')
			->join( 'position p', 'p.position_id = a.applicant_preferred_position', 'left' );

		if ( ! empty( $search['q'] ) ) {
			$this->db->where('(
				a.applicant_first LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.applicant_id LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.applicant_last LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.applicant_middle	LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				CONCAT(a.applicant_first, \' \', a.applicant_last) LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.passport_number LIKE \'%'.addslashes( $search['q'] ).'%\' OR 
				a.position_name LIKE \'%'.addslashes( $search['q'] ).'%\' OR
				a.applicant_remarks LIKE \'%'.addslashes( $search['q'] ).'%\') OR
				a.applicant_id = '.$requirement_id.' OR
				a.applicant_id = '.$ticket_no_id.' OR
				a.applicant_id = '.$applicantNumber_id.' OR
				a.applicant_id = '.$certificate_id.'
			', null, false);
		}

		if ( $search['country'] > 0 ) {
			$this->db->where([
				'applicant_preferred_country' => $search['country']
			]);
		}

		if ( $search['position'] > 0 ) {
			$this->db->where([
				'applicant_preferred_position' => $search['position']
			]);
			$this->db->or_where_in('a.applicant_id',$all_apid_sub_pos);
		}

		if ( $search['employer'] > 0 ) {
			$this->db->where([
				'employer_id' => $search['employer']
			]);
		}

		if ( ! empty( $search['gender'] ) ) {
			$this->db->where([
				'applicant_gender' => $search['gender']
			]);
		}

		if ( $search['age']['from'] > 0 ) {
			$this->db->where( 'applicant_age BETWEEN '.(int) $search['age']['from'].' AND '.(int) $search['age']['to'],
				null, false );
		}

		if ( $search['salary']['from'] > 0 ) {
			$this->db->where( 'applicant_expected_salary BETWEEN '.
				(float) $search['salary']['from'].' AND '.(float) $search['salary']['to'], 
				null, false );
		}

		if ( isset( $search['date-applied']['from'], $search['date-applied']['to'] ) 
			&& date('Y-m-d', strtotime( $search['date-applied']['from'] )) != date('Y-m-d', strtotime(null))
			&& date('Y-m-d', strtotime( $search['date-applied']['to'] )) != date('Y-m-d', strtotime(null))
			) {

			$dateFrom = date('Y-m-d', strtotime( $search['date-applied']['from'] ));
			$dateTo   = date('Y-m-d', strtotime( $search['date-applied']['to'] ));

			$this->db->where( 'DATE(applicant_date_applied) BETWEEN \''.$dateFrom.'\' AND \''.$dateTo.'\'',
				null, false );
		}
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$this->db->group_by( 'a.applicant_id' );

		$applicants = $this->db->get()->result_array();
		// dd($this->db->last_query());
			
		return count($applicants);
	}


	public function getApplicantById( $applicantId )
	{	
		//Get Applicant Info
		$this->db->flush_cache();
         $this->db->from( 'applicant_view' )
		->where([
				'applicant_id'	=> $applicantId,
			]);
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicant               = $this->db->get()->row_array();
		$workExperiences         = $this->getApplicantWorkExperiences( $applicantId );
		$otherPreferredPositions = $this->getApplicantOtherPreferredPositions( $applicantId );
		$otherPreferredCountries = $this->getApplicantOtherPreferredCountries( $applicantId );
		
		$experiences = [];
		foreach ( $workExperiences as $experience ) {
			$experiences[ $experience['experience_id'] ] = $experience;
		}
		$workExperiences = $experiences;
		
		$positions = [];
		foreach ( $otherPreferredPositions as $position ) {
			$positions[ $position['position_id'] ] = $position;
		}
		$otherPreferredPositions = $positions;
		
		$countries = [];
		foreach ( $otherPreferredCountries as $country ) {
			$countries[ $country['country_id'] ] = $country;
		}
		$otherPreferredCountries = $countries;
		
		$applicant['experiences']               = $workExperiences;
		$applicant['other-preferred-positions'] = $otherPreferredPositions;
		$applicant['other-preferred-countries'] = $otherPreferredCountries;
		return $applicant;
	}

	function getApplicantCertificateById( $applicantId ){
		$query = $this->db->get_where('applicant_certificate', array('certificate_id' => $applicantId));
		$result =  $query->result();
		return $result[0];
	}

	function getApplicantRawById( $applicantId ){
		$query = $this->db->get_where(' applicant', array('applicant_id' => $applicantId));
		$result =  $query->result();
		return $result[0];
	}

	function getApplicantRequirementsById( $applicantId ){
		$query = $this->db->get_where('applicant_requirement', array('requirement_id' => $applicantId));
		$result =  $query->result();
		return $result[0];
	}	
	public function getCurrencyById($applicantId){
		$this->db->from( 'applicant' )
            ->where([
				'applicant_id'	=> $applicantId,
			]);
		$applicant = $this->db->get()->row_array();
		return $applicant['currency'];
	}
	
	//For admin/applicants/send_applicants
	public function getApplicantsByIds( $applicantIds )
	{
		$this->db->flush_cache();
		$this->db->from( 'applicant' )
			->where_in('applicant_id', $applicantIds);
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();

		return $applicants;
	}

	public function lineUpApplicants( $applicantIds, $employerId )
	{
		$this->db->flush_cache();
		$this->db->where_in( 'applicant_id', $applicantIds )
			->update( 'applicant', [
				'applicant_status'   => $this->status['Line Up'],
				'applicant_employer' => $employerId,
			]);
		foreach ($applicantIds as $applicantId) {
			$logInserted = $this->addLog( 'Send Applicant', $applicantId, $employerId, $this->status['Line Up'], date( 'Y-m-d', time() ) );
		}	
	}

	

public function getApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'])
	{
		if($_SESSION['admin']['user']['user_type'] == 9){
        	$user_rs = $this->db->get_where('user', array('team_lead_id' => $_SESSION['admin']['user']['user_id']));
        	foreach ($user_rs->result() as $key => $value) {
        		$users_rs_id[] = $value->user_id;
        	}

        	$this->db->flush_cache();
			$this->db->where_in('employer_user',$users_rs_id);
			$employers_all = $this->db->get('employer')->result_array();
			foreach ($employers_all as $key => $value) {
        		$employers_id[] = $value['employer_id'];
        	}
		}

		if($_SESSION['admin']['user']['user_type'] == 10){
        	$employers = $this->db->get_where('employer', array('employer_user' => $_SESSION['admin']['user']['user_id']));
        	foreach ($employers->result() as $key => $value) {
        		$employers_id[] = $value->employer_id;
        	}
		}

        $this->db->flush_cache();
        $this->db->from( 'applicant_view' ); 

		if(($_SESSION['admin']['user']['user_type'] == 10) || ($_SESSION['admin']['user']['user_type'] == 9)){
			$this->db->where_in('applicant_employer',$employers_id);
		}

        //For Selected
        $this->db->join( 'employer_selected', 'selected_employer = applicant_employer AND selected_applicant = applicant_id', 'left' );

        //For Deployed
        $this->db->join( 'deployed', 'deployed_employer = applicant_employer AND deployed_applicant = applicant_id', 'left' );

		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1'); 
		$applicants = $this->db->get()->result_array();
        
        return $this->indexArray( $applicants, 'applicant_id' );
	}
	
	public function getApplicantsCount( $options = [] )
	{
		if($_SESSION['admin']['user']['user_type'] == 9){
        	$user_rs = $this->db->get_where('user', array('team_lead_id' => $_SESSION['admin']['user']['user_id']));
        	foreach ($user_rs->result() as $key => $value) {
        		$users_rs_id[] = $value->user_id;
        	}

        	$this->db->flush_cache();
			$this->db->where_in('employer_user',$users_rs_id);
			$employers_all = $this->db->get('employer')->result_array();
			foreach ($employers_all as $key => $value) {
        		$employers_id[] = $value['employer_id'];
        	}
		}

		if($_SESSION['admin']['user']['user_type'] == 10){
        	$employers = $this->db->get_where('employer', array('employer_user' => $_SESSION['admin']['user']['user_id']));
        	foreach ($employers->result() as $key => $value) {
        		$employers_id[] = $value->employer_id;
        	}
		}


		$this->db->flush_cache();
		$this->db->from( 'applicant_view' ); 
		
		if(($_SESSION['admin']['user']['user_type'] == 10) || ($_SESSION['admin']['user']['user_type'] == 9)){
			$this->db->where_in('applicant_employer',$employers_id);
		}
		
		$this->setDBQueryOptions( $options );
		
		$applicants = $this->db->count_all_results();

		return $applicants;
	}

	public function cyd_get_multiple_employer( $applicant_id = [] )
	{
		$this->db->flush_cache();
		$this->db->from( 'multiple_lineups' )->where([
                'applicant_id' => $applicant_id,
            ]);
        $this->db->group_by('applicant_employer');
		$lineup_ids = $this->db->get()->result_array();
		
		$result = '';
		foreach ($lineup_ids as $lineup_id) {
			$this->db->flush_cache();
			$this->db->from( 'employer' )->where([
                'employer_id' => $lineup_id['applicant_employer'],
            ]);
            $tbl_employer = $this->db->get()->result_array();
            $result .= $tbl_employer[0]['employer_name'].', ';
		}
		return $result;
	}
    
    public function getPreSelected( $options = [], $limit = 0, $offset = 0, $sort = ['reservation_expiration', 'ASC'])
	{
        $this->db->flush_cache();
	
        $this->db->from( 'applicant_view' )
			->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
            ->where([
                'applicant_status' => $this->status['Pre-Selected'],
            ]);
		
		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1'); 
		$applicants = $this->db->get()->result_array();
        
        return $this->indexArray( $applicants, 'applicant_id' );
	}
    public function getPreSelectedCount( $options = [] )
	{
        $this->db->flush_cache();
	
        $this->db->from( 'applicant_view' )
			->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
            ->where([
                'applicant_status' => $this->status['Pre-Selected'],
            ]);
		
		$this->setDBQueryOptions( $options );
		$this->db->query('SET SQL_BIG_SELECTS=1'); 
		$applicants = $this->db->count_all_results();

		return $applicants;
	}

     public function getForBooking( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_id', 'DESC'])
	{
        $this->db->flush_cache();
	
        $this->db->from( 'applicant_view' )
			 ->where([
                'applicant_status' =>(int) 8,
            ]);
		
		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1'); 
		$applicants = $this->db->get()->result_array();
        
        return $this->indexArray( $applicants, 'applicant_id' );
	}
    public function getForBookingCount( $options = [] )
	{
        $this->db->flush_cache();
	
        $this->db->from( 'applicant_view' )
		   ->where([
                'applicant_status' =>(int) 8,
            ]);
		
		$this->setDBQueryOptions( $options );
		$this->db->query('SET SQL_BIG_SELECTS=1'); 
		$applicants = $this->db->count_all_results();

		return $applicants;
	}

	public function getReservedApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['reservation_expiration', 'ASC'])
	{
        $this->db->flush_cache();
	
        $this->db->from( 'applicant_view' )
			->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
            ->where([
                'applicant_status' => $this->status['Reserved'],
            ]);
		
		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1'); 
		$applicants = $this->db->get()->result_array();
        
        return $this->indexArray( $applicants, 'applicant_id' );
	}
    public function getReservedApplicantsCount( $options = [] )
	{
        $this->db->flush_cache();
	
        $this->db->from( 'applicant_view' )
			->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
            ->where([
                'applicant_status' => $this->status['Reserved'],
            ]);
		
		$this->setDBQueryOptions( $options );
		$this->db->query('SET SQL_BIG_SELECTS=1'); 
		$applicants = $this->db->count_all_results();

		return $applicants;
	}
    
    public function getExpiredReservedApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'reservation_expiration', 'ASC' ] )
    {
        $this->db->flush_cache();
		
        $this->db->from( 'applicant_view' )
            ->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
            ->where([
                'applicant_status'          => $this->status['Reserved'],
                'reservation_expiration <=' => date( 'Y-m-d', time() ),
                'reservation_expiration >'  => DATE_EMPTY,
            ]);
        
        $this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1'); 
		$applicants = $this->db->get()->result_array();
        
        return $this->indexArray( $applicants, 'applicant_id' );
    }
    
    public function getExpiredReservedApplicantsCount( $options = [] )
    {
        $this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            ->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
            ->where([
                'applicant_status'          => $this->status['Reserved'],
                'reservation_expiration <=' => date( 'Y-m-d', time() ),
                'reservation_expiration >'  => DATE_EMPTY,
            ]);
        
        $this->setDBQueryOptions( $options );

		$applicants = $this->db->count_all_results();

		return $applicants; 
    }

    public function getExpiredMedicalApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'certificate_medical_expiration', 'ASC' ] )
    {
    	$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
        	->where([
                'certificate_medical_clinic !='     => '',
        		'certificate_medical_expiration <=' => date( 'Y-m-d', time() + 60*60*24*14 ),
        		'certificate_medical_expiration >'  => DATE_EMPTY,
        	]);
    		$notinclude = array(1,7,9);
            $this->db->where_not_in('applicant_status',$notinclude);
        
			$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();
        
        return $this->indexArray( $applicants, 'applicant_id' );
    }

    public function getExpiredMedicalApplicantsCount( $options = [] )
    {
        $this->db->flush_cache();
        $this->db->from( 'applicant_view' )
        	->where([
                'certificate_medical_clinic !='     => '',
        		'certificate_medical_expiration <=' => date( 'Y-m-d', time() + 60*60*24*14 ),
        		'certificate_medical_expiration >'  => DATE_EMPTY,
        	]);
    		$notinclude = array(1,7,9);
            $this->db->where_not_in('applicant_status',$notinclude);
        
        $this->setDBQueryOptions( $options );

		$applicants = $this->db->count_all_results();

		return $applicants; 
    }

    public function getExpiredVisaApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'requirement_visa_expiration', 'ASC' ] )
    {
    	$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            ->where([
                'requirement_visa'               => 1,
                'requirement_visa_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30 ),
                'requirement_visa_expiration >'  => DATE_EMPTY,
            ]);

    		$notinclude = array(1,7,9);
            $this->db->where_not_in('applicant_status',$notinclude);
        
        $this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
     $this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();

        return $this->indexArray( $applicants, 'applicant_id' );
    }

    public function getExpiredVisaApplicantsCount( $options = [] )
    {
        $this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            ->where([
                'requirement_visa'               => 1,
                'requirement_visa_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30 ),
            ]);

    		$notinclude = array(1,7,9);
            $this->db->where_not_in('applicant_status',$notinclude);
        
        $this->setDBQueryOptions( $options );

		$applicants = $this->db->count_all_results();

		return $applicants; 
    }

    public function getExpiredPassportsApplicants( $options = [], $limit = 0, $offset = 0, $sort = [ 'passport_expiration', 'ASC' ] )
    {
    	$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            // ->where_in( 'applicant_status', [
            //     $this->m_applicant->status['Selected'],
            //     $this->m_applicant->status['Reserved'],
            //     $this->m_applicant->status['For Deployment'],
            // ])
            ->where([
                'passport_number !='     => '',
                'passport_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30*8 ),
                'passport_expiration >'  => DATE_EMPTY,
            ]);

    		$notinclude = array(1,7,9);
            $this->db->where_not_in('applicant_status',$notinclude);
        
			$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();
        
        return $this->indexArray( $applicants, 'applicant_id' );
    }

    public function getExpiredPassportsApplicantsCount( $options = [] )
    {
        $this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            // ->where_in( 'applicant_status', [
            //     $this->m_applicant->status['Selected'],
            //     $this->m_applicant->status['Reserved'],
            //     $this->m_applicant->status['For Deployment'],
            // ])
            ->where([
                'passport_number !='     => null,
                'passport_number !='     => '',
                'passport_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30*8 ),
                'passport_expiration >'  => DATE_EMPTY,
            ]);

    		$notinclude = array(1,7,9);
            $this->db->where_not_in('applicant_status',$notinclude);
        
        $this->setDBQueryOptions( $options );

		$applicants = $this->db->count_all_results();

		return $applicants; 
    }
	
	
	
	
	public function getLineUpApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
	{
		$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            ->where([
                'applicant_status'     => $this->status['Line Up'],
            ]);
        
        $this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();
		
		return $this->indexArray( $applicants, 'applicant_id' );
	} 

	public function cyd_getLineUpApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
	{
		$this->db->flush_cache();
		$this->db->select('*');
		$this->db->from('applicant_view');
		$this->db->join('multiple_lineups', 'applicant_view.applicant_id = multiple_lineups.applicant_id');
		$this->db->where([
                'applicant_status' => $this->status['Line Up'],
                'multiple_lineups.applicant_employer' => $options['where'][0]['applicant_employer'],
            ]);
		$this->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1');
        
		$applicants = $this->db->get()->result_array();
		
		return $this->indexArray( $applicants, 'applicant_id' );
	} 

	public function getLineUpApplicantsCount( $options = [] )
	{
		$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            ->join( 'employer_selected', 'selected_applicant = applicant_id' )
            ->where([
                'applicant_status'     => $this->status['Line Up'],
            ]);
        
        $this->setDBQueryOptions( $options );

		$applicants = $this->db->count_all_results();

		return $applicants;
	}


	
	
	
	
	
	
	
	
	public function getSelectedApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['applicant_updated', 'DESC'] )
	{
		$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            ->join( 'employer_selected', 'selected_applicant = applicant_id' )
            ->where([
                'applicant_status'     => $this->status['Selected'],
            ]);
        
        $this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();

		return $this->indexArray( $applicants, 'applicant_id' );
	} 
	
	public function getSelectedApplicantsCount( $options = [] )
	{
		$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            ->join( 'employer_selected', 'selected_applicant = applicant_id' )
            ->where([
                'applicant_status'     => $this->status['Selected'],
            ]);
        
        $this->setDBQueryOptions( $options );
		
		$applicants = $this->db->count_all_results();

		return $applicants;
	}

	public function getDeployedApplicants( $options = [], $limit = 0, $offset = 0, $sort = ['deployed_date', 'DESC'] )
	{
		$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
			->join( 'deployed', 'deployed_applicant = applicant_id', 'inner' )
            ->where([
                'applicant_status'     => $this->status['Deployed'],
            ]);
        
        $this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$applicants = $this->db->get()->result_array();

		return $this->indexArray( $applicants, 'applicant_id' );
	} 
	
	public function getDeployedApplicantsCount( $options = [] )
	{
		$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
			->join( 'deployed', 'deployed_applicant = applicant_id', 'inner' )
            ->where([
                'applicant_status'     => $this->status['Deployed'],
            ]);
        
        $this->setDBQueryOptions( $options );

		$applicants = $this->db->count_all_results();

		return $applicants;
	}
	
	public function getApplicantWorkExperiences( $applicantId )
	{
		//Get Work Experiences
		$this->db->flush_cache();
		$this->db->from('applicant_experiences')
			->where([
				'experience_applicant'	=> $applicantId,
			]);
			$this->db->query('SET SQL_BIG_SELECTS=1');
		$experiences = $this->db->get()->result_array();
        
        return $this->indexArray( $experiences, 'experience_id' );
	}

	public function getApplicantOtherPreferredPositions( $applicantId )
	{
		$this->db->flush_cache();
		$this->db->select( 'p.*' )
			->from('position p')
			->join('applicant_preferred_positions pp', 'pp.position_position = p.position_id')
			->where([
				'pp.position_applicant' => $applicantId,
			]);
			$this->db->query('SET SQL_BIG_SELECTS=1');
		$positions = $this->db->get()->result_array();
        
        return $this->indexArray( $positions, 'position_id' );
	}
	
	public function getApplicantOtherPreferredCountries( $applicantId )
	{
		$this->db->flush_cache();
		$this->db->select( 'c.*' )
			->from('country c')
			->join('applicant_preferred_countries pc', 'pc.country_country = c.country_id')
			->where([
				'pc.country_applicant' => $applicantId,
			]);
			$this->db->query('SET SQL_BIG_SELECTS=1');
		$countries = $this->db->get()->result_array();
        
        return $this->indexArray( $countries, 'country_id' );
	}
    
    public function getApplicantFiles( $applicantId, $options = [], $limit = 0, $offset = 0 )
    {
        $this->db->flush_cache();
        $this->db->from( 'applicant_files' )
            ->join( 'user', 'user_id = file_createdby' )
            ->where([
                'file_applicant' => $applicantId,
                'file_status'    => 1,
            ]);
        
        $this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset );
            
        $this->db->order_by( 'file_created', 'DESC' );
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $files = $this->db->get()->result_array();
        
        return $this->indexArray( $files, 'file_type' );
    }

    public function getApplicantFileByType( $applicantId, $type )
    {
    	$this->db->flush_cache();
    	$this->db->from('applicant_files')
    		->where([
    			'file_applicant' => $applicantId,
    			'file_type'      => $type,
    		]);
		$this->db->query('SET SQL_BIG_SELECTS=1');
    	$file = $this->db->get()->row_array();

    	return $file;
    }
    
    public function getApplicantFileById( $fileId )
    {
        $this->db->flush_cache();
        $this->db->from( 'applicant_files' )
            ->join( 'user', 'user_id = file_createdby' )
            ->where([
                'file_id' => $fileId,
            ]);
        
        $file = $this->db->get()->row_array();
        
        return $file;
    }
    
    public function getApplicantLogs( $applicantId, $options = [], $limit = 0, $offset = 0, $sort = [ 'log_created', 'DESC' ] )
    {
        $this->db->flush_cache();

        $this->db->from( 'applicants_logs_view' )
            ->where([
                'log_applicant' => $applicantId,
            ]);

        $this->setDBQueryOptions( $options )
            ->setDBQueryRange( $limit, $offset )
            ->setDBQueryOrders( $sort );
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $logs = $this->db->get()->result_array();
        
        return $this->indexArray( $logs, 'log_id' );
    }


    public function getStatus( $applicantId )
    {
    	$this->db->flush_cache();
    	$this->db->from( 'applicant_view' )
    		->where([
    			'applicant_id' => $applicantId,
    		]);
		$this->db->query('SET SQL_BIG_SELECTS=1');
    	$applicant = $this->db->get()->row_array();

    	$response = [
    		'employer' => $applicant['applicant_employer'],
    		'country'  => $applicant['country_name'],
    		'status'   => $applicant['applicant_status'],
    		'date'     => date( 'Y-m-d', time() ),
    		'remarks'  => null,
    	];

    	$this->db->flush_cache();
    	$this->db->from( 'applicant_log' )
    		->where([
    			'log_applicant' => $applicantId,
    			'log_status'    => $applicant['applicant_status'],
    		])
    		->order_by( 'log_created', 'DESC' )
    		->limit(1);
		$this->db->query('SET SQL_BIG_SELECTS=1');
    	$log = $this->db->get()->row_array();

    	if ( ! empty( $log ) ) {

    		$response = array_merge( $response, [
    			'status'   => $log['log_status'],
    			'date'     => date( 'Y-m-d', strtotime( $log['log_date'] ) ),
	    		'remarks'  => $log['log_remarks'],
    		]);
    	}

    	return $response;
    }

    public function getAllLogs( $options = [], $limit = 0, $offset = 0, $sort = [ 'log_date', 'DESC' ] )
    {
        $this->db->from( 'applicants_logs_view' );
        
        $this->setDBQueryOptions( $options )
            ->setDBQueryRange( $limit, $offset )
            ->setDBQueryOrders( $sort );
          $this->db->query('SET SQL_BIG_SELECTS=1');				
        $logs = $this->db->get()->result_array();
        
        return $this->indexArray( $logs, 'log_id' );
    }

    public function getCounters()
    {
    	$this->db->query('SET SQL_BIG_SELECTS=1');
		$counter = [];

    	for ( $m = 6; $m >= 0; $m-- ) {

    		$month    = date('Y-F', strtotime('-'.$m.' months'));
			$dateFrom = date( 'Y-m-01', strtotime( '-'.$m.' months' ) );
			$dateTo   = date( 'Y-m-t', strtotime( '-'.$m.' months' ) );

			$this->db->flush_cache();
			$this->db->from( 'applicant' )
				->where( "`applicant_date_applied` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false);

			$counter['applied'][$month] = $this->db->count_all_results();

			$this->db->flush_cache();
			$this->db->from( 'applicant_view' );
			$this->db->where([
				'applicant_status' => $this->status['Deployed'],
			]);
			$this->db->join( 'deployed', 'deployed_applicant = applicant_id' );
			$this->db->where(
				"DATE(deployed_date) BETWEEN '".$dateFrom."' AND '".$dateTo."'",
				null, false);
			$this->db->where("deployed_id = (SELECT deployed_id FROM deployed WHERE deployed_applicant = applicant_id ORDER BY deployed_created DESC LIMIT 1)", null, false);

			$counter['deployed'][$month] = $this->db->count_all_results();
			
			/*
			$this->db->from( 'applicant_log' )
				->join( 'applicant', 'applicant_id = log_applicant' )
				->where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
				->where([
					'log_status'       => $this->status['Deployed'],
				]);
			$counter['deployed'][$month] = $this->db->count_all_results();
*/

			$this->db->flush_cache();
			$this->db->from( 'applicant_log' )
				->where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
				->where([
					'log_status'       => $this->status['Reserved'],
				]);

			$counter['reserved'][$month] = $this->db->count_all_results();

			$this->db->flush_cache();
			$this->db->from( 'applicant_log' )
				->where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
				->where([
					'log_status'       => $this->status['Selected'],
				]);

			$counter['Selected'][$month] = $this->db->count_all_results();

			$this->db->flush_cache();
			$this->db->from( 'applicant_log' )
				->where( "`log_created` BETWEEN '".$dateFrom."' AND '".$dateTo."' ", null, false)
				->where([
					'log_status'       => $this->status['For Deployment'],
				]);

			$counter['booking'][$month] = $this->db->count_all_results();
		}

		$response = [];

		foreach ( $counter as $status => $months ) {
			foreach ( $counter[$status] as $month => $count ) {
				$response[$month][$status] = $count;
			}
		}

		return $response;
    }

    public function getBalance( $applicantId )
	{
		//order by created desc
		$this->db->flush_cache();

		$this->db->from( 'bill' )
			->where([
				'bill_applicant' => $applicantId,
				'bill_status'    => 0,
			]);

		$bill = $this->db->get()->row_array();

		if ( empty( $bill ) ) {
			return 0.0;
		}

		$balance = $bill['bill_applicant_deposit'] > $bill['bill_applicant_cost'] ? 0 : $bill['bill_applicant_cost'] - $bill['bill_applicant_deposit'];

		return $balance;
	}
	
	public function addApplicant()
	{	
		$applicant          = $_POST['applicant'];

		$basic				= 
		$passport			=
		$visa               =
		$education			=
		$experiences		=
		$applicantData		= 
		$preferredPositions =
		$preferredCountries =
		$passportData		=
		$educationData		=		
		$experiencesData	=
   		$visaData		    =
        $certificateData    = 
        $requirementData    =
		$logData            = [];
		
		$basic 				= $applicant['basic'];
		$passport 			= $applicant['passport'];
		$education 			= $applicant['education'];
		$experiences 		= isset( $applicant['work-experience'] ) ? $applicant['work-experience'] : [];
		
		//Start Transaction
		$this->db->trans_begin();

		if(!isset($applicant['currency']))
			$applicant['currency'] = 'PHP';

		//Applicant

		$applicantData = [
			'applicant_first'			   => ucwords( $basic['first'] ),
			'applicant_middle'			   => ucwords( $basic['middle'] ),
			'applicant_last'		 	   => ucwords( $basic['last'] ),
			//'applicant_suffix'		 	=> $basic['suffix'],
			'applicant_birthdate'		   => date('Y-m-d', strtotime( $basic['birthdate'] ) ),
			'applicant_age'				   => 0, //This will compute automatically be the db trigger
			'applicant_gender'			   => $basic['gender'],
			'applicant_contacts'           => $basic['contacts'],
			'applicant_address'			   => $basic['address'],
			'applicant_email'			   => $basic['email'],
			'applicant_nationality'		   => $basic['nationality'],
			'applicant_civil_status'	   => $basic['status'],
			'applicant_religion'		   => $basic['religion'],
			'applicant_languages'		   => $basic['languages'],
			'applicant_height'			   => $basic['height'],
			'applicant_weight'			   => $basic['weight'],
			'applicant_position_type'	   => $applicant['type'],
			'currency'  				   => $applicant['currency'],
			'applicant_mothers' 		   => $basic['applicant_mothers'],
			'applicant_children' 		   => $basic['applicant_children'],
			'applicant_preferred_position' => $applicant['preferred-position'],
			'applicant_expected_salary'    => $applicant['expected-salary'],
			'applicant_preferred_country'  => $applicant['preferred-country'],
			'applicant_other_skills'       => $applicant['other-skills'],
			'applicant_cv'				   => '',
			'applicant_photo'			   => '',
			'applicant_status'			   => $this->status['For Review'], //Available, Not Selected
            'applicant_source'			   => $applicant['source'],
			'applicant_remarks'			   => $applicant['remarks'],
			'applicant_date_applied'       => date( 'Y-m-d', strtotime( $applicant['date-applied'] ) ),
			'applicant_createdby'		   => isset( $_SESSION['admin']['user']['user_id'] )
											  ? $_SESSION['admin']['user']['user_id']
											  : 0,
			'applicant_updatedby'		   => isset( $_SESSION['admin']['user']['user_id'] )
											  ? $_SESSION['admin']['user']['user_id']
											  : 0,
			'applicant_created'			   => date('Y-m-d H:i:s', time()),
			'applicant_updated'			   => date('Y-m-d H:i:s', time()),
		];
		
        $this->db->flush_cache();
		$applicantInserted	= $this->db->insert('applicant', $applicantData);
		$applicantId 		= $this->db->insert_id();

		//Upload photo
		if ( isset( $_FILES['applicant']['name']['photo'] ) ) {
			$file = [
				'name'     => $_FILES['applicant']['name']['photo'],
				'type'     => $_FILES['applicant']['type']['photo'],
				'tmp_name' => $_FILES['applicant']['tmp_name']['photo'],
				'error'    => $_FILES['applicant']['error']['photo'],
				'size'     => $_FILES['applicant']['size']['photo'],
			];

			if ( ! $file['error'] ) {
				$filePath = $this->uploadPhoto( $applicantId, $file );

				$this->db->flush_cache();
				$this->db->where([
						'applicant_id' => $applicantId,
					])->update( 'applicant', [
						'applicant_photo' => $filePath,
					]);
			}
		}
        
        //Create applicant slug
        $this->db->flush_cache();
        $this->db->where([
                'applicant_id' => $applicantId,
            ])->update( 'applicant', [ 
                'applicant_slug' => str_pad( $applicantId, 10, '0', STR_PAD_LEFT ).'/'
                .strSlug( $basic['first'].' '.$basic['middle'].' '.$basic['last'] ) 
            ]);
		
		//Other preferred positions
		if ( isset( $applicant['other-preferred-positions'] ) && count( $applicant['other-preferred-positions'] ) > 0 ) {
			foreach( $applicant['other-preferred-positions'] as $position) {
				$preferredPositions[] = [
					'position_applicant'   => $applicantId,
					'position_position'    => $position,
					'position_createdby'   => isset( $_SESSION['admin']['user']['user_id'] )
											  ? $_SESSION['admin']['user']['user_id']
											  : 0,
					'position_created'     => date( 'Y-m-d H:i:s', time() ),
				];
			}
			
			if ( count( $preferredPositions ) > 0 ) {
                $this->db->flush_cache();
				$this->db->insert_batch( 'applicant_preferred_positions', $preferredPositions );
			}
		}
		
		//Other preferred countries
		if ( isset( $applicant['other-preferred-countries'] ) && count( $applicant['other-preferred-countries'] ) > 0 ) {
			foreach( $applicant['other-preferred-countries'] as $country) {
				$preferredCountries[] = [
					'country_applicant'  => $applicantId,
					'country_country'    => $country,
					'country_createdby'  => isset( $_SESSION['admin']['user']['user_id'] )
										    ? $_SESSION['admin']['user']['user_id']
										    : 0,
					'country_created'    => date( 'Y-m-d H:i:s', time() ),
				];
			}
			
			if ( count( $preferredCountries ) > 0 ) {
                $this->db->flush_cache();
				$this->db->insert_batch( 'applicant_preferred_countries', $preferredCountries );
			}
		}
		
		//Passport
        $passportData = [
            'passport_applicant'	=> $applicantId,
            'passport_number'		=> $passport['number'],
            'passport_issue'        => date( 'Y-m-d', strtotime( $passport['issue'] ) ),
			'passport_issue_place'  => $passport['issue-place'],
            'passport_expiration'	=> date('Y-m-d', strtotime( $passport['expiration'] ) ),
            'passport_createdby'	=> isset( $_SESSION['admin']['user']['user_id'] )
									   ? $_SESSION['admin']['user']['user_id']
									   : 0,
            'passport_updatedby'	=> isset( $_SESSION['admin']['user']['user_id'] )
									   ? $_SESSION['admin']['user']['user_id']
									   : 0,
            'passport_created'		=> date( 'Y-m-d H:i:s', time() ),
            'passport_updated'		=> date( 'Y-m-d H:i:s', time() ),
        ];
        
        $this->db->flush_cache();
        $passportInserted 	= $this->db->insert( 'applicant_passport', $passportData );
		$passportId 		= $this->db->insert_id();
        
		//Education
		if ( $applicantId && isset( $applicant['education'] ) ) {
			$educationData = [
				'education_applicant'		=> $applicantId,
				'education_mba'				=> $education['mba'],
				'education_mba_course'		=> $education['mba-course'],
				'education_mba_year'		=> $education['mba-year'],
				'education_college'			=> $education['college'],
				'education_college_skills'	=> $education['college-skills'],
				'education_college_year'	=> $education['college-year'],
				'education_others'			=> $education['others'],
				'education_others_year'		=> $education['others-year'],
				'education_highschool'		=> $education['highschool'],
				'education_highschool_year'	=> $education['highschool-year'],
				'education_createdby'		=> isset( $_SESSION['admin']['user']['user_id'] )
											   ? $_SESSION['admin']['user']['user_id']
											   : 0,
				'education_updatedby'		=> isset( $_SESSION['admin']['user']['user_id'] )
											   ? $_SESSION['admin']['user']['user_id']
											   : 0,
				'education_created'			=> date('Y-m-d H:i:s', time()),
				'education_updated'			=> date('Y-m-d H:i:s', time()),
			];
			
            $this->db->flush_cache();
			$educationInserted 	= $this->db->insert('applicant_education', $educationData);
			$educationId 		= $this->db->insert_id();
		}
		
		//Work Experience
		if ( $applicantId && isset( $applicant['work-experience'] ) ) {
			for ( $i = 0; $i < count( $experiences['company'] ); $i++ ) {
				if ( empty( $experiences['company'][$i] ) || empty( $experiences['position'][$i] ) ) {
					continue;
				}
				
				$experiencesData[] = [
					'experience_applicant'	=> $applicantId,
					'experience_company'	=> $experiences['company'][$i],
					'experience_position'	=> $experiences['position'][$i],
					'experience_salary'		=> $experiences['salary'][$i],
					'hospital_level'		=> $experiences['hospital_level'][$i],
					'experience_country'	=> $experiences['country'][$i],
					'experience_from'		=> $experiences['from'][$i],
					'experience_to'			=> $experiences['to'][$i],
					'experience_years'		=> $experiences['years'][$i],
					'experience_createdby'	=> isset( $_SESSION['admin']['user']['user_id'] )
											   ? $_SESSION['admin']['user']['user_id']
											   : 0,
					'experience_created'	=> date( 'Y-m-d H:i:s', time() ),
				];
			}
			
			if ( count( $experiencesData ) > 0 ) {
                $this->db->flush_cache();
				$experienceInserted = $this->db->insert_batch('applicant_experiences', $experiencesData);
			}
		}
        
        //Visa
  //       $visaData = $this->rawApplicantVisa([ 'visa_applicant' => $applicantId]);
		// $this->db->flush_cache();
		// $visaInserted = $this->db->insert( 'applicant_visa', $visaData);
		// $visaId       = $this->db->insert_id();
        
        //Certificates
        $certificateData     = $this->rawApplicantCertificate( [ 'certificate_applicant' => $applicantId ] );
        $this->db->flush_cache();
        $certificateInserted = $this->db->insert( 'applicant_certificate', $certificateData);
		$certificateId       = $this->db->insert_id();
        
		//Requirements
        $requirementData     = $this->rawApplicantRequirements( [ 'requirement_applicant' => $applicantId ] );
        $this->db->flush_cache();
        $requirementInserted = $this->db->insert( 'applicant_requirement', $requirementData);
		$requirementId       = $this->db->insert_id();
        
		$applicant = $this->getApplicantById( $applicantId );
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $applicantInserted) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
        
        $this->addLog('Applicant has been registered.', $applicantId, 0, $this->status['Available'], date( 'Y-m-d', time() ) );
		
		//Commit transaction
		$this->db->trans_commit();	
		
		return $applicant;
	}
    	
    public function changePhoto( $applicantId )
    {
		$file = [
			'name'     => $_FILES['applicant']['name']['photo'],
			'type'     => $_FILES['applicant']['type']['photo'],
			'tmp_name' => $_FILES['applicant']['tmp_name']['photo'],
			'error'    => $_FILES['applicant']['error']['photo'],
			'size'     => $_FILES['applicant']['size']['photo'],
		];

		if ( ! $file['error'] ) {
			$filePath = $this->uploadPhoto( $applicantId, $file );

			$this->db->flush_cache();
			$this->db->where([
					'applicant_id' => $applicantId,
				])->update( 'applicant', [
					'applicant_photo' => $filePath,
				]);

			return true;
		}
		
		return false;	
    }

    public function addLog( $remarks, $applicantId, $employerId, $status = null, $date = null )
    {
        $applicant =
        $logData   = [];
        
        //Start Transaction
		$this->db->trans_begin();
        
        //Get applicant info
        $applicant = 
        $this->db->from( 'applicant' )
            ->where([
                'applicant_id' => $applicantId,
            ])
            ->get()->row_array();
        
        //Prefer log data

		$cyd_get_id = $this->cyd_get_id();      
        if(!isset($cyd_get_id)){
        	$cyd_get_id = 0;
        }

        $logData = [
			'log_applicant'  => $applicantId,
            'log_employer'   => $employerId,
			'log_status'     => is_null( $status ) ? $applicant['applicant_status'] : $status,
			'log_country'    => $applicant['applicant_preferred_country'],
            'log_date'       => is_null( $date ) ? date( 'Y-m-d', time() ) : $date,
			'log_remarks'    => $remarks,
			'log_createdby'  => $cyd_get_id,
			'log_created'    => date( 'Y-m-d H:i:s', time() ),
		];
        
        //Insert
        $this->db->flush_cache();
        $this->db->insert( 'applicant_log', $logData );
        
        //Rollback if transaction fails
		if ( ! $this->db->trans_status() ) {
			$this->db->trans_rollback();
			return false;
		}
		
		//Commit transaction
		$this->db->trans_commit();
        
        return true;
    }

    function cyd_get_id(){
    	$logid = 0;
    	if(isset($_SESSION['admin']['user']['user_id']))
    		$logid = $_SESSION['admin']['user']['user_id'];
    	else
	    	$logid = $_SESSION['employer']['user']['employer_id'];

	    return $logid;
    }

	public function updateApplicantProfile( $applicantId )
	{
		$applicant = $this->getApplicantById( $applicantId );

		$post      = $_POST['applicant'];

		$basic                    =
		$passport                 =
        $visa                     =
		$education                =
		$preferredPositions       =
		$preferredPositionsRemove =
		$preferredPositionsData   =
		$preferredCountries       =
		$preferredCountriesRemove =
		$preferredCountriesData   =
		$passportData             =
		$oldWorkExperiences       =
		$newWorkExperiences       =
		$workExperiencesRemove    =
		$workExperiencesData       =
        $visaData                 = [];
		
		$basic                    = $post['basic'];
		$passport                 = $post['passport'];
		$education                = $post['education'];
		$preferredPositions       = isset( $post['other-preferred-positions'] ) ? $post['other-preferred-positions'] : [];
		$preferredCountries       = isset( $post['other-preferred-countries'] ) ? $post['other-preferred-countries'] : [];
		$oldWorkExperiences       = isset( $post['work-experience-old'] ) ? $post['work-experience-old'] : [];
		$newWorkExperiences       = isset( $post['work-experience'] ) ? $post['work-experience'] : [];
		
		//Start Transaction
		//$this->db->trans_begin();
		
		//Update applicant profile
		$applicantData = [
			'applicant_first'			   => ucwords( $basic['first'] ),
			'applicant_middle'			   => ucwords( $basic['middle'] ),
			'applicant_last'		 	   => ucwords( $basic['last'] ),
			'applicant_birthdate'          => date('Y-m-d', strtotime( $basic['birthdate'] ) ),
			'applicant_age'				   => 0, //This will compute automatically be the db trigger
			'applicant_gender'			   => $basic['gender'],
			'applicant_contacts'           => $basic['contacts'],
			'applicant_address'			   => $basic['address'],
			'applicant_email'			   => $basic['email'],
			'applicant_nationality'		   => $basic['nationality'],
			'applicant_civil_status'	   => $basic['status'],
			'applicant_religion'		   => $basic['religion'],
			'applicant_languages'		   => $basic['languages'],
			'applicant_height'			   => $basic['height'],
			'applicant_weight'			   => $basic['weight'],
			'applicant_preferred_position' => $post['preferred-position'],
			'currency' 					   => $post['currency'],
			'applicant_children'		   => $basic['children'],
			'applicant_mothers'			   => $basic['mothers'],
			'applicant_expected_salary'    => $post['expected-salary'],
			'applicant_preferred_country'  => $post['preferred-country'],
			'applicant_other_skills'       => $post['other-skills'],
            'applicant_source'			   => $post['source'],
			 'applicant_position_type'	   => $post['type'],
			'other_source'			   => $basic['other_source'],
			'applicant_incase_name'		 => $basic['applicant_incase_name'],
			'applicant_incase_relation'	 => $basic['applicant_incase_relation'],
			'applicant_incase_contact'	 => $basic['applicant_incase_contact'],
			'applicant_incase_address'	 => $basic['applicant_incase_address'],
			'applicant_remarks'            => $post['remarks'],
			'applicant_slug'               => str_pad( $applicantId, 10, '0', STR_PAD_LEFT )
											 .'/'.strSlug( $basic['first'].' '.$basic['middle'].' '.$basic['last'] ),
			'applicant_date_applied'       => date( 'Y-m-d', strtotime( $post['date-applied'] ) ),
			'applicant_updatedby'          => $_SESSION['admin']['user']['user_id'],
			'applicant_updated'            => date('Y-m-d H:i:s', time()),
		];
		
		$this->db->flush_cache();
		$applicantUpdated =
			$this->db->where([
				'applicant_id' => $applicantId,
			])
			->update( 'applicant', $applicantData );

		//Remove preferred positions
		foreach ( $applicant['other-preferred-positions'] as $position ) {
			if ( ! in_array( $position['position_id'], $preferredPositions ) ) {
				$preferredPositionsRemove[] = $position['position_id'];
				continue;
			}
		}

		if ( count( $preferredPositionsRemove ) > 0 ) {
			$this->db->flush_cache();
			$this->db->where_in( 'position_position', $preferredPositionsRemove )
				->where([
					'position_applicant' => $applicantId,
				])
				->delete( 'applicant_preferred_positions' );
		}
		
		//Add preferred Positions
		foreach ( $preferredPositions as $positionId ) {
			if ( ! array_key_exists( $positionId, $applicant['other-preferred-positions'] ) ) {
				$preferredPositionsData[] = [
					'position_applicant'   => $applicantId,
					'position_position'    => $positionId,
					'position_createdby'   => $_SESSION['admin']['user']['user_id'],
					'position_created'     => date( 'Y-m-d H:i:s', time() ),
				];
			}
		}

		if ( count( $preferredPositionsData ) > 0 ) {
			$this->db->flush_cache();
			$this->db->insert_batch( 'applicant_preferred_positions', $preferredPositionsData );
		}
		
		//Remove preferred countries
		foreach ( $applicant['other-preferred-countries'] as $country ) {
			if ( ! in_array( $country['country_id'], $preferredCountries ) ) {
				$preferredCountriesRemove[] = $country['country_id'];
				continue;
			}
		}
		
		if ( count( $preferredCountriesRemove ) > 0) {
			$this->db->flush_cache();
			$this->db->where_in( 'country_country', $preferredCountriesRemove )
				->where([
					'country_applicant' => $applicantId,
				])
				->delete( 'applicant_preferred_countries' );
		}
		//Add preferred Countries
		foreach ( $preferredCountries as $countryId ) {
			if ( ! array_key_exists( $countryId, $applicant['other-preferred-countries'] ) ) {
				$preferredCountriesData[] = [
					'country_applicant'  => $applicantId,
					'country_country'    => $countryId,
					'country_createdby'  => $_SESSION['admin']['user']['user_id'],
					'country_created'    => date( 'Y-m-d H:i:s', time() ),
				];
			}
		}

		if ( count( $preferredCountriesData ) > 0 ) {
			$this->db->flush_cache();
			$this->db->insert_batch( 'applicant_preferred_countries', $preferredCountriesData );
		}
		
		//Update passport
		$passportData = [
			'passport_number'         => $passport['number'],
			'passport_issue'          => date( 'Y-m-d', strtotime( $passport['issue'] ) ),
			'passport_issue_place'    => $passport['issue-place'],
			'passport_expiration'     => date( 'Y-m-d', strtotime( $passport['expiration'] ) ),
			'passport_updatedby'      => $_SESSION['admin']['user']['user_id'],
			'passport_updated'        => date( 'Y-m-d H:i:s', time() ),
		];
		
		$this->db->flush_cache();
		$passportUpdated =
			$this->db->where([
				'passport_applicant'  => $applicantId
			])
			->update( 'applicant_passport', $passportData);
		
		//Update educational background
		$educationData = [
			'education_mba'				=> $education['mba'],
			'education_mba_course'		=> $education['mba-course'],
			'education_mba_year'		=> $education['mba-year'],
			'education_college'			=> $education['college'],
			'education_college_skills'	=> $education['college-skills'],
			'education_college_year'	=> $education['college-year'],
			'education_others'			=> $education['others'],
			'education_others_year'		=> $education['others-year'],
			'education_highschool'		=> $education['highschool'],
			'education_highschool_year'	=> $education['highschool-year'],
			'education_updatedby'		=> $_SESSION['admin']['user']['user_id'],
			'education_updated'			=> date( 'Y-m-d H:i:s', time() ),
		];
		
		$this->db->flush_cache();
		$educationUpdated =
			$this->db->where([
				'education_applicant' => $applicantId,
			])
			->update( 'applicant_education', $educationData );
		
		//Remove unselected work experiences
		foreach ( $applicant['experiences'] as $experienceId => $experience ) {
			if ( ! isset( $oldWorkExperiences['company'][$experienceId] ) ) {
				$workExperiencesRemove[] = $experienceId;
				continue;
			}
            
            $workExperiencesUpdate = [
                'experience_applicant'  => $applicantId,
                'experience_company'    => $oldWorkExperiences['company'][$experienceId],
                'experience_position'   => $oldWorkExperiences['position'][$experienceId],
                'experience_salary'     => $oldWorkExperiences['salary'][$experienceId],
                'hospital_level'	    => $oldWorkExperiences['hospital_level'][$experienceId],
                'bed_capacity'	    	=> $oldWorkExperiences['bed_capacity'][$experienceId],
                'experience_country'    => $oldWorkExperiences['country'][$experienceId],
                'experience_from'       => $oldWorkExperiences['from'][$experienceId],
                'experience_to'         => $oldWorkExperiences['to'][$experienceId],
                'experience_years'      => $oldWorkExperiences['years'][$experienceId],
                'experience_createdby'  => $_SESSION['admin']['user']['user_id'],
                'experience_updatedby'  => $_SESSION['admin']['user']['user_id'],
                'experience_created'    => date( 'Y-m-d H:i:s', time() ),
                'experience_updated'    => date( 'Y-m-d H:i:s', time() ),
            ];
            
            $this->db->flush_cache();
            $this->db->where([
                    'experience_id' => $experienceId,
                ])->update( 'applicant_experiences', $workExperiencesUpdate );
		}
		
		if ( count( $workExperiencesRemove ) > 0) {
			$this->db->flush_cache();
			$this->db->where_in( 'experience_id', $workExperiencesRemove )
				->where([
					'experience_applicant' => $applicantId,
				])
				->delete( 'applicant_experiences' );
		}



		//Add new work experiences
		if ( isset( $newWorkExperiences['company'] ) ) {
			for ( $i = 0; $i < count( $newWorkExperiences['company'] ); $i++ ) {				
				if ( empty( $newWorkExperiences['company'][$i] ) || empty( $newWorkExperiences['position'][$i] ) ) {
					continue;
				}
				
				$workExperiencesData[] = [
					'experience_applicant'	=> $applicantId,
					'experience_company'	=> $newWorkExperiences['company'][$i],
					'experience_position'	=> $newWorkExperiences['position'][$i],
					'experience_salary'		=> $newWorkExperiences['salary'][$i],
					'experience_country'    => $newWorkExperiences['country'][$i],
					'experience_from'		=> $newWorkExperiences['from'][$i],
					'experience_to'			=> $newWorkExperiences['to'][$i],
					'experience_years'		=> $newWorkExperiences['years'][$i],
					'experience_createdby'	=> $_SESSION['admin']['user']['user_id'],
	                'experience_updatedby'	=> $_SESSION['admin']['user']['user_id'],
					'experience_created'	=> date( 'Y-m-d H:i:s', time() ),
	                'experience_updated'	=> date( 'Y-m-d H:i:s', time() ),
				];
			}
			
			if ( count ( $workExperiencesData ) > 0 ) {
				$experienceInserted = $this->db->insert_batch('applicant_experiences', $workExperiencesData);
			}
		}
		
		
		//Get the updated applicant record
		$applicant = $this->getApplicantById( $applicantId );


		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $applicantUpdated || ! $passportUpdated || ! $educationUpdated) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		return $applicant;
	} 
    
    public function updateApplicantCertificates( $applicantId )
    {
        $applicant = $this->getApplicantById( $applicantId );
		$post      = $_POST['applicant'];
		
		$certificate     =
		$certificateData = [];
		
		$certificate     = $post['certificate'];
		
		//Start Transaction
		$this->db->trans_begin();
        
        $certificateData = [
            'certificate_applicant'          => $applicantId,
            'certificate_tesda'              => isset( $certificate['tesda'] ),
            'certificate_info_sheet'         => isset( $certificate['info-sheet'] ),
            'certificate_authenticated'      => isset( $certificate['authenticated'] ),
           // 'red_ribbon_file_date'			 => date( 'Y-m-d H:i:s', strtotime( $certificate['red-filed-date'] ) ),
            //'red_ribbon_expired_date'		 => date( 'Y-m-d H:i:s', strtotime( $certificate['red-expired-date'] ) ),
            'certificate_authenticated_nbi'  => isset( $certificate['authenticated-nbi'] ),
            'nbi_expired_date'				 => date( 'Y-m-d H:i:s', strtotime( $certificate['nbi-expired-date'] ) ),
            'certificate_insurance'          => $certificate['insurance'],
            'insurance_no'			         => $certificate['insurance-no'],
            'certificate_medical_clinic'     => $certificate['medical-clinic'],
            'certificate_medical_exam_date'  => date( 'Y-m-d H:i:s', strtotime( $certificate['medical-exam-date'] ) ),
            'certificate_medical_result'     => $certificate['medical-result'],
            'certificate_medical_remarks'    => $certificate['medical-remarks'],
            'certificate_medical_expiration' => date( 'Y-m-d H:i:s', strtotime( $certificate['medical-expiration'] ) ),
            'certificate_pdos'               => isset( $certificate['pdos'] ),
            'certificate_pt_result'          => $certificate['pt-result'],
            'certificate_pt_result_date'     => date( 'Y-m-d H:i:s', strtotime( $certificate['pt-result-date'] ) ),
            'certificate_philhealth'         => isset( $certificate['philhealth'] ),
            'certificate_m1b'                => isset( $certificate['m1b'] ),
			'certificate_tor'                => $certificate['tor'] ,
			'certificate_prc_cert'           => $certificate['prc_cert'] ,
			'certificate_prc_id'             => $certificate['prc_id'] ,
			'certificate_prc_rating'         => $certificate['prc_rating'],
			'certificate_coe'                => $certificate['coe'] ,
			'certificate_bc'                 => $certificate['bc'] ,
			'certificate_mc'                 => $certificate['mc'] ,
			'applicant_certificate_no_marriage' => date( 'Y-m-d H:i:s', strtotime( $certificate['no_marriage'] ) ),
			'certificate_saudi_id'           => $certificate['saudi_ids'] ,
			'certificate_prc_take'           => $certificate['prc_take'],	
			'certificate_ksa'         	 	 => $certificate['ksa'] ,
			'certificate_haad'          	 => $certificate['haad'] ,
			'certificate_qatar'          	 => $certificate['qatar'] ,
			'certificate_nclex'          	 => $certificate['nclex'] ,
			'certificate_ielts'          	 => $certificate['ielts'] ,
			'certificate_cgfns'          	 => $certificate['cgfns'] ,
			'certificate_cgfns_id'          	 => $certificate['cgfns_id'] ,
			'certificate_cgfns_exam'          	 => $certificate['cgfns_exam'] ,
			'certificate_dha'          	 => $certificate['dha'] ,
			'certificate_mmr'          	 => $certificate['mmr'] ,
			'certificate_tesda_date'     => $certificate['tesda_date'] ,
			'certificate_pdos_date'      => $certificate['pdos_date'] ,
			'certificate_owwa'      => $certificate['owwa'] ,
			//'certificate_owwa_to'      => $certificate['owwato'] ,
			'certificate_owwa_from'      => $certificate['owwafrom'] ,
            'certificate_updatedby'          => $_SESSION['admin']['user']['user_id'],
            'certificate_updated'            => date( 'Y-m-d H:i:s', time() ),        
        ];
        $this->db->flush_cache();
        $certificateUpdated =
            $this->db->where([
                'certificate_applicant' => $applicantId,
            ])->update( 'applicant_certificate', $certificateData );
        		
		//Get the updated applicant record
		$applicant = $this->getApplicantById( $applicantId );

		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $certificateUpdated) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		return $applicant;
    }
    
    public function updateApplicantRequirements( $applicantId )
	{
		$applicant = $this->getApplicantById( $applicantId );
		$post      = $_POST['applicant'];
	
		$requirement      =
		$requirementsData = [];
		
		$requirement      = $post['requirement'];
		
		//Start Transaction
		$this->db->trans_begin();

        $requirementsData = [
            'requirement_applicant'           => $applicantId,
            'requirement_trade_test'          => isset( $requirement['trade-test'] ),
            'requirement_picture_status'      => $requirement['picture-status'],
            //'requirement_coe'                 => isset( $requirement['coe'] ),
            'requirement_school_records'      => $requirement['school-records'],
            'requirement_visa'                => isset( $requirement['visa'] ),            
            'requirement_visa_date'           => date( 'Y-m-d', strtotime( $requirement['visa-date'] ) ),
			'requirement_visa_stamp'           => date( 'Y-m-d', strtotime( $requirement['visa-stamp'] ) ),
            'requirement_visa_release_date'   => date( 'Y-m-d', strtotime( $requirement['visa-release-date'] ) ),
            'requirement_visa_expiration'     => date( 'Y-m-d', strtotime( $requirement['visa-expiration'] ) ),
            'requirement_oec_number'          => $requirement['oec-number'],
            'requirement_oec_submission_date' => date( 'Y-m-d', strtotime( $requirement['oec-submission-date'] ) ),
            'requirement_oec_release_date'    => date( 'Y-m-d', strtotime( $requirement['oec-release-date'] ) ),
            //'requirement_owwa_certificate'    => $requirement['owwa-certificate'],
            //'requirement_owwa_schedule'       => date( 'Y-m-d', strtotime( $requirement['owwa-schedule'] ) ),
            'requirement_contract'            => date( 'Y-m-d', strtotime( $requirement['contract'] ) ),
            //'requirement_mofa'                => $requirement['mofa'],
            'requirement_job_offer'           => $requirement['job-offer'],
			'requirement_job_received'           => date( 'Y-m-d', strtotime( $requirement['jo-received'] ) ),
			'requirement_job_accepted'           => date( 'Y-m-d', strtotime( $requirement['jo-accept'] ) ),
            'offer_letter'			          => $requirement['offer-letter'],
            'requirement_ticket'              => $requirement['ticket'],
            'ticket_no'			              => $requirement['ticket-no'],
            'flight_date'			          => date( 'Y-m-d', strtotime( $requirement['flight-date'] ) ),
            'ticket_remarks'			      => $requirement['flight-remarks'],
            'requirement_offer_salary'        => $requirement['offer-salary'],
            'requirement_remarks'             => $requirement['remarks'],
			'requirement_visa_no'             => $requirement['visa-no'],
			'requirement_visa_category'        => $requirement['visa-category'],
            'requirement_updatedby'           => $_SESSION['admin']['user']['user_id'],
            'requirement_updated'             => date( 'Y-m-d H:i:s', time() ),
        ];

        $this->db->flush_cache();
        $requirementsUpdated =
            $this->db->where([
                'requirement_applicant' => $applicantId,
            ])->update( 'applicant_requirement', $requirementsData );
        
        //Update applicant job offer
        $this->db->flush_cache();
        $this->db->where([
                'applicant_id' => $applicantId,
            ])->update( 'applicant', [ 'applicant_job' => $requirement['job-offer'] ] );
		
		//createBilling
		$billFine = true;
		// if ( ! $applicant['requirement_job_offer'] && $requirement['job-offer'] > 0 ) {
		// 	$this->load->model( 'm_billing' );
		// 	$billFine = ( new m_billing )->createBilling( $applicantId );
		// }

		if ( $requirement['job-offer'] > 0 ) {
			
			$this->load->model( 'm_billing' );
			
			if (  ! ( new m_billing )->hasBilling( $applicantId ) ) {
				$billFine = ( new m_billing )->createBilling( $applicantId );
			}
		}		

		//Get the updated applicant record
		$applicant = $this->getApplicantById( $applicantId );

		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $requirementsUpdated || ! $billFine ) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	

		return $applicant;
	}
    
    public function uploadApplicantFile( $applicantId, $file )
    {
        $post = $_POST['applicant']['file'];
        
        ini_set('memory_limit', '100M');
        ini_set('post_max_size', '100M');
        ini_set('upload_max_filesize', '100M');

        switch ( $file['error'] ) {
            case UPLOAD_ERR_OK:

                break;

            case UPLOAD_ERR_NO_FILE:  

                Message::addWarning('No file sent');
                return false;   

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:

                Message::addWarning('Exceeded filesize limit');
                return false;  

            default:

                Message::addWarning('Unknown errors occur.');
                return false;
        }
        
        $fileName = time().'-'.$file['name'];
        
        $uploadDir     = __DIR__.'/../../files/applicant/uploaded/';
        $applicantDir  = $uploadDir . str_pad( $applicantId, 7, '0', STR_PAD_LEFT ) . '/';
        $applicantPath = 'files/applicant/uploaded/'.str_pad( $applicantId, 7, '0', STR_PAD_LEFT ) . '/';
        
        if ( ! is_dir( $applicantDir ) ) {
            mkdir( $applicantDir, 0777, true );
        }
        
        //Make directory rewritable
        chmod( $applicantDir , 0777);
        
        $uploaded = move_uploaded_file( $file['tmp_name'], $applicantDir . $fileName );
        
        if ( ! $uploaded ) {
            Message::addWarning('File cannot be upload. Please check the file.');
            return false;
        }
        
        $fileData = [
            'file_applicant' => $applicantId,
            'file_name'      => $post['name'],
            'file_type'      => $post['type'],
            'file_size'      => $file['size'],
            'file_mime'      => $file['type'],
            'file_path'      => $applicantPath . $fileName,
            'file_status'    => 1,
            'file_createdby' => $_SESSION['admin']['user']['user_id'],
            'file_created'   => date( 'Y-m-d H:i:s', time() ),
        ];
        
        $this->db->flush_cache();
        $this->db->insert( 'applicant_files', $fileData );
        $fileId = $this->db->insert_id();

        $file = $this->getApplicantFileById( $fileId );

        return $file;
    }
    
    public function updateApplicantStatus( $applicantId )
    {
        $applicant = $this->getApplicantById( $applicantId );
		$post      = $_POST['applicant'];
		
		$log       =
		$logData   = [];
		
		$log      = $post['status'];
        
        $logInserted = $this->addLog( $log['remarks'], $applicantId, $log['employer'], $log['status'], date( 'Y-m-d', strtotime( $log['date'] ) ) );

        //Update applicant status
        $this->db->flush_cache();
        $this->db->where([
                'applicant_id' => $applicantId,
            ])
            ->update( 'applicant', [
                'applicant_employer' => $log['employer'],
                'applicant_status'   => $log['status'],
                'sub_status'	     => $log['substatus'],
                'applicantNumber'    => $log['applicant-no'],
            ]);
        
        switch ( $log['status'] ) {

        	case $this->status['Reserved']:

            	//Start Transaction
				$this->db->trans_begin();

                //Delete previous selected record
		        $this->db->flush_cache();
				$this->db->where([
						'reservation_applicant' => $applicantId,
					])->delete( 'employer_reservation' );

            	$selectionData = [
		            'reservation_employer'   => $log['employer'],
		            'reservation_applicant'  => $applicantId,
		            'reservation_expiration' => date( 'Y-m-d', strtotime( ' +'.self::RESERVED_DAYS_EXPIRATION.' days' ) ),
		            'reservation_status'     => 1,
		            'reservation_remarks'    => '',
		            'reservation_date'       => fdate( 'Y-m-d', $log['date'] ),
		            'reservation_createdby'  => $_SESSION['admin']['user']['user_id'],
		            'reservation_updatedby'  => $_SESSION['admin']['user']['user_id'],
		            'reservation_updated'    => date( 'Y-m-d H:i:s', time() ),
		            'reservation_created'    => date( 'Y-m-d H:i:s', time() ),
		        ];

		        $this->db->flush_cache();
		        $selectionInserted = $this->db->insert( 'employer_reservation', $selectionData );
		        
		        //Commit transaction
				$this->db->trans_commit();

        		break;

            case $this->status['Selected']:
                
                //Start Transaction
				$this->db->trans_begin();

                //Delete previous selected record
		        $this->db->flush_cache();
		        $selected = $this->db->get_where('employer_selected', [
		        		'selected_applicant' => $applicantId,
		        	])->row_array();

		        if ( empty( $selected ) ) {

		        	$selectedData = [
			            'selected_employer'   => $log['employer'],
			            'selected_applicant'  => $applicantId,
			            'selected_date'       => fdate( 'Y-m-d', $log['date'] ),
			            'selected_remarks'    => $log['remarks'],
			            'selected_createdby'  => $_SESSION['admin']['user']['user_id'] ,
			            'selected_updatedby'  => $_SESSION['admin']['user']['user_id'] ,
			            'selected_updated'    => date( 'Y-m-d H:i:s', time() ),
			            'selected_created'    => date( 'Y-m-d H:i:s', time() ),
			        ];

			        $this->db->flush_cache();
			        $this->db->insert( 'employer_selected', $selectedData );

		        } else {

		        	$selectedData = [
			            'selected_employer'   => $log['employer'],
			            'selected_date'       => fdate( 'Y-m-d', $log['date'] ),
			            'selected_remarks'    => $log['remarks'],
			            'selected_updatedby'  => $_SESSION['admin']['user']['user_id'] ,
			            'selected_updated'    => date( 'Y-m-d H:i:s', time() ),
			        ];

			        $this->db->flush_cache();
			        $this->db->where([
			        		'selected_id' => $selected['selected_id'],
			        	])
			        	->update( 'employer_selected', $selectedData );

		        }
 
		        //Rollback if transaction fails
				if ( ! $this->db->trans_status() ) {
					$this->db->trans_rollback();
					return false;
				}
				
				//Commit transaction
				$this->db->trans_commit();

        		break;
            
            case $this->status['Deployed']:

            	$this->db->flush_cache();

            	//Add to deployed list
            	$deployedData = [
					'deployed_applicant'  => $applicantId,
					'deployed_employer'   => $applicant['applicant_employer'],
					'deployed_job'        => $applicant['applicant_job'],
					'deployed_country'    => $applicant['applicant_preferred_country'],
					'deployed_position'   => $applicant['job_position'],
					'deployed_salary'     => (float) $applicant['applicant_expected_salary'],
					'deployed_remarks'    => 'Applicant has been added to deployed list.',
					'deployed_date'       => fdate( 'Y-m-d', $log['date'] ),
					'deployed_createdby'  => $_SESSION['admin']['user']['user_id'],
					'deployed_updatedby'  => $_SESSION['admin']['user']['user_id'],
					'deployed_created'    => date( 'Y-m-d H:i:s', time() ),
					'deployed_updated'    => date( 'Y-m-d H:i:s', time() ),
            	];

            	$this->db->flush_cache();
            	$this->db->insert( 'deployed', $deployedData);

            	$this->db->flush_cache();
            	$this->db->set('`job_occupied`', '`job_occupied` + 1', FALSE)
            		->where([
            			'job_id' => $applicant['applicant_job'],
            		])->update( 'job' );
				
				//create Commission for recruitment agent
				$this->load->model( 'm_commission_recruitment_agent' );
				( new m_commission_recruitment_agent )->createCommission( $applicantId );
				
				if ( $applicant['applicant_employer'] ) {
					$this->load->model( 'm_commission_marketing_agency' );
					( new m_commission_marketing_agency )->createCommission( $applicant['applicant_employer'], $applicantId );
					
					$this->load->model( 'm_commission_marketing_agent' );
					( new m_commission_marketing_agent )->createCommission( $applicant['applicant_employer'], $applicantId );
				}

				break;
        }
        
        $applicant = [];

        if ( $logInserted ) {
            $applicant = $this->getApplicantById( $applicantId );
        }
        
		return $applicant;
    }
    
    public function reserveApplicant( $applicantId, $employerId )
    {
        $selectionData = $applicantData = [];
        
        $selectionData = [
            'reservation_employer'   => $employerId,
            'reservation_applicant'  => $applicantId,
            'reservation_expiration' => date( 'Y-m-d', strtotime( ' +'.self::RESERVED_DAYS_EXPIRATION.' days' ) ),
            'reservation_status'     => 1,
            'reservation_remarks'    => '',
            'reservation_date'       => date( 'Y-m-d', time() ),
            'reservation_createdby'  => $_SESSION['employer']['user']['user_id'],
            'reservation_updatedby'  => $_SESSION['employer']['user']['user_id'],
            'reservation_updated'    => date( 'Y-m-d H:i:s', time() ),
            'reservation_created'    => date( 'Y-m-d H:i:s', time() ),
        ];

        $this->db->flush_cache();
        $selectionInserted = $this->db->insert( 'employer_reservation', $selectionData );

        //$logInserted = $this->addLog( $log['remarks'], $applicantId, $log['employer'], $log['status'], date( 'Y-m-d', strtotime( $log['date'] ) ) );
        
        $applicantData = [
            'applicant_status'   => $this->status['Reserved'],
            'applicant_employer' => $employerId,
            'applicant_job'      => 0,
        ];
        
        $this->db->flush_cache();
        $applicantUpdated =
            $this->db->where([
                'applicant_id' => $applicantId,
            ])
            ->update( 'applicant', $applicantData);
        
        if ( $selectionInserted && $applicantUpdated ) {
            $this->addLog('Applicant was reserved', $applicantId, $employerId, $this->status['Reserved']);
            return true;
        }
        
        return false;
    }

    public function delete_multipleLineup( $applicantId){
    	 $this->db->delete('multiple_lineups', array('applicant_id' => $applicantId));  
    }
    
    public function extendReserveApplicant( $reservationId, $daysToExtend = self::RESERVED_DAYS_EXPIRATION, $remarks = '' )
    {
        $reservationData = [
            'reservation_expiration' => date( 'Y-m-d', strtotime( ' +'.$daysToExtend.' days' ) ),
            'reservation_remarks'    => $remarks,
            'reservation_updatedby'  => $_SESSION['admin']['user']['user_id'],
            'reservation_updated'    => date( 'Y-m-d', time() ),
        ];
        
        $this->db->flush_cache();
        $reserveUpdated = 
            $this->db->where([
                'reservation_id' => $reservationId,
            ])->update( 'employer_reservation', $reservationData );
        
        return $reserveUpdated;
    }
    
    public function unReserveApplicant( $applicantId, $employerId )
    {
		//Delete reservation entry
        $this->db->flush_cache();
        $selectionDeleted = 
		$this->db->where([
				'reservation_employer'  => $employerId,
				'reservation_applicant' => $applicantId,
			])->delete( 'employer_reservation' );
       
	    //Revert applicant status 
        $applicantData = [
            'applicant_status'   => $this->status['Available'],
			'applicant_employer' => 0,
			'applicant_job'       => 0,
        ];
        
        $this->db->flush_cache();
        $applicantUpdated = 
		$this->db->where([
				'applicant_id' => $applicantId,
			])->update( 'applicant', $applicantData );

		$this->addLog('Unreserved the applicant.', $applicantId, $employerId, $this->status['Available'], date( 'Y-m-d', time() ) );
        
        return $selectionDeleted && $applicantUpdated;
    }

    public function selectApplicant( $applicantId, $employerId, $remarks = '' )
    {
        $selectedData = $applicantData = [];

        $this->db->flush_cache();
        $selected = 
		$this->db->get_where( 'employer_selected', [ 
			'selected_applicant' => $applicantId,
			'selected_employer'  => $employerId,
		]);

        if ( empty( $selected ) ) { 
        	return true;
        }
        
        $selectedData = [
            'selected_employer'   => $employerId,
            'selected_applicant'  => $applicantId,
            'selected_remarks'    => $remarks,
            'selected_createdby'  => isset( $_SESSION['employer']['user'] ) 
            						? $_SESSION['employer']['user']['user_id'] 
            						: $_SESSION['admin']['user']['user_id'],
            'selected_updatedby'  => isset( $_SESSION['employer']['user'] ) 
            						? $_SESSION['employer']['user']['user_id'] 
            						: $_SESSION['admin']['user']['user_id'],
            'selected_updated'    => date( 'Y-m-d H:i:s', time() ),
            'selected_created'    => date( 'Y-m-d H:i:s', time() ),
        ];

        $this->db->flush_cache();
        $selectedInserted = $this->db->insert( 'employer_selected', $selectedData );
        
        $applicantData = [
            'applicant_status' => $this->status['Selected'],
        ];
        
        $this->db->flush_cache();
        $applicantUpdated =
            $this->db->where([
                'applicant_id' => $applicantId,
            ])
            ->update( 'applicant', $applicantData);
        
        return $selectedInserted && $applicantUpdated;
    }

    public function unSelectApplicant( $applicantId, $employerId )
    {
		//Remove selection entry
        $this->db->flush_cache();
        $selectionDeleted = 
		$this->db->where([
				'selected_employer'  => $employerId,
				'selected_applicant' => $applicantId,
			])->delete( 'employer_selected' );
        
        $applicantData = [
            'applicant_status' => $this->status['Reserved'],
			'applicant_job'    => 0,
        ];
		
		$this->db->flush_cache();
		$applicantUpdated =
		$this->db->where([
				'applicant_id' => $applicantId,
			])->update( 'applicant', $applicantData );
        
        return $applicantUpdated;
    }
    
    public function isReserved( $applicantId, $employerId )
    {
        $this->db->flush_cache();
        $this->db->select()
            ->from( 'applicant a' )
            ->join( 'employer_reservation er', 'er.reservation_applicant = a.applicant_id' )
            ->where([
                'er.reservation_employer'  => $employerId,
                'er.reservation_applicant' => $applicantId,
                'er.reservation_status'    => 1,
                'a.applicant_status'       => $this->status['Reserved'],
            ]);

        return $this->db->count_all_results() > 0;
    }

    public function archiveApplicant( $applicantId )
    {
    	$strQuery = "INSERT INTO `archive_applicant` SELECT *,? FROM `applicant` WHERE `applicant_id` = ?";
    	/*
    	$this->db->flush_cache();
    	$archived = 
    	$this->db->query( $strQuery, [
    		$_SESSION['admin']['user']['user_id'],
    		$applicantId,
    	]);
*/
    	$this->db->flush_cache();
    	$deleted = $this->db->where([
    			'applicant_id' => $applicantId,
    		])->delete( 'applicant');

    	return /*$archived &&*/ $deleted;
    }
    
    public function archiveApplicantFile( $applicantId, $fileId )
    {
        $file = $this->getApplicantFileById( $fileId );
        
        if ( empty( $file ) ) {
            Message::addInfo( 'File is not longer available.' );
            return false;
        }
        
        $filePath         = $file['file_path'];
        $fileName         = pathinfo( $file['file_path'], PATHINFO_BASENAME );
        $applicantFolder  = end( explode( '/', dirname( $file['file_path'] ) ) );
        $applicantDir     = __DIR__.'/../../files/archive/applicant/uploaded/'.$applicantFolder.'/';
        
        //Make rewritable directory
        if ( ! is_dir( $applicantDir ) ) {
            mkdir( $applicantDir, 0777, true );
        }
        
        //Make directory rewritable
        chmod( $applicantDir , 0777);
        
        $archived = rename( $file['file_path'], $applicantDir . $fileName );
        
        if ( ! $archived ) {
            Message::addWarning('File is unaccessible. Please contact your administrator.');
            return false;
        }
        
        $this->db->flush_cache();
        $fileUpdated = 
            $this->db->where([
                'file_id' => $fileId,
            ])->update( 'applicant_files', [ 'file_status' => 0 ] );
        
        return $fileUpdated;
    }

    public function moveToAvailable( $applicantIds, $options = [] )
    {
    	//Delete from reservation
    	$this->db->flush_cache();
    	$this->db->where_in( 'reservation_applicant', $applicantIds )
    		->delete( 'employer_reservation' );

    	//Delete from selected
    	$this->db->flush_cache();
    	$this->db->where_in( 'selected_applicant', $applicantIds )
    		->delete( 'employer_selected' );

    	foreach ( $applicantIds as $applicantId ) {
    		$this->clearApplicantBilling( $applicantId );
    	}
 
    	$this->db->flush_cache();
    	$this->db->where_in( 'applicant_id', $applicantIds );

    	$this->setDBQueryOptions( $options );

    	$this->db->update( 'applicant', [
    			'applicant_status'   => $this->status['Available'],
                'applicant_employer' => 0,
                'applicant_job'      => 0,
    		]);

    	$affectedApplicants = $this->db->affected_rows(); 

    	foreach ( $applicantIds as $applicantId ) {
    		$this->addLog('Applicant has been moved back to list of available.', $applicantId, 0, 1, date( 'Y-m-d', time() ) );
    	}

    	return $affectedApplicants;
    }

    public function clearApplicantBilling( $applicantId )
    {
    	//Get bill
    	$this->db->from( 'bill' )
    		->where([
    			'bill_applicant' => $applicantId,
    		]);

    	$bill = $this->db->get()->row_array();

    	if ( empty( $bill ) ) {
    		return false;
    	}

    	//Get all ORs
    	$ors = [];


    	$this->db->from( 'or' )
    		->where([
    			'or_applicant' => $applicantId,
    		]);

    	$orRows = $this->db->get()->result_array();
    	$orRows = $this->indexArray( $orRows, 'or_number' );
    	$ors    = array_merge( $ors, array_keys( $orRows ) );


    	$this->db->from( 'bill_payment_applicant' )
    		->where([
    			'payment_bill'      => $bill['bill_id'],
    			'payment_applicant' => $applicantId,
    		]);

    	$orRows = $this->db->get()->result_array();
    	$orRows = $this->indexArray( $orRows, 'payment_or' );
    	$ors    = array_merge( $ors, array_keys( $orRows ) );

    	$this->db->from( 'paidall_employer_applicants' )
    		->where([
    			'paidall_bill'      => $bill['bill_id'],
    			'paidall_applicant' => $applicantId,
    		]);

    	$orRows = $this->db->get()->result_array();
    	$orRows = $this->indexArray( $orRows, 'paidall_or' );
    	$ors    = array_merge( $ors, array_keys( $orRows ) );


    	$this->db->from( 'payment_employer_detail' )
    		->where([
    			'detail_bill'      => $bill['bill_id'],
    			'detail_applicant' => $applicantId,
    		]);

    	$orRows = $this->db->get()->result_array();
    	$orRows = $this->indexArray( $orRows, 'detail_or' );
    	$ors    = array_merge( $ors, array_keys( $orRows ) );


    	$this->db->from( 'payment_worker_detail' )
    		->where([
    			'detail_bill'      => $bill['bill_id'],
    			'detail_applicant' => $applicantId,
    		]);

    	$orRows = $this->db->get()->result_array();
    	$orRows = $this->indexArray( $orRows, 'detail_or' );
    	$ors    = array_merge( $ors, array_keys( $orRows ) );


    	$orsFiltered = [];
    	foreach ($ors as $or) {
    		if ( ! in_array( $or , $orsFiltered) ) {
    			$orsFiltered[] = $or;	
    		}    		
    	}

    	//Delete transaction history
    	$this->db->flush_cache();
    	$this->db->where([
    			'bill_id' => $bill['bill_id'],
    		])
    		->delete( 'bill' );

    	$this->db->flush_cache();
    	$this->db->where([
    			'detail_bill' => $bill['bill_id'],
    		])
    		->delete( 'bill_detail' );

    	$this->db->flush_cache();
    	$this->db->where([
    			'payment_bill' => $bill['bill_id'],
    		])
    		->delete( 'bill_payment_applicant' );


    	$this->db->flush_cache();
    	$this->db->where([
    			'detail_bill' => $bill['bill_id'],
    		])
    		->delete( 'payment_worker_detail' );

    	$this->db->flush_cache();
    	$this->db->where([
    			'detail_bill' => $bill['bill_id'],
    		])
    		->delete( 'payment_employer_detail' );


    	$this->db->flush_cache();
    	$this->db->where_in( 'payment_or', $orsFiltered )
    		->delete( 'bill_payment_employer' );

    	$this->db->flush_cache();
    	$this->db->where_in( 'paidall_or', $orsFiltered )
    		->delete( 'paidall_employer_applicants' );

    	$this->db->flush_cache();
    	$this->db->where_in( 'or_number', $orsFiltered )
    		->delete( 'or' );

    	return true;
    }

	/* Protected Methods
	-------------------------------*/
	protected function uploadPhoto( $applicantId, $file )
	{
		$dirDestination = DIR_UPLOADS.'applicant'.DIRECTORY_SEPARATOR;

		$fileName = time().'-'.$applicantId.'.'.pathinfo( $file['name'], PATHINFO_EXTENSION );

		if ( ! is_writable( $dirDestination ) ) {
			chmod( $dirDestination, 0777);	
		}	

		$uploaded = move_uploaded_file( $file['tmp_name'], $dirDestination.$fileName );
		
		if ( $uploaded ) {
			return $fileName;
		}

		return false;
	}

	protected function endProcess()
	{
		if (isset($_SESSION['post']['admin']['applicants/add'])) {
			unset($_SESSION['post']['admin']['applicants/add']);
		}
		
		if (isset($_SESSION['post']['admin']['applicants/review'])) {
			unset($_SESSION['post']['admin']['applicants/review']);
		}

		if (isset($_SESSION['post']['public']['applicants/registration'])) {
			unset($_SESSION['post']['public']['applicants/registration']);
		}
		
		return $this;		
	}
	
	/* Private Methods
	-------------------------------*/
    private function rawApplicantPassport( array $elements = [] )
    {
        $passportData = [
            'passport_applicant'   => null,
            'passport_number'      => null,
            'passport_issue'       => null,
            'passport_issue_place' => null,
            'passport_expiration'  => null,
            'passport_createdby'   => isset( $_SESSION['admin']['user']['user_id'] )
									  ? $_SESSION['admin']['user']['user_id']
									  : 0,
            'passport_updatedby'   => isset( $_SESSION['admin']['user']['user_id'] )
									  ? $_SESSION['admin']['user']['user_id']
									  : 0,
            'passport_created'     => date( 'Y-m-d H:i:s', time() ),
            'passport_updated'     => date( 'Y-m-d H:i:s', time() ),
        ];
        
        $passportData = array_merge( $passportData, $elements );
        
        return $passportData;
    }
    
    private function rawApplicantVisa( array $elements = [] )
    {
        $visaData = [
            'visa_applicant'   => null,
            'visa_date'        => null,
            'visa_release'     => null,
            'visa_expiration'  => null,
            'visa_status'      => null,
            'visa_createdby'   => isset( $_SESSION['admin']['user']['user_id'] )
								  ? $_SESSION['admin']['user']['user_id']
								  : 0,
            'visa_updatedby'   => isset( $_SESSION['admin']['user']['user_id'] )
								  ? $_SESSION['admin']['user']['user_id']
								  : 0,
            'visa_created'     => date( 'Y-m-d H:i:s', time() ),
            'visa_updated'     => date( 'Y-m-d H:i:s', time() ),
		];
        
        $visaData = array_merge( $visaData, $elements );
        
        return $visaData;
    }
    
    private function rawApplicantCertificate( array $elements = [] )
    {
        $certificateData = [
            'certificate_applicant'           => null,
            'certificate_tesda'               => null,
            'certificate_info_sheet'          => null,
            'certificate_authenticated'       => null,
            'certificate_authenticated_nbi'   => null,
            'certificate_insurance'           => null,
            'certificate_medical_clinic'      => null,
            'certificate_medical_exam_date'   => null,
            'certificate_medical_result'      => null,
            'certificate_medical_remarks'     => null,
            'certificate_medical_expiration'  => null,
            'certificate_pdos'                => null,
            'certificate_pt_result'           => null,
            'certificate_pt_result_date'      => null,
            'certificate_philhealth'          => null,
            'certificate_m1b'                 => null,
            'certificate_createdby'           => isset( $_SESSION['admin']['user']['user_id'] )
											     ? $_SESSION['admin']['user']['user_id']
											     : 0,
            'certificate_updatedby'           => isset( $_SESSION['admin']['user']['user_id'] )
												 ? $_SESSION['admin']['user']['user_id']
												 : 0,
            'certificate_created'             => date( 'Y-m-d H:i:s', time() ),
            'certificate_updated'             => date( 'Y-m-d H:i:s', time() ),            
		];
        
        $certificateData = array_merge( $certificateData, $elements );
        
        return $certificateData;
    }
    
    private function rawApplicantRequirements( array $elements = [] )
    {
        $requirementsData = [
            'requirement_applicant'           => null,
            'requirement_visa'                => null,
            'requirement_visa_date'           => null,
            'requirement_visa_release_date'   => null,
            'requirement_visa_expiration'     => null,
            'requirement_oec_number'          => null,
            'requirement_oec_submission_date' => null,
            'requirement_oec_release_date'    => null,
            'requirement_owwa_certificate'    => null,
            'requirement_owwa_schedule'       => null,
            'requirement_contract'            => null,
            'requirement_mofa'                => null,
            'requirement_job_offer'           => null,
            'requirement_remarks'             => null,
            'requirement_createdby'           => isset( $_SESSION['admin']['user']['user_id'] )
												 ? $_SESSION['admin']['user']['user_id']
												 : 0,
            'requirement_updatedby'           => isset( $_SESSION['admin']['user']['user_id'] )
												 ? $_SESSION['admin']['user']['user_id']
												 : 0,
            'requirement_created'             => date( 'Y-m-d H:i:s', time() ),
            'requirement_updated'             => date( 'Y-m-d H:i:s', time() ),            
		];
        
        $requirementsData = array_merge( $requirementsData, $elements );
        
        return $requirementsData;
    }

    public function cyd_get_applicant_requirement(){
    	$return = array();
		//requirement_oec_number search
		$this->db->flush_cache();
		$query = $this->db->get('applicant_requirement');
		$results = $query->result();

		foreach($results as $per_result) {
			$return[$per_result->requirement_applicant] = $per_result;
		}
		return $return;
    }

    public function cyd_get_all_sub_position(){
    	$array_return = array();
    	$position_array_return = array();

    	$this->db->flush_cache();
		$position_query = $this->db->get('position');
		$position_results = $position_query->result();
		foreach ($position_results as $position_value) {
			$position_array_return[$position_value->position_id] = $position_value->position_name;
		}

    	$this->db->flush_cache();
		$query = $this->db->get('applicant_preferred_positions');
		$results = $query->result();

		foreach ($results as $value) {
			if(!isset($array_return[$value->position_applicant]))
				$array_return[$value->position_applicant] = ' ';

			if(isset($position_array_return[$value->position_position])){
				if(strlen($array_return[$value->position_applicant]) < 4)
					$array_return[$value->position_applicant] .= $position_array_return[$value->position_position];
				else
					$array_return[$value->position_applicant] .= ', '.$position_array_return[$value->position_position];
			}
			
		}
		return $array_return;
    }

   //add all application table to an array
    public function cyd_get_applicants_raw(){
    	$applicant_array_return = array();
    	$applicant_query = $this->db->get('applicant');
		$applicant_results = $applicant_query->result();
		foreach ($applicant_results as $applicant_value) {
			$applicant_array_return[$applicant_value->applicant_id] = $applicant_value;
		}
		return $applicant_array_return;
    }

    public function cyd_get_applicant_certificate_raw(){
    	$applicant_array_return = array();
    	$applicant_query = $this->db->get('applicant_certificate');
		$applicant_results = $applicant_query->result();
		foreach ($applicant_results as $applicant_value) {
			$applicant_array_return[$applicant_value->certificate_applicant] = $applicant_value;
		}
		return $applicant_array_return;
    }
    
    public function cyd_applicant_requirement_raw(){
    	$applicant_array_return = array();
    	$applicant_query = $this->db->get('applicant_requirement');
		$applicant_results = $applicant_query->result();
		foreach ($applicant_results as $applicant_value) {
			$applicant_array_return[$applicant_value->requirement_applicant] = $applicant_value;
		}
		return $applicant_array_return;
    }
}
