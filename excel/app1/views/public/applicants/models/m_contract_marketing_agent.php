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

class m_contract_marketing_agent extends MY_Model {
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
	public function getContractById( $contractId )
	{
		$this->db->flush_cache();
		$this->db->from( 'contract_marketing_agent' )
			->where([
				'contract_id' => $contractId,
			]);

		$contract = $this->db->get()->row_array();

		return $contract;
	}

	public function getContractByEmployerId( $employerId )
	{
		$this->db->flush_cache();
		$this->db->from( 'contract_marketing_agent' )
			->where([
				'contract_employer' => $employerId,
			]);

		$contract = $this->db->get()->row_array();

		return $contract;
	}

	public function hasContract( $employerId, $agentId = null )
	{
		if ( is_null( $agentId) ) {
			$this->db->flush_cache();
			$this->db->from( 'employer' )
				->where([
					'employer_id' => $employerId,
				]);

			$employer = $this->db->get()->row_array();

			if ( empty( $employer['employer_source_agent'] ) ) {
				return false;
			}

			$agentId = $employer['employer_source_agent'];
		}

		$this->db->flush_cache();
		$this->db->from( 'contract_marketing_agent' )
			->where([
				'contract_agent'   => $agentId,
				'contract_employer' => $employerId,
			]);

		$existing = $this->db->count_all_results() > 0;

		return $existing;
	}

	public function createContract( $employerId )
	{
		//Start Transaction
		$this->db->trans_begin();

		//Get employer info
		$this->db->flush_cache();
		$this->db->from( 'employer' )
			->where([
				'employer_id' => $employerId,
			]);

		$employer = $this->db->get()->row_array();

		//If no source, skip
		if ( empty( $employer['employer_source_agent'] ) ) {
			return false;
		}

		$agentId = $employer['employer_source_agent'];

		//If has already contract, skip
		if ( $this->hasContract( $employerId, $agentId ) ) {
			return false;
		}

		//Create an entry for commission
		$contractData =[
			'contract_agent'         => $agentId,
			'contract_employer'      => $employerId,
			'contract_percentage'    => $employer['employer_agent_commission'],
			'contract_placement_fee' => $employer['employer_agent_commission_from'] == 'Placement Fee',
			'contract_service_fee'   => $employer['employer_agent_commission_from'] == 'Service Fee',
			'contract_amount'        => 0, //will compute automatically
			'contract_remarks'       => 'Agent was selected as source of the employer',
			'contract_status'        => 0,
			'contract_createdby'     => $_SESSION['admin']['user']['user_id'],
			'contract_updatedby'     => $_SESSION['admin']['user']['user_id'],
			'contract_created'       => date( 'Y-m-d H:i:s', time() ),
			'contract_updated'       => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
        $this->db->insert( 'contract_marketing_agent', $contractData );
        $contractId = $this->db->insert_id();

        //Get fresh copy of the contract
        $contract = $this->getContractById( $contractId );

        //Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $contract) {
			$this->db->trans_rollback();
			return false;
		}

		//Commit transaction
		$this->db->trans_commit();

		return $contract;
	}

	public function updateContract( $employerId )
	{
		//Get employer info
		$this->db->flush_cache();
		$this->db->from( 'employer' )
			->where([
				'employer_id' => $employerId,
			]);

		$employer = $this->db->get()->row_array();

		//If no source, skip
		if ( empty( $employer['employer_source_agent'] ) ) {
			return false;
		}

		$agentId = $employer['employer_source_agent'];

		//If has already contract, skip
		if ( ! $this->hasContract( $employerId, $agentId ) ) {
			return $this->createContract( $employerId );
		}

		$contract = $this->getContractByEmployerId( $employerId );

		//Create an entry for commission
		$contractData =[
			'contract_percentage'    => $employer['employer_agent_commission'],
			'contract_placement_fee' => $employer['employer_agent_commission_from'] == 'Placement Fee',
			'contract_service_fee'   => $employer['employer_agent_commission_from'] == 'Service Fee',
			'contract_updatedby'     => $_SESSION['admin']['user']['user_id'],
			'contract_updated'       => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
        $this->db->where([
        		'contract_id'   => $contract['contract_id'],
        	])
        	->update( 'contract_marketing_agent', $contractData );

        //Get fresh copy of the contract
        $contract = $this->getContractById( $contractId );

		return $contract;
	}
	
	/* Protected Methods
	-------------------------------*/
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
