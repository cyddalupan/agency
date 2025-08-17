<?php //-->
/*
 * This file is part a custom application package.
 * (c) 2014 Clemente Quiñones Jr. <clemquinones@gmail.com>
 */

/**
 * Core Knowledge of all pages
 *
 * @author     Clemente Quiñones Jr. <clemquinones@gmail.com>
 * @version    1.0.0
 */
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class m_employer extends MY_Model {
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
	public function find( $employerId )
	{
		$this->db->flush_cache();
		$this->db->from( 'employer' )
			->where([
				'employer_id' => $employerId,
			]);
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$employer = $this->db->get()->row_array();

		return $employer;
	}

	public function all( $options = [], $limit = 0, $offset = 0 )
	{
		$this->db->flush_cache();
		$this->db->from( 'employer' );

		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset );
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$employers = $this->db->get()->result_array();

		return $this->indexArray( $employers, 'employer_id' );
	}

	public function delete( $employerId )
	{
		$employer = $this->find( $employerId );

		$this->db->flush_cache();
		$this->db->where([
				'employer_id' => $employerId,
			])->delete( 'employer' );

		//Delete user
		$this->db->flush_cache();
        $this->db->where([
                'user_id' => $employer['employer_user'],
            ])->delete( 'user' );        

		//Delete job offers and job fees
		$this->db->flush_cache();
        $this->db->where([
                'job_employer' => $employerId,
            ])->delete( 'job' );
        
        $this->db->flush_cache();
        $this->db->where([
                'fee_employer' => $employerId,
            ])->delete( 'job_fee' );

       	return $employer;
	}

	public function getEmployerById( $employerId, $withJobOffers = true )
	{	
		//Get Applicant Info
		$this->db->flush_cache();
		$this->db->from('employer')
            ->join( 'country', 'country_id = employer_country' )
			->join( 'user', 'user_id = employer_user' )
            ->join( 'marketing_agency', 'agency_id = employer_source_agency', 'left' )
            ->join( 'marketing_agent', 'agent_id = employer_source_agent', 'left' )
			->where([
				'employer_id'	=> $employerId,
			]);
			
		$employer = $this->db->get()->row_array();

		if ( $withJobOffers == true ) {

			$this->db->flush_cache();        
			$this->db->from('job')
	            ->join( 'position', 'position_id = job_position', 'inner' )
	            ->where([
	            	'job_employer' => $employerId,
	            ]);
				
			$jobs = $this->db->get()->result_array();
	        $employer['job-offers'] = $this->indexArray( $jobs, 'job_id' );
    	}	

		return $employer;
	}
	
	public function getEmployers( $options = [], $limit = 0, $offset = 0, $sort = ['employer_name', 'ASC'] )
	{
		//Get applicant status
		$this->load->model( 'm_applicant' );
		$status = $this->m_applicant->status;
		
		//Get Applicant Info
		$this->db->flush_cache();
		$this->db->select( '*, 
				(SELECT COUNT(*) FROM `applicant` 
					WHERE `applicant_employer` = `employer_id` 
					AND `applicant_status` != '.$status['Reserved'].') as `workers`')
			->from('employer')
            ->join( 'country', 'country_id = employer_country' )
			->join( 'user', 'user_id = employer_user' )
            ->join( 'marketing_agency', 'agency_id = employer_source_agency', 'left' )
            ->join( 'marketing_agent', 'agent_id = employer_source_agent', 'left' );
		
		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );		
			
		$employers = $this->db->get()->result_array();
        
        return $this->indexArray( $employers, 'employer_id' );
	}
	
	public function getEmployersCount( $options = [] )
	{
		//Get Applicant Info
		$this->db->flush_cache();
		$this->db->from('employer')
            ->join( 'country', 'country_id = employer_country' )
			->join( 'user', 'user_id = employer_user' );
		
		$this->setDBQueryOptions( $options );
		
		$employers = $this->db->count_all_results(); 
		
		return $employers;
	}
	
	public function addEmployer()
	{			
		$employer = $_POST['employer'];

		$user          = 
		$userData      =
		$employerData  =
        $contractData  = [];
		
		$user          = $employer['user'];

		//Start Transaction
		$this->db->trans_begin();

		//$employerNoExists = (new m_employer)->employerNumberExists( $employer['number'] );

		//if ( $employerNoExists ) {
		//	Message::addWarning( '* <strong>Employer #'.$employer['number'].'</strong> is already exists.');
	//		redirect( site_url( 'admin/employers/all' ) );
	//		exit;
	//	}
		
		$this->load->model( 'm_user' );
		
		//Insert User
		$userData 	= [
			'user_name'		    => strtolower( $user['name'] ),
			'user_password'		=> $this->m_user->encryptPass( $user['password'] ),
			'user_fullname'		=> ucwords( $employer['contact_person'] ),
			'user_email'		=> $employer['email'],
			
			'user_type'		    => 5, //Employer
			'user_status'		=> 1,
			'user_lastlogin'	=> null,
			'user_createdby'	=> $_SESSION['admin']['user']['user_id'],
			'user_updatedby'	=> $_SESSION['admin']['user']['user_id'],
			'user_created'		=> date( 'Y-m-d H:i:s', time() ),
			'user_updated'		=> date( 'Y-m-d H:i:s', time() ),
		];
		
		$userInserted 	= $this->db->insert( 'user', $userData );
		$userId 	= $this->db->insert_id();
		
		//Get country info
		$this->load->model( 'm_country' );
		$country = $this->m_country->getCountryById( $employer['country'] );
		
		//Insert Employer
		$employerData = [
			'employer_user'				       => $userId,
			//'employer_no'				       => $employer['number'],
			'employer_name'				       => $employer['name'],
			'employer_contact_person'          => ucwords( $employer['contact_person'] ),
			'employer_contact'			       => $employer['contact'],
			'employer_email'			       => $employer['email'],
			'employer_address'			       => ucwords( $employer['address'] ),
			'employer_country'			       => (int) $country['country_id'],
       		'employer_source_agency'           => (int) $employer['source_agency'],
       		'employer_source_agent'	           => (int) $employer['source_agent'],
    		'employer_agency_commission'       => (float) $employer['source_agency_commission'],
    		'employer_agent_commission'        => (float) $employer['source_agent_commission'],
    		'employer_agency_commission_from'  => $employer['source_agency_commission_from'],
            'employer_agent_commission_from'   => $employer['source_agent_commission_from'],
			'employer_remarks'			       => $employer['remarks'],
			
			
		
			
			'employer_createdby'		       => $_SESSION['admin']['user']['user_id'],
			'employer_updatedby'		       => $_SESSION['admin']['user']['user_id'],
			'employer_created'			       => date( 'Y-m-d H:i:s', time() ),
			'employer_updated'			       => date( 'Y-m-d H:i:s', time() ),		
		];
		
		$employerInserted	= $this->db->insert( 'employer', $employerData );
		$employerId 		= $this->db->insert_id();

        $employer = $this->getEmployerById( $employerId );

        //Create employer slug
		$this->db->flush_cache();
		$this->db->where([
				'employer_id' => $employerId,
			])->update( 'employer', [ 
				'employer_slug' => str_pad( $employerId, 10, '0', STR_PAD_LEFT ).'/'.strSlug( $employer['employer_name'] ),
			]);

		//Update contract
        if ( $employer['employer_source_agency'] ) {
        	$this->load->model( 'm_contract_marketing_agency' );
        	( new m_contract_marketing_agency )->createContract( $employerId );
        }

        //Update contract
        if ( $employer['employer_source_agent'] ) {
        	$this->load->model( 'm_contract_marketing_agent' );
        	( new m_contract_marketing_agent )->createContract( $employerId );
        }
        
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() ) {
			$this->db->trans_rollback();
			return false;
		}
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		return $employer;
		
	}
	
	public function updateEmployer( $employerId )
	{			
		$post 		= $_POST['employer'];
		$employer	= $this->getEmployerById( $employerId );		

		$user	  			= 
		$userData			=
		$employerData		=
        $contractData       = [];

        $user = $post['user'];
				
		//Start Transaction
		$this->db->trans_begin(); 
		
		//Get country info
		$this->load->model( 'm_country' );
		$country = $this->m_country->getCountryById( $post['country'] );

		//Update Employer
		$employerData = [
			//'employer_user'				       => $employerId,
			'employer_no'				       => $post['number'],
			'employer_name'				       => $post['name'],
			'employer_contact_person'	       => ucwords( $post['contact_person'] ),
			'employer_contact'			       => $post['contact'],
			'employer_selections'			       => $post['selections'],
			'employer_email'			       => $post['email'],
			'employer_address'			       => $post['address'],
			'employer_country'			       => (int) $country['country_id'],
            'employer_source_agency'	       => (int) $post['source_agency'],
            'employer_source_agent'	           => (int) $post['source_agent'],
           	'rs_id'							   => (int) $post['employer_user'],
            'employer_agency_commission'       => (float) $post['source_agency_commission'], //optional
            'employer_agent_commission'        => (float) $post['source_agent_commission'],  //optional
            'employer_agency_commission_from'  => $post['source_agency_commission_from'],
            'employer_agent_commission_from'   => $post['source_agent_commission_from'],
			'employer_remarks'			       => $post['remarks'],
			'employer_updatedby'		       => $_SESSION['admin']['user']['user_id'],
			'employer_updated'			       => date( 'Y-m-d H:i:s', time() ),		
		];
		
		$employerUpdated	= 
		$this->db->where([
			'employer_id'	=> $employerId,
		])
		->update( 'employer', $employerData );
        
		//Update User
		if ( isset( $post['change-password'] ) ) {
			$this->load->model( 'm_user' );
			
			$userData 	= [
				'user_password'		=> ( new m_user )->encryptPass( $user['password'] ),
				'user_fullname'		=> ucwords( $post['contact_person'] ),
				'user_email'		=> $post['email'],
				'user_updatedby'	=> $_SESSION['admin']['user']['user_id'],
				'user_updated'		=> date( 'Y-m-d H:i:s', time() ),
			];
			
			$userUpdated 	= 
			$this->db->where([
				'user_id'	=> $employer['employer_user'],
			])
			->update( 'user', $userData );			
		}
		
		//Deactive user
		if ( isset( $post['deactivated'] ) ) {
			$userData 	= [
				'user_status'		=> 0,
			];
			
			$userUpdated 	= 
			$this->db->where([
				'user_id'	=> $employer['employer_user'],
			])
			->update( 'user', $userData );	
		}
		
		$employer = $this->getEmployerById( $employerId );

		if(isset($employer['employer_name'])){
			//Create employer slug
			$this->db->flush_cache();
			$this->db->where([
					'employer_id' => $employerId,
				])->update( 'employer', [ 
					'employer_slug' => str_pad( $employerId, 10, '0', STR_PAD_LEFT ).'/'.strSlug( $employer['employer_name'] ),
				]);
		}

		//Update contract
		if ( (isset($employer['employer_source_agency'])) && ($employer['employer_source_agency'] > 0) ) {
			$this->load->model( 'm_contract_marketing_agency' );
        	( new m_contract_marketing_agency )->updateContract( $employerId );
		}

		//Update contract
		if ( (isset($employer['employer_source_agent'])) && ($employer['employer_source_agent'] > 0) ) {
	        $this->load->model( 'm_contract_marketing_agent' );
	        ( new m_contract_marketing_agent )->updateContract( $employerId );
	    }
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || empty( $employer ) ) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();
		
		return $employer;
		
	}

	public function employerNumberExists( $eNo )
	{
		$this->db->flush_cache();
		$this->db->from( 'employer' )
			->where([
				'employer_no' => strtoupper( $eNo ),
			]);

		$exists = $this->db->count_all_results() > 0;

		return $exists;
	}
	/* Protected Methods
	-------------------------------*/
	/* Protected Methods
	-------------------------------*/
	protected function endProcess()
	{
		if (isset($_SESSION['post']['admin']['employers/all'])) {
			unset($_SESSION['post']['admin']['employers/all']);
		}
		
		return $this;		
	}
	/* Private Methods
	-------------------------------*/
}
