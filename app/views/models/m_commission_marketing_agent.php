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

class m_commission_marketing_agent extends MY_Model {
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
	public function getCommissionById( $commissionId )
	{
		$this->db->flush_cache();
		$this->db->from( 'commission_marketing_agent' )
			->where([
				'commission_id' => $commissionId,
			]);

		$commission = $this->db->get()->row_array();

		return $commission;
	}

	public function getAgents( $onlyUnpaid = true, $from = null, $to = null  )
	{
		//Start Transaction
		$this->db->trans_begin();

		//Get agents
		$this->db->flush_cache();
		$this->db->from( 'marketing_agent' )
			->join( 'commission_marketing_agent', 'commission_agent = agent_id', 'inner' );

		if ( $onlyUnpaid ) {
			$this->db->where([
					'commission_status' => 0
				]);
		}

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}
			
		$this->db->order_by( 'commission_status', 'ASC' )
			->order_by( 'commission_created', 'DESC' );

		$agents = $this->db->get()->result_array();

		$this->indexArray( $agents, 'agent_id' );

		//Count applicants
		foreach ($agents as $agentId => $agent) {
			$this->db->flush_cache();
			$this->db->select('SUM(`commission_amount`) AS `total_commission`', false)
				->from( 'commission_marketing_agent' )
				->where([
					'commission_agent' => $agentId,
				]);

			if ( ! is_null( $from ) && ! is_null( $to ) ) {
				$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
			}

			$counter = $this->db->get()->row_array();

			$this->db->flush_cache();
			$this->db->select('SUM(`commission_amount`) as total_paid', false)
				->from( 'commission_marketing_agent')
				->where([
					'commission_agent' => $agentId,
					'commission_status' => 1,
				]);

			if ( ! is_null( $from ) && ! is_null( $to ) ) {
				$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
			}

			$amount = $this->db->get()->row_array();
			$amount['remaining'] = $counter['total_commission'] - $amount['total_paid'];

			$agents[$agentId] = array_merge( $agents[$agentId], $counter, $amount );
		}

		//Rollback if transaction fails
		if ( ! $this->db->trans_status() ) {
			$this->db->trans_rollback();
			return false;
		}

		//Commit transaction
		$this->db->trans_commit();

