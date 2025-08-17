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

class m_or extends MY_Model {
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
	public function getORById( $ORId )
	{
		$this->db->flush_cache();
		$this->db->from( 'or' )
			->join( 'user', 'user_id = or_approvedby', 'left' )
			->join( 'employer', 'employer_id = or_employer', 'inner' )
			->join( 'applicant', 'applicant_id = or_applicant', 'left' )
			->where([
				'or_id' => $ORId,
			]);

		$or = $this->db->get()->row_array();

		$or['applicants'] = [];

		if ( is_null( $or['or_applicant'] ) ) {
			$this->db->flush_cache();
			$this->db->from( 'applicant' )
				->join('paidall_employer_applicants', 'paidall_applicant = applicant_id', 'inner')
				->where([
					'paidall_or' => $or['or_number'],
				]);

			$applicants = $this->db->get()->result_array();

			$or['applicants'] = $applicants;
		}
 
		return $or;
	}

	public function getOR( $ORnumber )
	{
		$this->db->flush_cache();
		$this->db->from( 'or' )
			->join( 'user', 'user_id = or_approvedby', 'left' )
			->where([
				'or_number' => $ORnumber,
			]);

		$or = $this->db->get()->row_array();

		return $or;
	}

	public function getORs()
	{
		$this->db->flush_cache();
		$this->db->select('*, (SELECT COUNT(*) FROM `paidall_employer_applicants` WHERE `paidall_or` = `or_number`) AS `applicants`')
			->from( 'or' )
			->join( 'user', 'user_id = or_approvedby', 'left' )
			->join( 'employer', 'employer_id = or_employer', 'left' )
			->join( 'applicant', 'applicant_id = or_applicant', 'left' )
			->order_by( 'or_created', 'DESC' );

		$ORs = $this->db->get()->result_array();

		return $this->indexArray( $ORs, 'or_number' );
	}

	public function searchORs()
	{
		$orNumber = $_GET['search']['or'];

		$this->db->flush_cache();
		$this->db->select('*, (SELECT COUNT(*) FROM `paidall_employer_applicants` WHERE `paidall_or` = `or_number`) AS `applicants`')
			->from( 'or' )
			->join( 'user', 'user_id = or_approvedby', 'left' )
			->join( 'employer', 'employer_id = or_employer', 'left' )
			->join( 'applicant', 'applicant_id = or_applicant', 'left' )
			->where( 'or_number LIKE \'%'.addslashes( $orNumber ).'%\'' , null, false )
			->order_by( 'or_created', 'DESC' );

		$ORs = $this->db->get()->result_array();

		return $this->indexArray( $ORs, 'or_number' );
	}

