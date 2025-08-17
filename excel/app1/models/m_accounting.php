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

class m_accounting extends MY_Model {
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
	public function getEmployers()
	{
		$this->db->flush_cache();
		$this->db->from( 'employer' )
			->order_by( 'employer_name' );

		$employers = $this->db->get()->result_array();

		return $this->indexArray( $employers, 'employer_id' );
	}

	public function getWorkersTransactions( $status = null, $from = null, $to = null, $byEmployerId = false )
	{
		$this->db->from('bill_payment_applicant')
			->join('applicant', 'applicant_id = payment_applicant', 'inner')
			->join('or', 'or_number = payment_or', 'inner')
			->join('employer', 'employer_id = or_employer', 'inner');

		if ( ! is_null( $status ) ) {
			$this->db->where([
				'or_status' =>  $status ,
			]);
		}
		
		$this->db->order_by('payment_applicant', 'ASC')
			->order_by('or_date', 'ASC')
			->order_by('payment_created', 'ASC');

		if ( $byEmployerId > 0 ) {
			$this->db->where([
				'or_employer' => $byEmployerId,
			]);
		}

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where("DATE(or_date) BETWEEN '".fdate('Y-m-d', $from)."' AND '".fdate('Y-m-d', $to)."'", null, false);
		}

		$transactions = $this->db->get()->result_array();

		$this->indexArray( $transactions, 'payment_or' );

		return $transactions;
	}

	public function getEmployersTransactions( $employerId = null, $status = null,  $from = null, $to = null )
	{
		$this->db->from('bill_payment_employer')
			->join('or', 'or_number = payment_or', 'inner')
			->join('employer', 'employer_id = or_employer', 'inner');			

		if ( ! is_null( $status ) ) {
			$this->db->where([
				'or_status' =>  $status ,
			]);
		}

		$this->db->order_by('payment_employer', 'ASC')
			->order_by('or_date', 'ASC')
			->order_by('payment_created', 'ASC');

		if ( ! is_null( $employerId ) ) {
			$this->db->where([
				'employer_id' => $employerId,
			]);
		}

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where("DATE(or_date) BETWEEN '".fdate('Y-m-d', $from)."' AND '".fdate('Y-m-d', $to)."'", null, false);
		}

		$transactions = $this->db->get()->result_array();

		$this->indexArray( $transactions, 'payment_or' );

		return $transactions;
	}

	public function getMarketingAgencies( $status = null, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join('marketing_agency', 'agency_id = voucher_marketing_agency', 'inner')
			->join('applicant', 'applicant_id = voucher_applicant', 'left')
			->join('employer', 'employer_id = voucher_employer', 'left')
			->where([
				'voucher_marketing_agency >' => 0,
			]);

		if ( ! is_null( $status ) ) {
			$this->db->where([
				'voucher_status' =>  $status ,
			]);
		}

		$this->db->order_by('voucher_marketing_agency', 'ASC')
			->order_by('voucher_employer', 'ASC')
			->order_by('applicant_first', 'ASC')
			->order_by('applicant_last', 'ASC');

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where("DATE(voucher_date) BETWEEN '".fdate('Y-m-d', $from)."' AND '".fdate('Y-m-d', $to)."'", null, false);
		}

		$agencies = $this->db->get()->result_array();

		return $agencies;
 	}

	public function getMarketingAgencyTransactions( $agencyId, $status = null, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join('applicant', 'applicant_id = voucher_applicant', 'inner')
			->join('employer', 'employer_id = voucher_employer', 'inner')
			->where([
				'voucher_marketing_agency' => $agencyId,
			]);

		if ( ! is_null( $status ) ) {
			$this->db->where([
				'voucher_status' =>  $status ,
			]);
		}

		$this->db->order_by('voucher_employer', 'ASC')
			->order_by('applicant_first', 'ASC')
			->order_by('applicant_last', 'ASC');

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where("DATE(voucher_date) BETWEEN '".fdate('Y-m-d', $from)."' AND '".fdate('Y-m-d', $to)."'", null, false);
		}

		$agency = $this->db->get()->result_array();

		return $agency;
	}

	public function getMarketingAgents( $status = null, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join('marketing_agent', 'agent_id = voucher_marketing_agent', 'inner')
			->join('applicant', 'applicant_id = voucher_applicant', 'left')
			->join('employer', 'employer_id = voucher_employer', 'left')
			->where([
				'voucher_marketing_agent >' => 0,
			]);

		if ( ! is_null( $status ) ) {
			$this->db->where([
				'voucher_status' =>  $status ,
			]);
		}
		
		$this->db->order_by('voucher_marketing_agent', 'ASC')
			->order_by('voucher_employer', 'ASC')
			->order_by('applicant_first', 'ASC')
			->order_by('applicant_last', 'ASC');

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where("DATE(voucher_date) BETWEEN '".fdate('Y-m-d', $from)."' AND '".fdate('Y-m-d', $to)."'", null, false);
		}

		$agents = $this->db->get()->result_array();

		//dd($this->db->last_query());

		return $agents;
	}

	public function getMarketingAgentTransactions( $agentId,  $status = null, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join('applicant', 'applicant_id = voucher_applicant', 'left')
			->join('employer', 'employer_id = voucher_employer', 'left')
			->where([
				'voucher_marketing_agent' => $agentId,
			]);

		if ( ! is_null( $status ) ) {
			$this->db->where([
				'voucher_status' =>  $status ,
			]);
		}

		$this->db->order_by('voucher_employer', 'ASC')
			->order_by('applicant_first', 'ASC')
			->order_by('applicant_last', 'ASC');

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where("DATE(voucher_date) BETWEEN '".fdate('Y-m-d', $from)."' AND '".fdate('Y-m-d', $to)."'", null, false);
		}

		$agent = $this->db->get()->result_array();

		return $agent;
	}
 
	public function getRecruitmentAgents( $status, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join('recruitment_agent', 'agent_id = voucher_recruitment_agent', 'inner')
			->join('applicant', 'applicant_id = voucher_applicant', 'inner')
			->join('employer', 'employer_id = voucher_employer', 'inner')
			->where([
				'voucher_recruitment_agent >' => 0,
			]);

		if ( ! is_null( $status ) ) {
			$this->db->where([
				'voucher_status' =>  $status ,
			]);
		}

		$this->db->order_by('voucher_recruitment_agent', 'ASC')
			->order_by('voucher_employer', 'ASC')
			->order_by('applicant_first', 'ASC')
			->order_by('applicant_last', 'ASC');

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where("DATE(voucher_date) BETWEEN '".fdate('Y-m-d', $from)."' AND '".fdate('Y-m-d', $to)."'", null, false);
		}

		$agents = $this->db->get()->result_array();

		return $agents;		 
	}

	public function getRecruitmentAgentTransactions( $agentId, $status = null, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join('applicant', 'applicant_id = voucher_applicant', 'inner')
			->join('employer', 'employer_id = voucher_employer', 'inner')
			->where([
				'voucher_recruitment_agent' => $agentId,
			]);

		if ( ! is_null( $status ) ) {
			$this->db->where([
				'voucher_status' =>  $status ,
			]);
		}

		$this->db->order_by('voucher_employer', 'ASC')
			->order_by('applicant_first', 'ASC')
			->order_by('applicant_last', 'ASC');

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where("DATE(voucher_date) BETWEEN '".fdate('Y-m-d', $from)."' AND '".fdate('Y-m-d', $to)."'", null, false);
		}

		$agent = $this->db->get()->result_array();

		return $agent;
	}
 
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
