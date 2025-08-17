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

class m_voucher extends MY_Model {
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
	public function getVoucherById( $voucherId, $withWorkers = false )
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join( 'user', 'user_id = voucher_approvedby', 'left')
			->where([
				'voucher_id' => $voucherId,
			]);

		$voucher = $this->db->get()->row_array();

		$voucher = array_merge( $voucher, [
			'marketing-agency'  => $this->getMarketingAgency( $voucher ),
			'marketing-agent'   => $this->getMarketingAgent( $voucher ),
			'recruitment-agent' => $this->getRecruitmentAgent( $voucher ),
		]);

		if ( $withWorkers === true ) {
			$voucher = array_merge( $voucher, [
				'workers'  => $this->getWorkers( $voucher ),
			]);
		}

		return $voucher;
	}

	public function getVoucher( $voucherNo )
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join( 'user', 'user_id = voucher_approvedby', 'left')
			->where([
				'voucher_number' => $voucherNo,
			]);

		$voucher = $this->db->get()->row_array();

		return $voucher;
	}
	
	public function getVouchers()
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join( 'user', 'user_id = voucher_approvedby', 'left')
			->join( 'employer', 'employer_id = voucher_employer', 'left' )
			->join( 'applicant', 'applicant_id = voucher_applicant', 'left' )
			->join( 'deployed', 'deployed_applicant = applicant_id', 'left')
			->order_by( 'voucher_created', 'DESC' );

		$vouchers = $this->db->get()->result_array();

		$this->indexArray( $vouchers, 'voucher_number' );

		foreach ( $vouchers as $voucherNumber => $voucher ) {

			$vouchers[$voucherNumber] = array_merge( $vouchers[$voucherNumber], [
				'marketing-agency'  => $this->getMarketingAgency( $voucher ),
				'marketing-agent'   => $this->getMarketingAgent( $voucher ),
				'recruitment-agent' => $this->getRecruitmentAgent( $voucher ),
			]);
			
		}

		return $vouchers;
	}

	public function searchVouchers()
	{
		$voucherNumber = $_GET['search']['voucher'];

		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->join( 'user', 'user_id = voucher_approvedby', 'left')
			->join( 'employer', 'employer_id = voucher_employer', 'left' )
			->join( 'applicant', 'applicant_id = voucher_applicant', 'left' )
			->where( 'voucher_number LIKE \'%'.addslashes( $voucherNumber ).'%\'' , null, false )
			->order_by( 'voucher_created', 'DESC' );

		$vouchers = $this->db->get()->result_array();

		$this->indexArray( $vouchers, 'voucher_number' );

		foreach ( $vouchers as $voucherNumber => $voucher ) {

			$vouchers[$voucherNumber] = array_merge( $vouchers[$voucherNumber], [
				'marketing-agency'  => $this->getMarketingAgency( $voucher ),
				'marketing-agent'   => $this->getMarketingAgent( $voucher ),
				'recruitment-agent' => $this->getRecruitmentAgent( $voucher ),
			]);

		}

		return $vouchers;
	}

	public function generate( $prepend = null)
	{
		$this->db->flush_cache();
		$result = $this->db->query("
				SELECT `AUTO_INCREMENT` 
				FROM `information_schema`.`tables` 
				WHERE `TABLE_SCHEMA`='"
				. $this->db->database."' 
				AND `TABLE_NAME`='voucher' 
				LIMIT 1
			")->row_array();

		$voucherId = $result['AUTO_INCREMENT'];

		return $prepend.str_pad( $voucherId, 7, '0', STR_PAD_LEFT );
	}

	/* Protected Methods
	-------------------------------*/
	protected function getMarketingAgency( $voucher )
	{
		if ( ! $voucher['voucher_marketing_agency'] ) {
			return false;
		}

		$this->db->flush_cache();
		$this->db->from( 'marketing_agency' )
			->where([
				'agency_id' => $voucher['voucher_marketing_agency'],
			]);

		$agency = $this->db->get()->row_array();

		return $agency;
	}

	protected function getMarketingAgent( $voucher )
	{
		if ( ! $voucher['voucher_marketing_agent'] ) {
			return false;
		}

		$this->db->flush_cache();
		$this->db->from( 'marketing_agent' )
			->where([
				'agent_id' => $voucher['voucher_marketing_agent'],
			]);

		$agent = $this->db->get()->row_array();

		$voucher['marketing-agent'] = $agent;

		return $agent;
	}

	protected function getRecruitmentAgent( $voucher )
	{
		if ( ! $voucher['voucher_recruitment_agent'] ) {
			return false;
		}

		$this->db->flush_cache();
		$this->db->from( 'recruitment_agent' )
			->where([
				'agent_id' => $voucher['voucher_recruitment_agent'],
			]);

		$agent = $this->db->get()->row_array();

		return $agent;
	}

	protected function getWorkers( $voucher )
	{
		$workers = [];

		$this->db->flush_cache();

		if ( $voucher['voucher_marketing_agency'] ) {

			$this->db->from( 'commission_marketing_agency' );

		} elseif ( $voucher['voucher_marketing_agent'] ) {

			$this->db->from( 'commission_marketing_agent' );

		} elseif ( $voucher['voucher_recruitment_agent'] ) {

			$this->db->from( 'commission_recruitment_agent' );

		}

		$this->db->join( 'applicant', 'applicant_id = commission_applicant', 'inner' )
			->join( 'employer', 'employer_id = commission_employer', 'inner' )
			->join( 'deployed', 'deployed_applicant = applicant_id', 'left' )
			->where([
				'commission_voucher' => $voucher['voucher_number'],
			])
			->order_by( 'employer_name', 'ASC' )
			->order_by( 'applicant_first', 'ASC' )
			->order_by( 'applicant_last', 'ASC' )
			->group_by( 'applicant_id' );

		$workers = $this->db->get()->result_array();

		return $workers;
	}

	public function updateVoucher( $voucherId )
	{
		$post = $_POST['voucher'];

		$voucher = $this->getVoucherById( $voucherId );

		if ( $voucher['voucher_marketing_agency'] ) {
			$this->db->flush_cache();
			$this->db->where([
					'commission_voucher' => $voucher['voucher_number'],
				])->update( 'commission_marketing_agency', [
					'commission_voucher' => $post['number'],
				]);
		}

		if ( $voucher['voucher_marketing_agent'] ) {
			$this->db->flush_cache();
			$this->db->where([
					'commission_voucher' => $voucher['voucher_number'],
				])->update( 'commission_marketing_agent', [
					'commission_voucher' => $post['number'],
				]);
		}

		if ( $voucher['voucher_recruitment_agent'] ) {
			$this->db->flush_cache();
			$this->db->where([
					'commission_voucher' => $voucher['voucher_number'],
				])->update( 'commission_recruitment_agent', [
					'commission_voucher' => $post['number'],
				]);
		}

		$voucherData = [
			'voucher_number'  => $post['number'],
			'voucher_check'   => $post['check'],
			'voucher_remarks' => $post['remarks'],
			'voucher_date'    => date( 'Y-m-d', strtotime( $post['date'] ) ),
		];

		if ( isset( $post['amount'] ) ) {
			$voucherData['voucher_amount'] = (float) $post['amount'];
		}

		$this->db->flush_cache();
		$this->db->where([
				'voucher_id' => $voucherId
			])->update( 'voucher', $voucherData );

		$voucher = $this->getVoucherById( $voucherId );
		
		return $voucher;
	}

	public function revertVoucher( $voucherId )
	{
		$this->db->flush_cache();
		$this->db->from( 'voucher' )
			->where([
				'voucher_id' => $voucherId,
			]);

		$voucher = $this->db->get()->row_array();

		$txnBy =  $this->checkTxnFrom( $voucher['voucher_number'] );

		if ( $txnBy == 'marketing-agency' ) {
			$this->revertTxnByMarketingAgency( $voucher );
		} elseif ( $txnBy == 'marketing-agent' ) {
			 $this->revertTxnByMarketingAgent( $voucher );
		} elseif ( $txnBy == 'recruitment-agent' ) {
			 $this->revertTxnByRecruitmentAgent( $voucher );
		} else {
			return false;
		}

		$this->db->flush_cache();
		$this->db->where([
				'voucher_id' => $voucherId,
			])
			->delete( 'voucher' );

		return $voucher;
	}

	public function checkTxnFrom( $voucherNumber )
	{
		$this->db->flush_cache();
		$this->db->from( 'commission_marketing_agency' )
			->where([
				'commission_voucher' => $voucherNumber,
			]);

		if ( $this->db->count_all_results() > 0 ) {
			return 'marketing-agency';
		}

		$this->db->flush_cache();
		$this->db->from( 'commission_marketing_agent' )
			->where([
				'commission_voucher' => $voucherNumber,
			]);

		if ( $this->db->count_all_results() > 0 ) {
			return 'marketing-agent';
		}

		$this->db->flush_cache();
		$this->db->from( 'commission_recruitment_agent' )
			->where([
				'commission_voucher' => $voucherNumber,
			]);

		if ( $this->db->count_all_results() > 0 ) {
			return 'recruitment-agent';
		}

		return '';
	}

	public function revertTxnByMarketingAgency( $voucher )
	{
		$commissionData = [
			'commission_status'  => 0,
			'commission_voucher' => null,
		];

		$this->db->flush_cache();
		$this->db->where([
				'commission_voucher' => $voucher['voucher_number'],
			])
			->update( 'commission_marketing_agency', $commissionData );
	}

	public function revertTxnByMarketingAgent( $voucher )
	{
		$commissionData = [
			'commission_status'  => 0,
			'commission_voucher' => null,
		];

		$this->db->flush_cache();
		$this->db->where([
				'commission_voucher' => $voucher['voucher_number'],
			])
			->update( 'commission_marketing_agent', $commissionData );
	}

	public function revertTxnByRecruitmentAgent( $voucher )
	{
		$commissionData = [
			'commission_status'  => 0,
			'commission_voucher' => null,
		];

		$this->db->flush_cache();
		$this->db->where([
				'commission_voucher' => $voucher['voucher_number'],
			])
			->update( 'commission_recruitment_agent', $commissionData );
	}

	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