		return $agents;
	}

	public function getCommissionDetails( $agentId, $onlyUnpaid = true, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'commission_marketing_agent' )
			->join( 'bill', 'bill_id = commission_bill', 'inner' )
			->join( 'employer', 'employer_id = commission_employer', 'inner' )
			->join( 'applicant', 'applicant_id = commission_applicant', 'inner' )
			->join( 'job', 'job_id = applicant_job', 'inner' )
			->where([
				'commission_agent' => $agentId
			]);

		if ( $onlyUnpaid ) {
			$this->db->where([
					'commission_status' => 0,
				]);
		}

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}

		$this->db->order_by( 'commission_status', 'ASC' )
			->order_by( 'commission_employer', 'ASC' )
			->order_by( 'commission_applicant', 'ASC' );

		$details = $this->db->get()->result_array();

		return $this->indexArray( $details, 'commission_applicant');
	}

	public function markAsPaid( $commissionId )
	{
		$commission = $this->getCommissionById( $commissionId );

		//Start Transaction
		$this->db->trans_begin();

		//Generate voucher Id
		$this->load->model( 'm_voucher' );
		$voucher = ( new m_voucher )->generate();

		$commissionData = [
			'commission_status'    => 1,
			'commission_voucher'   => $voucher,
			'commission_updatedby' => $_SESSION['admin']['user']['user_id'],
			'commission_updated'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$commissionUpdate =
		$this->db->where([
				'commission_id' => $commissionId,
			])->update( 'commission_marketing_agent', $commissionData );

		//Insert voucher
        $voucherData = [
			'voucher_number'            => $voucher,
			'voucher_amount'            => (float) $commission['commission_amount'],
			'voucher_marketing_agency'  => null,
			'voucher_marketing_agent'   => $commission['commission_agent'],
			'voucher_recruitment_agent' => null,
			'voucher_employer'          => $commission['commission_employer'],
			'voucher_applicant'         => $commission['commission_applicant'],
			'voucher_remarks'           => 'Paid a total of P'.number_format( $commission['commission_amount'], 2 ).'.',
			'voucher_date'              => date( 'Y-m-d', time() ),
			'voucher_createdby'         => $_SESSION['admin']['user']['user_id'],
			'voucher_created'           => date( 'Y-m-d H:i:s', time() ),
        ];

        $this->db->flush_cache();
        $this->db->insert( 'voucher', $voucherData );
        $voucherId = $this->db->insert_id();

		if ( ! $commissionUpdate ) {
			return false;
		}

		$this->load->model( 'm_voucher' );
		$voucher = ( new m_voucher )->getVoucherById( $voucherId );

		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $voucher) {
			$this->db->trans_rollback();
			return false;
		}

		//Commit transaction
		$this->db->trans_commit();

		return $voucher;
	}

	public function paidAll( $agentId, $from = null, $to = null )
	{

		//Start Transaction
		$this->db->trans_begin();

		$totalPaid = 0;

		//Generate voucher Id
		$this->load->model( 'm_voucher' );
		$voucher = ( new m_voucher )->generate();

		//Just compute the total amount for given date range
		$this->db->flush_cache();
		$this->db->select( '*, SUM(`commission_amount`) as total_amount', false)
			->from('commission_marketing_agent')
			->where([
				'commission_agent'  => $agentId,
				'commission_status' => 0,
			]);

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}

		$commission = $this->db->get()->row_array();

		//Mark as paid
		$commissionData = [
			'commission_status'    => 1,
			'commission_voucher'   => $voucher,
			'commission_updatedby' => $_SESSION['admin']['user']['user_id'],
			'commission_updated'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$commissionUpdate =
		$this->db->where([
				'commission_agent'  => $agentId,
				'commission_status' => 0,
			]);

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}

		$this->db->update( 'commission_marketing_agent', $commissionData );

		$affectedRows = $this->db->affected_rows();

		//Insert voucher
        $voucherData = [
			'voucher_number'            => $voucher,
			'voucher_amount'            => (float) $commission['total_amount'],
			'voucher_marketing_agency'  => null,
			'voucher_marketing_agent'   => $agentId,
			'voucher_recruitment_agent' => null,
			'voucher_employer'          => $affectedRows == 1 ? $commission['commission_employer'] : 0,
			'voucher_applicant'         => $affectedRows == 1 ? $commission['commission_applicant'] : 0,
			'voucher_remarks'           => 'Paid a total of P'.number_format( $commission['total_amount'], 2 ).'.',
			'voucher_date'              => date( 'Y-m-d', time() ),
			'voucher_createdby'         => $_SESSION['admin']['user']['user_id'],
			'voucher_created'           => date( 'Y-m-d H:i:s', time() ),
        ];

        $this->db->flush_cache();
        $this->db->insert( 'voucher', $voucherData );
        $voucherId = $this->db->insert_id();

		if ( ! $commissionUpdate ) {
			return false;
		}

		$this->load->model( 'm_voucher' );
		$voucher = ( new m_voucher )->getVoucherById( $voucherId );

		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $voucher) {
			$this->db->trans_rollback();
			return false;
		}

		//Commit transaction
		$this->db->trans_commit();

		return $voucher;
	}

	public function hasCommission( $employerId, $applicantId )
	{
		$this->db->flush_cache();
		$this->db->from( 'commission_marketing_agent' )
			->where([
				'commission_employer'  => $employerId,
				'commission_applicant' => $applicantId,
			]);

		$existing = $this->db->count_all_results() > 0;

		return $existing;
	}

	public function createCommission( $employerId = 0 , $applicantId = 0)
	{
		if ( $this->hasCommission( $employerId, $applicantId ) ) {
			return false;
		}

		//Start Transaction
		$this->db->trans_begin();

		//Get employer info
		$this->db->flush_cache();
		$this->db->from( 'employer' )
			->where([
				'employer_id' => $employerId,
			]);
		
		$employer = $this->db->get()->row_array();

		$agentId = $employer['employer_source_agent'];

		if ( ! $agentId ) {
			return false;
		}

		$this->load->model( 'm_contract_marketing_agent' );
		$contract = ( new m_contract_marketing_agent )->getContractByEmployerId( $employerId );

		//Get bill info
		$this->db->flush_cache();
		$this->db->from( 'bill' )
			->where([
				'bill_employer'  => $employerId,
				'bill_applicant' => $applicantId,
			]);

		$bill = $this->db->get()->row_array();

		if($bill['bill_id'] != '')
		{
			//Get bill details
			$this->db->flush_cache();
			$this->db->from( 'bill_detail' )
				->where_in( 'detail_fee', [1,2] )
				->where([
					'detail_bill' => $bill['bill_id'],
				])
				->order_by( 'detail_fee', 'ASC' );
		}
		else
		{
			$bill['bill_id'] = 0;
		}

		$fees = $this->db->get()->result_array();

		$this->indexArray( $fees, 'detail_fee' );

		$baseAmount = $contract['contract_placement_fee'] == 1
						? $fees[1]['detail_amount']
						: $fees[2]['detail_amount'];
		$amount     = $contract['contract_placement_fee'] == 1
						? $fees[1]['detail_amount'] * ( $contract['contract_percentage']/100 ) 
						: $fees[2]['detail_amount'] * ( $contract['contract_percentage']/100 );

		//Create an entry for commission
		$commissionData =[
			'commission_agent'          => $agentId,
			'commission_applicant'      => $applicantId,
			'commission_employer'       => $employerId,
			'commission_bill'           => $bill['bill_id'],
			'commission_placement_fee'  => $contract['contract_placement_fee'],
			'commission_service_fee'    => $contract['contract_service_fee'],
			'commission_percentage'     => $contract['contract_percentage'],
			'commission_base_amount'    => (float) $baseAmount,
			'commission_amount'         => (float) $amount,
			'commission_remarks'        => 'Applicant was deployed',
			'commission_status'         => 0,
			'commission_voucher'        => null,
			'commission_createdby'      => $_SESSION['admin']['user']['user_id'],
			'commission_updatedby'      => $_SESSION['admin']['user']['user_id'],
			'commission_created'        => date( 'Y-m-d H:i:s', time() ),
			'commission_updated'		=> date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
        $this->db->insert( 'commission_marketing_agent', $commissionData );
        $commissionId = $this->db->insert_id();
 
        //update contract
        $contractData = [
        	'contract_base_amount' => $commissionData['commission_base_amount'],
        	'contract_amount'      => $commissionData['commission_amount'],
        ];

        $this->db->flush_cache();
        $this->db->where([
        		'contract_id' => $contract['contract_id'],
        	])->update( 'contract_marketing_agency', $contractData );

        $commission = $this->getCommissionById( $commissionId );

        //Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $commission) {
			$this->db->trans_rollback();
			return false;
		}

		//Commit transaction
		$this->db->trans_commit();

		return $commission;
	}
	
	/* Protected Methods
	-------------------------------*/
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