	public function generate( $prepend = null)
	{
		$this->db->flush_cache();
		$result = $this->db->query("
				SELECT `AUTO_INCREMENT` 
				FROM `information_schema`.`tables` 
				WHERE `TABLE_SCHEMA`='"
				. $this->db->database."' 
				AND `TABLE_NAME`='or' 
				LIMIT 1
			")->row_array();

		$ORId = $result['AUTO_INCREMENT'];

		return $prepend.str_pad( $ORId, 7, '0', STR_PAD_LEFT );
	}

	public function updateOR( $ORId )
	{
		$post = $_POST['or'];

		$or = $this->getORById( $ORId );

		$orData = [
			'or_number'  => str_pad( $post['number'], 7, '0', STR_PAD_LEFT ),
			'or_date'    => date('Y-m-d', strtotime( $post['date'] ) ),
			'or_remarks' => $post['remarks'],
		];

		$this->db->flush_cache();
		$this->db->where([
				'or_id' => $ORId
			])->update( 'or', $orData );

		$or = $this->getORById( $ORId );
		
		return $or;
	}

	public function revertOR( $ORId )
	{
		$this->db->flush_cache();
		$this->db->from( 'or' )
			->where([
				'or_id' => $ORId,
			]);

		$or = $this->db->get()->row_array();

		$this->revertTxnByEmployer( $or );
		$this->revertTxnByWorker( $or );

		$this->db->flush_cache();
		$this->db->where([
				'or_id' => $ORId,
			])
			->delete( 'or' );

		return true;

		// $this->db->trans_begin();

		// $this->db->flush_cache();
		// $this->db->from( 'or' )
		// 	->where([
		// 		'or_id' => $ORId,
		// 	]);

		// $or = $this->db->get()->row_array();

		// $txnBy =  $this->checkTxnFrom( $or['or_number'] );

		// if ( $txnBy == 'employer' ) {
		// 	$this->revertTxnByEmployer( $or );
		// } elseif ( $txnBy == 'worker' ) {
		// 	 $this->revertTxnByWorker( $or );
		// } else {
		// 	return false;
		// }

		// $this->db->flush_cache();
		// $this->db->where([
		// 		'or_id' => $ORId,
		// 	])
		// 	->delete( 'or' );
		// if ( ! $this->db->trans_status() ) {
		// 	$this->db->trans_rollback();
		// 	return false;
		// }

		// $this->db->trans_commit();

		// return true;
	}

	public function checkTxnFrom( $orNumber )
	{
		$this->db->flush_cache();
		$this->db->from( 'bill_payment_employer' )
			->where([
				'payment_or' => $orNumber,
			]);

		if ( $this->db->count_all_results() > 0 ) {
			return 'employer';
		}

		$this->db->flush_cache();
		$this->db->from( 'bill_payment_applicant' )
			->where([
				'payment_or' => $orNumber,
			]);

		if ( $this->db->count_all_results() > 0 ) {
			return 'worker';
		}
		return '';

	}

	public function revertTxnByEmployer( $or )
	{
		$this->db->flush_cache();
		$this->db->from( 'payment_employer_detail' )
			->where([
				'detail_or' => $or['or_number'],
			]);

		$details   = $this->db->get()->result_array();
		$detailsId = [];

		$billId  = 0;
		$deposit = 0;

		foreach ( $details as $detail ) {
			
			$this->db->flush_cache();
			$this->db->where([
					'detail_bill' => $detail['detail_bill'],
					'detail_fee'  => $detail['detail_fee']
				])
				->set('detail_employer_deposit', 'detail_employer_deposit - '.(float) $detail['detail_amount'], false)
				->set('detail_employer_balance', 'detail_employer_balance + '.(float) $detail['detail_amount'], false)
				->update( 'bill_detail' );

			$deposit += (float) $detail['detail_amount'];
			$billId = $detail['detail_bill'];
		}

		//Back bill status to zero, unpaid
		$this->db->flush_cache();
		$this->db->where([
				'bill_id' => $billId,
			])
			->set( 'bill_status', 0 )
			->set( 'bill_employer_deposit', 'bill_employer_deposit - '.$deposit , false )
			->update( 'bill' );

		$this->db->flush_cache();
		$this->db->where([
				'payment_or' => $or['or_number'],
			])
			->delete( 'bill_payment_employer' );

		$this->db->flush_cache();
		$this->db->where([
				'detail_or' => $or['or_number'],
			])
			->delete( 'payment_employer_detail' );
	}

	public function revertTxnByWorker( $or )
	{
		$this->db->flush_cache();
		$this->db->from( 'payment_worker_detail' )
			->where([
				'detail_or' => $or['or_number'],
			]);

		$details   = $this->db->get()->result_array();
		$detailsId = [];

		$billId  = 0;
		$deposit = 0;

		foreach ( $details as $detail ) {
			
			$this->db->flush_cache();
			$this->db->where([
					'detail_bill' => $detail['detail_bill'],
					'detail_fee'  => $detail['detail_fee']
				])
				->set('detail_applicant_deposit', 'detail_applicant_deposit - '.(float) $detail['detail_amount'], false)
				->set('detail_applicant_balance', 'detail_applicant_balance + '.(float) $detail['detail_amount'], false)
				->update( 'bill_detail' );

			$deposit += (float) $detail['detail_amount'];
			$billId = $detail['detail_bill'];
		}

		//Back bill status to zero, unpaid
		$this->db->flush_cache();
		$this->db->where([
				'bill_id' => $billId,
			])
			->set( 'bill_status', 0 )
			->set( 'bill_applicant_deposit', 'bill_applicant_deposit - '.$deposit , false )
			->update( 'bill' );

		$this->db->flush_cache();
		$this->db->where([
				'payment_or' => $or['or_number'],
			])
			->delete( 'bill_payment_applicant' );

		$this->db->flush_cache();
		$this->db->where([
				'detail_or' => $or['or_number'],
			])
			->delete( 'payment_worker_detail' );
	}

	/* Protected Methods
	-------------------------------*/
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
