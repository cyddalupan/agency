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

class m_commission_recruitment_agent extends MY_Model {
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
		$this->db->from( 'commission_recruitment_agent' )
			->join( 'bill', 'bill_id = commission_bill', 'inner' )
			->where([
				'commission_id' => $commissionId,
			]);

		$commission = $this->db->get()->row_array();

		return $commission;
	}

	public function getAgents( $onlyUnpaid = true, $from = null, $to = null )
	{
		//Get agents
		$this->db->flush_cache();
		$this->db->from( 'recruitment_agent' )
			->join( 'commission_recruitment_agent', 'commission_agent = agent_id', 'inner' );

		if ( $onlyUnpaid ) {
			$this->db->where([
					'commission_status' => 0
				]);
		}

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}
			
		$this->db->order_by( 'commission_created', 'DESC' );

		$agents = $this->db->get()->result_array();

		$this->indexArray( $agents, 'agent_id' );

		//Count applicants
		foreach ($agents as $agentId => $agent) {
			$this->db->flush_cache();
			$this->db->select('COUNT(`commission_applicant`) AS `applicants`', false)
				->from( 'commission_recruitment_agent' )
				->where([
					'commission_agent' => $agentId,
				]);

			if ( ! is_null( $from ) && ! is_null( $to ) ) {
				$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
			}

			$counter = $this->db->get()->row_array();

			$agents[$agentId] = array_merge( $agents[$agentId], $counter );
		}

		return $agents;
	}

	public function getCommissionDetails( $agentId, $onlyUnpaid = true, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'commission_recruitment_agent' )
			->join( 'applicant', 'applicant_id = commission_applicant', 'inner' )
			->join( 'job', 'job_id = applicant_job', 'inner' )
			->join( 'bill', 'bill_id = commission_bill', 'left' )
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
			->order_by( 'commission_applicant', 'ASC' );

		$details = $this->db->get()->result_array();

		return $this->indexArray( $details, 'commission_applicant');
	}

	public function getWorkers( $agentId, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'applicant')
			->join( 'commission_recruitment_agent', 'commission_applicant = applicant_id','inner' )
			->join( 'job', 'job_id = applicant_job', 'inner' )
			->where([
				'commission_agent' => $agentId,
				'commission_status'=> 0,
			]);

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}

		$workers = $this->db->get()->result_array();

		return $this->indexArray( $workers, 'applicant_id' );
	}

	public function getWorker( $applicantId )
	{
		$this->db->flush_cache();
		$this->db->from( 'applicant')
			->join( 'job', 'job_id = applicant_job', 'inner' )
			->where([
				'applicant_id' => $applicantId,
			]);

		$worker = $this->db->get()->row_array();

		return $worker;
	}

	public function paid( $commissionId )
	{
		$amount = (float) $_POST['payment']['amount'];

		//Start Transaction
		$this->db->trans_begin();

		//Update commission
		$commissionData = [
			'commission_status'    => 1,
			'commission_amount'    => (float) $amount,
			'commission_remarks'   => 'Commission has been closed.',
			'commission_updatedby' => $_SESSION['admin']['user']['user_id'],
			'commission_updated'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->where([
				'commission_id' => $commissionId,
			])->update( 'commission_recruitment_agent', $commissionData);

		$commission = $this->getCommissionById( $commissionId );
		/*
		//Insert payment detail
		$ORId = $this->generateOR( $commission['bill_id'] );

		$paymentData = [
			'payment_or'        => 'SOA-'.$ORId,
			'payment_applicant' => $commission['commission_applicant'],
			'payment_agent'     => $commission['commission_agent'],
			'payment_amount'    => (float) $amount,
			'payment_bill'      => $commission['bill_id'],
			'payment_createdby' => $_SESSION['admin']['user']['user_id'],
			'payment_created'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->insert( 'payment_recruitment', $paymentData );
		$paymentId = $this->db->insert_id();
		*/

		//Generate voucher Id
		$this->load->model( 'm_voucher' );
		$voucher = ( new m_voucher )->generate();

		//Insert voucher
        $voucherData = [
			'voucher_number'            => $voucher,
			'voucher_amount'            => (float) $amount,
			'voucher_marketing_agency'  => null,
			'voucher_marketing_agent'   => null,
			'voucher_recruitment_agent' => $commission['commission_agent'],
			'voucher_employer'          => $commission['commission_employer'],
			'voucher_applicant'         => $commission['commission_applicant'],
			'voucher_remarks'           => 'Paid a total of P'.number_format( $amount, 2 ).'.',
			'voucher_date'              => date( 'Y-m-d', time() ),
			'voucher_createdby'         => $_SESSION['admin']['user']['user_id'],
			'voucher_created'           => date( 'Y-m-d H:i:s', time() ),
        ];

        $this->db->flush_cache();
        $this->db->insert( 'voucher', $voucherData );
        $voucherId = $this->db->insert_id();

		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $voucherId) {
			$this->db->trans_rollback();
			return false;
		}

		//Commit transaction
		$this->db->trans_commit();

		return $commission;
	}

	public function paidAll( $agentId, $from = null, $to = null )
	{
		$amount = (float) $_POST['payment']['amount'];

		//Start Transaction
		$this->db->trans_begin();

		$totalPaid = 0;

		//Generate voucher Id
		$this->load->model( 'm_voucher' );
		$voucher = ( new m_voucher )->generate();

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
				'commission_agent' => $agentId,
			]);

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`commission_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}

		$this->db->update( 'commission_recruitment_agent', $commissionData );

		$affectedRows = $this->db->affected_rows();

		//Insert voucher
        $voucherData = [
			'voucher_number'            => $voucher,
			'voucher_amount'            => (float) $amount,
			'voucher_marketing_agency'  => null,
			'voucher_marketing_agent'   => null,
			'voucher_recruitment_agent' => $agentId,
			'voucher_employer'          => null,
			'voucher_applicant'         => null,
			'voucher_remarks'           => 'Paid a total of P'.number_format( $amount, 2 ).'.',
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

	public function hasCommission( $applicantId )
	{
		$this->db->flush_cache();
		$this->db->from( 'commission_recruitment_agent' )
			->where([
				'commission_applicant' => $applicantId,
			]);

		$existing = $this->db->count_all_results() > 0;

		return $existing;
	}

	public function createCommission( $applicantId = 0)
	{
		if ( $this->hasCommission( $applicantId ) ) {
			return false;
		}

		//Start Transaction
		$this->db->trans_begin();

		//Get applicant info
		$this->db->flush_cache();
		$this->db->from( 'applicant' )
			->where([
				'applicant_id' => $applicantId,
			]);
		
		$applicant = $this->db->get()->row_array();
		
		if ( empty( $applicant['applicant_source'] ) ) {
			return false;
		}

		$agentId = $applicant['applicant_source'];

		//Get bill info
		$this->db->flush_cache();
		$this->db->from( 'bill' )
			->where([
				'bill_applicant'  => $applicantId,
			]);

		$bill = $this->db->get()->row_array();

		if(!isset($bill['bill_id'])){
			$bill['bill_id'] = 0;
		}

		//Create an entry for commission
		$commissionData =[
			'commission_agent'      => $agentId,
			'commission_applicant'  => $applicantId,
			'commission_employer'   => $applicant['applicant_employer'],
			'commission_bill'       => $bill['bill_id'],
			'commission_amount'     => 0,
			'commission_remarks'    => 'Applicant was deployed',
			'commission_status'     => 0,
			'commission_createdby'  => $_SESSION['admin']['user']['user_id'],
			'commission_updatedby'  => $_SESSION['admin']['user']['user_id'],
			'commission_created'    => date( 'Y-m-d H:i:s', time() ),
			'commission_updated'    => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
        $this->db->insert( 'commission_recruitment_agent', $commissionData );
        $commissionId = $this->db->insert_id();

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

	public function generateOR( $billId )
	{
		$this->db->flush_cache();
		$result = $this->db->query("
				SELECT `AUTO_INCREMENT` 
				FROM `information_schema`.`tables` 
				WHERE `TABLE_SCHEMA`='"
				. $this->db->database."' 
				AND `TABLE_NAME`='payment_recruitment' 
				LIMIT 1
			")->row_array();

		$ORId = $result['AUTO_INCREMENT'];

		$billId = (int) $billId;

		return $billId.str_pad( $ORId, 6, '0', STR_PAD_LEFT );
	}
	
	/* Protected Methods
	-------------------------------*/
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
