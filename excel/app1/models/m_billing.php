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

class m_billing extends MY_Model {
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
	public function getBillById( $billId )
	{
		$this->db->flush_cache();
		$this->db->from( 'bill' )
			->where([
				'bill_id' => $billId,
			]);

		$bill = $this->db->get()->row_array();

		return $bill;
	}

	public function getBillByWorkerId( $applicantId )
	{
		$this->db->flush_cache();
		$this->db->from( 'bill' )
			->where([
				'bill_applicant' => $applicantId,
			]);

		$bill = $this->db->get()->row_array();

		return $bill;
	}

	public function getEmployerBillDetails( $billId, $onlyUnpaid = true )
	{
		$this->db->flush_cache();
		$this->db->from( 'bill_detail' )
			->join( 'fee', 'fee_id = detail_fee', 'inner' )
			->where([
				'detail_bill' => $billId,
			]);

		if ( $onlyUnpaid ) {
			$this->db->where( 'detail_employer_cost > detail_employer_deposit ', null, false );
		}
			
		$this->db->order_by( 'fee_id', 'ASC' );

		$details = $this->db->get()->result_array();

		return $this->indexArray( $details, 'fee_id' );
	}

	public function getWorkerBillDetails( $billId, $onlyUnpaid = true )
	{
		$this->db->flush_cache();
		$this->db->from( 'bill_detail' )
			->join( 'fee', 'fee_id = detail_fee', 'inner' )
			->where([
				'detail_bill' => $billId,
			]);

		if ( $onlyUnpaid ) {
			$this->db->where( 'detail_applicant_cost > detail_applicant_deposit ', null, false );
		}

		$this->db->order_by( 'fee_id', 'ASC' );

		$details = $this->db->get()->result_array();

		return $this->indexArray( $details, 'fee_id' );
	}

	public function hasBilling( $applicantId )
	{
		$this->db->flush_cache();
		$this->db->from( 'bill' )
			->where([
				'bill_applicant' => $applicantId,
			]);
		
		$existing = $this->db->count_all_results() > 0;

		return $existing;
	}

	public function createBilling( $applicantId )
	{
		//Start Transaction
		$this->db->trans_begin();

		//Get applicant info
		$this->db->flush_cache();
		$this->db->from( 'applicant' )
			->where([
				'applicant_id' => $applicantId,
			]);
		
		$applicant = $this->db->get()->row_array();
		
		if ( empty( $applicant['applicant_employer'] ) || empty( $applicant['applicant_job'] ) ) {
			return false;
		}

		$employerId = $applicant['applicant_employer'];

		//Get job info
		$this->db->flush_cache();
		$this->db->from( 'job' )
			->where([
				'job_id' => $applicant['applicant_job'],
			]);

		$job   = $this->db->get()->row_array();
		
		if ( empty( $job ) ) {
			return false;
		}

		$jobId = $job['job_id'];

		//Get job fees, order by fee_id
		$this->db->flush_cache();
        $this->db->from( 'job_fee j' )
            ->join( 'fee f', 'f.fee_id = j.fee_fee' )
            ->where([
                'j.fee_job' => $jobId,
            ])
            ->order_by( 'f.fee_id', 'ASC' );
        
        $fees = $this->db->get()->result_array();

        $this->indexArray( $fees, 'fee_id' );

        $totalRevenue = $employerCost = $applicantCost = 0;

        foreach ( $fees as $fee ) {
        	$totalRevenue  += (float) $fee['fee_amount'];
        	$employerCost  += (float) $fee['fee_employer_cost'];
        	$applicantCost += (float) $fee['fee_applicant_cost'];
        }

        $billData = [
			'bill_employer'          => $employerId,
			'bill_applicant'         => $applicantId,
			'bill_amount'            => $totalRevenue,
			'bill_employer_cost'     => $employerCost,
			'bill_applicant_cost'    => $applicantCost,
			'bill_employer_deposit'  => 0,
			'bill_applicant_deposit' => 0,
			'bill_status'            => 0,
			'bill_remarks'           => 'Applicant selected job offer',
			'bill_createdby'         => $_SESSION['admin']['user']['user_id'],
			'bill_updatedby'         => $_SESSION['admin']['user']['user_id'],
			'bill_created'           => date( 'Y-m-d H:i:s', time() ),
			'bill_updated'           => date( 'Y-m-d H:i:s', time() ),
        ];

        $this->db->flush_cache();
        $this->db->insert( 'bill', $billData );
        $billId = $this->db->insert_id();

        $bill = $this->getBillById( $billId );

        $billDetailData = [];

        foreach ( $fees as $feeId => $fee ) {
        	$billDetailData[] = [
				'detail_bill'               => $billId,
				'detail_fee'                => $feeId,
				'detail_amount'             => (float) $fee['fee_amount'],
				'detail_employer'           => $employerId,
				'detail_applicant'          => $applicantId,
				'detail_employer_cost'      => (float) $fee['fee_employer_cost'],
				'detail_applicant_cost'     => (float) $fee['fee_applicant_cost'],
				'detail_employer_deposit'   => 0,
				'detail_applicant_deposit'  => 0,
				'detail_employer_balance'   => (float) $fee['fee_employer_cost'], //optional
				'detail_applicant_balance'  => (float) $fee['fee_applicant_cost'], //optional
				'detail_remarks'            => '',
				'detail_createdby'          => $_SESSION['admin']['user']['user_id'],
				'detail_updatedby'          => $_SESSION['admin']['user']['user_id'],
				'detail_created'            => date( 'Y-m-d H:i:s', time() ),
				'detail_updated'            => date( 'Y-m-d H:i:s', time() ),
			];
        }

        $this->db->flush_cache();
        $this->db->insert_batch( 'bill_detail', $billDetailData );

        if ( ! $this->db->trans_status() ) {
			$this->db->trans_rollback();
			return false;
		} 
		
		//Commit transaction
		$this->db->trans_commit();	
		return true;
	}
 
	public function getWorkers( $onlyUnpaid = true )
	{
		//order by created desc
		$this->db->flush_cache();

		$this->db->from( 'bill' )
			->join( 'applicant_view', 'applicant_id = bill_applicant', 'inner' );

		if ( $onlyUnpaid ) {
			$this->db->where([
				'bill_status' => 0,
			]);
		}
		
		$this->db->order_by( 'bill_status', 'ASC')
			->order_by( 'bill_created', 'DESC' );

		$workers = $this->db->get()->result_array();

		return $this->indexArray( $workers, 'applicant_id' );
	}

	public function getEmployers( $from = null, $to = null  )
	{
		//order by created desc
		$this->db->flush_cache();

		$this->db->from( 'billing_employer_view' );

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`bill_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}
		
		$this->db->order_by( 'bill_status', 'ASC')
			->order_by( 'bill_created', 'DESC' );

		$employers = $this->db->get()->result_array();

		return $this->indexArray( $employers, 'employer_id' );
	}

	public function getEmployersUnpaid( $withWorkers = false, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->select( '`employer`.*' )
			->from( 'bill' )
			->join( 'employer', 'bill_employer = bill_employer', 'inner' )
			->where([
				'bill_status' => 0,
			]);

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`bill_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}

		$this->db->group_by( 'bill_employer' )
			->order_by( 'employer_name', 'ASC' );

		$employers = $this->db->get()->result_array();
		$employers = $this->indexArray( $employers, 'employer_id' );

		foreach ( $employers as $employerId => $employer ) {
			$this->db->flush_cache();
			$this->db->select( 'SUM( `bill_employer_cost` - `bill_employer_deposit` ) AS `balance`' )
				->from( 'bill' )
				->where([
					'bill_employer' => $employerId,
					'bill_status'   => 0,
				]);

			$bill = $this->db->get()->row_array();

			$employers[ $employerId ]['balance'] = floatval( $bill['balance'] );

			if ( $withWorkers ) {
				$this->db->flush_cache();
				$this->db->select( 'applicant.*, bill.*' )
					->from( 'applicant' )
					->join( 'bill', 'bill_applicant = applicant_id', 'inner' )
					->where([
						'bill_status'   => 0,
						'bill_employer' => $employerId,
					])
					->order_by( 'applicant_first', 'ASC')
					->order_by( 'applicant_last', 'ASC');

				$workers = $this->db->get()->result_array();
				$workers = $this->indexArray( $workers, 'applicant_id' );

				$employers[ $employerId ]['workers'] = $workers;
			}
		}

		return $employers;  
	}

	public function getWorker( $applicantId )
	{
		//order by created desc
		$this->db->flush_cache();

		$this->db->from( 'applicant' )
			->join( 'job', 'job_id = applicant_job', 'inner' )
			->where([
				'applicant_id' => $applicantId,
			]);

		$applicant = $this->db->get()->row_array();

		return $applicant;
	}

	public function getEmployerWorkers( $employerId, $onlyUnpaid = true, $from = null, $to = null )
	{
		$this->db->flush_cache();

		$this->db->from( 'bill' )
			->join( 'applicant', 'applicant_id = bill_applicant', 'inner' )
			->join( 'job', 'job_id = applicant_job', 'inner' )
			->where([
				'bill_employer' => $employerId,
			]);

		if ( $onlyUnpaid ) {
			$this->db->where([
				'bill_status'   => 0,
			]);
		}
		
		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`bill_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}

		$this->db->order_by( 'bill_status', 'ASC' )
			->order_by( 'bill_created', 'DESC' );

		$workers = $this->db->get()->result_array();

		return $this->indexArray( $workers, 'applicant_id' );
	}

	public function getWorkerBreakdown( $applicantId, $onlyUnpaid = true )
	{
		$this->db->flush_cache();
		$this->db->from( 'bill' )
			->where([
				'bill_applicant' => $applicantId,
			]);

		if ( $onlyUnpaid ) {
			$this->db->where([
				'bill_status'   => 0,
			]);
		}

		$this->db->order_by( 'bill_status', 'ASC' )
			->order_by( 'bill_created', 'DESC' );

		$bill = $this->db->get()->row_array();

		if ( empty( $bill ) ) {
			return [];
		}

		$this->db->flush_cache();
		$this->db->from( 'bill_detail' )
			->join( 'fee', 'fee_id = detail_fee', 'inner' )
			->where([
				'detail_bill'            => $bill['bill_id'],
				'detail_employer_cost >' => 0
			])
			->order_by( 'fee_id', 'ASC' );

		$billDetails = $this->db->get()->result_array();

		return $this->indexArray( $billDetails, 'fee_id' );
	}

	public function makePaymentEmployer( $billId )
	{
		$payment = $_POST['payment'];

		//Start Transaction
		$this->db->trans_begin();

		$bill    = $this->getBillById( $billId );
		$details = $this->getEmployerBillDetails( $billId );

		$ORNumber = isset( $_POST['or'] ) ? $_POST['or'] : '';

		if ( ! isset( $_POST['or'] ) ) {
			$this->load->model( 'm_or' );
			//$ORNumber    = ( new m_or )->generate( (int) $billId );	
			$ORNumber    = ( new m_or )->generate();	
		}		
		
		$fees    = $payment['fee'];

		$totalPaid      = 0;
		$billData       =
		$detailData     =
		$paymentData    = 
		$paymentDetails = 
		$ORData         = [];

		foreach ( $fees as $feeId => $amount ) {
			
			if ( ! $amount ) {
				continue;
			}

			$totalPaid += (float) $amount;			 

			//Update bill fee
			$feeDeposit = $details[$feeId]['detail_employer_deposit'] + $amount;

			$detailData = [
				'detail_employer_deposit' => $feeDeposit,
				'detail_employer_balance' => $details[$feeId]['detail_employer_cost'] - $feeDeposit,
				'detail_remarks'          => 'Deposit employer (per fee) P'.number_format( $amount, 2 ),
				'detail_updatedby'        => $_SESSION['admin']['user']['user_id'],
				'detail_updated'          => date( 'Y-m-d H:i:s', time() ),
			];

			$this->db->flush_cache();
			$this->db->where([
					'detail_bill' => $billId,
					'detail_fee'  => $feeId,
				])->update( 'bill_detail', $detailData );

			//Add payment detail
			$paymentDetails[] = [
				'detail_payment'   => null, //will update later
				'detail_bill'      => $billId,
				'detail_or'        => $ORNumber,
				'detail_employer'  => $bill['bill_employer'],
				'detail_applicant' => $bill['bill_applicant'],
				'detail_fee'       => $feeId,
				'detail_amount'    => $amount,
				'detail_balance'   => $details[$feeId]['detail_employer_cost'] - $feeDeposit,
				'detail_createdby' => $_SESSION['admin']['user']['user_id'],
				'detail_created'   => date( 'Y-m-d H:i:s', time() ),
			];
		}

		if ( ! $totalPaid) {
			return false;
		}

		//Update bill
		$billData = [
			'bill_employer_deposit' => $bill['bill_employer_deposit'] + $totalPaid,
			'bill_remarks'          => 'Deposit employer a total of P'.number_format( $totalPaid, 2 ).'.',
			'bill_updatedby'        => $_SESSION['admin']['user']['user_id'],
			'bill_updated'          => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->where([
				'bill_id' => $billId,
			])->update( 'bill', $billData );

		//Insert payment
		$paymentData = [
			'payment_bill'      => $billId,
			'payment_or'        => $ORNumber,
			'payment_employer'  => $bill['bill_employer'],
			'payment_amount'    => $totalPaid,
			'payment_remarks'   => 'Deposit employer a total of P'.number_format( $totalPaid, 2 ).'.',
			'payment_createdby' => $_SESSION['admin']['user']['user_id'],
			'payment_updatedby' => $_SESSION['admin']['user']['user_id'],
			'payment_created'   => date( 'Y-m-d H:i:s', time() ),
			'payment_updated'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->insert( 'bill_payment_employer', $paymentData );

		$paymentId = $this->db->insert_id();

		//Insert payment details
		foreach ( $paymentDetails as $key => $detail ) {
			$paymentDetails[$key]['detail_payment'] = $paymentId;
		}

		$this->db->flush_cache();
		$this->db->insert_batch( 'payment_employer_detail', $paymentDetails );

		//Insert OR
		$ORData = [
			'or_number'    => $ORNumber,
			'or_amount'    => (float) $totalPaid,
			'or_employer'  => $bill['bill_employer'],
			'or_applicant' => $bill['bill_applicant'],
			'or_remarks'   => 'Deposit employer a total of P'.number_format( $totalPaid, 2 ).'.',
			'or_date'      => date( 'Y-m-d', time() ),
			'or_createdby' => $_SESSION['admin']['user']['user_id'],
			'or_created'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->insert( 'or', $ORData );
		$ORId = $this->db->insert_id();

		//Get fresh copy of payment
		$this->db->flush_cache();
		$payment = $this->db
			->get_where( 'bill_payment_employer', [
				'payment_id' => $paymentId
			])->row_array();

		//Rollback if transaction fails
		if ( ! $this->db->trans_status() ) {
			$this->db->trans_rollback();
			return false;
		}
		
		//Commit transaction
		$this->db->trans_commit();	

		return $payment;
	}

	public function paidAllEmployer( $employerId, $from = null, $to = null )
	{
		$datePaid = date( 'Y-m-d H:i:s' );

		//Start Transaction
		$this->db->trans_begin();

		//Generate OR#
		$this->load->model( 'm_or' );
		$ORId     = (new m_or )->generate();

		$this->db->flush_cache();
		$this->db->from('bill_detail')
			->join('bill', 'bill_id = detail_bill', 'inner')
			->where([
				'bill_employer'   => $employerId,
				'detail_employer' => $employerId,
				//'bill_status'     => 0,
			]);

		if ( ! is_null( $from ) && ! is_null( $to ) ) {
			$this->db->where( 'DATE(`bill_created`) BETWEEN \''.fdate( 'Y-m-d', $from ).'\' AND \''.fdate( 'Y-m-d', $to ).'\'' , null, false );
		}

		$this->db->order_by('bill_id', 'ASC')
			->order_by('detail_id', 'ASC');

		$details = $this->db->get()->result_array();
		$details = $this->indexArray( $details, 'detail_id');

		$totalPaid      = 0;
		$paymentDetails = [];
		$billIds        = [];

		$billsDeposit = [];

		$paidAllApplicantIds = [];

		foreach ( $details as $detailId => $detail ) {
			
			if ( ! in_array( $detail['bill_id'], $billIds ) ) {
				$billIds[] = $detail['bill_id'];	
			}

			$rowBalance = ( $detail['detail_employer_cost'] - $detail['detail_employer_deposit'] );

			if ( ! $rowBalance ) {
				continue;
			}

			$totalPaid += (float) $rowBalance;			 

			//Update bill fee
			$feeDeposit = $detail['detail_employer_deposit'] + $rowBalance;

			$billsDeposit[ $detail['bill_id'] ] = $rowBalance + ( isset( $billsDeposit[ $detail['bill_id'] ] ) ? $billsDeposit[ $detail['bill_id'] ] : 0 );

			$detailData = [
				'detail_employer_deposit' => $feeDeposit,
				'detail_employer_balance' => 0,
				'detail_remarks'          => 'Deposit employer (paid all) P'.number_format( $rowBalance, 2 ),
				'detail_updatedby'        => $_SESSION['admin']['user']['user_id'],
				'detail_updated'          => date( 'Y-m-d H:i:s', time() ),
			];

			$this->db->flush_cache();
			$this->db->where([
					'detail_id' => $detailId,
				])->update( 'bill_detail', $detailData );

			//Add payment detail
			$paymentDetails[] = [
				'detail_payment'   => null, //will update later
				'detail_bill'      => $detail['bill_id'],
				'detail_or'        => $ORId,
				'detail_employer'  => $employerId,
				'detail_applicant' => $detail['bill_applicant'],
				'detail_fee'       => $detail['detail_fee'],
				'detail_amount'    => $rowBalance,
				'detail_balance'   => 0,
				'detail_createdby' => $_SESSION['admin']['user']['user_id'],
				'detail_created'   => date( 'Y-m-d H:i:s', time() ),
			];

			$paidAllApplicantIds[ $detail['bill_id'] ] = [
				'paidall_or'         => $ORId,
				'paidall_bill'       => $detail['bill_id'],
				'paidall_employer'   => $employerId,
				'paidall_applicant'  => $detail['bill_applicant'],
				//'paidall_amount'     => $rowBalance,
				'paidall_paid'		 => $datePaid,
			];
		}

		foreach ( $billsDeposit as $billId => $deposit ) {
			$this->db->flush_cache();
			$this->db->where([
					'bill_id' => $billId,
				])
				->set( 'bill_employer_deposit', 'bill_employer_deposit + ' .(float) $deposit, false )
				->update( 'bill' );
		}

		//Mark as paid the bill
		$this->db->flush_cache();
		$this->db->where_in('bill_id', $billIds)
			->update( 'bill', [
				'bill_status' => 1,
			]);

		//Insert payment
		$paymentData = [
			'payment_bill'      => null,
			'payment_or'        => $ORId,
			'payment_employer'  => $employerId,
			'payment_amount'    => $totalPaid,
			'payment_remarks'   => 'Paid employer a total of P'.number_format( $totalPaid, 2 ).( is_null($from) || is_null($to) ? '' : ', period of '.$from.' to '.$to).'.',
			'payment_createdby' => $_SESSION['admin']['user']['user_id'],
			'payment_updatedby' => $_SESSION['admin']['user']['user_id'],
			'payment_created'   => date( 'Y-m-d H:i:s', time() ),
			'payment_updated'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->insert( 'bill_payment_employer', $paymentData );

		$paymentId = $this->db->insert_id();

		//Insert payment details
		foreach ( $paymentDetails as $key => $detail ) {
			$paymentDetails[$key]['detail_payment'] = $paymentId;
		}

		if ( count( $paymentDetails ) > 0 ) {
			$this->db->flush_cache();
			$this->db->insert_batch( 'payment_employer_detail', $paymentDetails );
		}
		
		if ( count( $paidAllApplicantIds ) > 0 ) {
			$this->db->flush_cache();
			$this->db->insert_batch( 'paidall_employer_applicants', $paidAllApplicantIds );
		}

		//Insert OR
		$ORData = [
			'or_number'    => $ORId,
			'or_amount'    => (float) $totalPaid,
			'or_employer'  => $employerId,
			'or_applicant' => null,
			'or_remarks'   => 'Deposit employer a total of P'.number_format( $totalPaid, 2 ).'.',
			'or_date'      => date( 'Y-m-d', time() ),
			'or_createdby' => $_SESSION['admin']['user']['user_id'],
			'or_created'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->insert( 'or', $ORData );
		$ORId = $this->db->insert_id();

		//Get fresh copy of payment
		$this->db->flush_cache();
		$this->db->from('bill_payment_employer')
			->join( 'or', 'or_number = payment_or', 'inner' )
			->join( 'employer', 'employer_id = payment_employer', 'inner' )
			->where([
				'payment_id' => $paymentId
			]);

		$payment = $this->db->get()->row_array();

		//Rollback if transaction fails
		if ( ! $this->db->trans_status() ) {
			$this->db->trans_rollback();
			return false;
		}
		
		//Commit transaction
		$this->db->trans_commit();	

		return $payment;
	}

	public function makePaymentWorker( $billId )
	{
		$payment = $_POST['payment'];

		//Start Transaction
		$this->db->trans_begin();

		$bill    = $this->getBillById( $billId );
		$details = $this->getWorkerBillDetails( $billId );
	
		$ORNumber = isset( $_POST['or'] ) ? $_POST['or'] : '';

		if ( ! isset( $_POST['or'] ) ) {
			$this->load->model( 'm_or' );
			//$ORNumber    = ( new m_or )->generate( (int) $billId );
			$ORNumber    = ( new m_or )->generate();
		}
		
		$fees    = $payment['fee'];

		$totalPaid      = 0;
		$billData       =
		$detailData     =
		$paymentData    = 
		$paymentDetails = [];

		foreach ( $fees as $feeId => $amount ) {

			if ( ! $amount ) {
				continue;
			}

			$totalPaid += (float) $amount;			 

			//Update bill fee
			$feeDeposit = $details[$feeId]['detail_applicant_deposit'] + $amount;
			$detailData = [
				'detail_applicant_deposit' => $feeDeposit,
				'detail_applicant_balance' => $details[$feeId]['detail_applicant_cost'] - $feeDeposit,
				'detail_remarks'           => 'Deposit worker P'.number_format( $amount, 2 ),
				'detail_updatedby'         => $_SESSION['admin']['user']['user_id'],
				'detail_updated'           => date( 'Y-m-d H:i:s', time() ),
			];

			$this->db->flush_cache();
			$this->db->where([
					'detail_bill' => $billId,
					'detail_fee'  => $feeId,
				])->update( 'bill_detail', $detailData );

			//Add payment detail
			$paymentDetails[] = [
				'detail_payment'   => null, //will update later
				'detail_bill'      => $billId,
				'detail_or'        => $ORNumber,
				'detail_applicant' => $bill['bill_applicant'],
				'detail_fee'       => $feeId,
				'detail_amount'    => $amount,
				'detail_balance'   => $details[$feeId]['detail_applicant_cost'] - $feeDeposit,
				'detail_createdby' => $_SESSION['admin']['user']['user_id'],
				'detail_created'   => date( 'Y-m-d H:i:s', time() ),
			];
		}

		if ( ! $totalPaid) {
			return false;
		}

		//Update bill
		$billData = [
			'bill_applicant_deposit' => $bill['bill_applicant_deposit'] + $totalPaid,
			'bill_remarks'          => 'Deposit worker a total of P'.number_format( $totalPaid, 2 ).'.',
			'bill_updatedby'        => $_SESSION['admin']['user']['user_id'],
			'bill_updated'          => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->where([
				'bill_id' => $billId,
			])->update( 'bill', $billData );

		//Insert payment
		$paymentData = [
			'payment_bill'      => $billId,
			'payment_or'        => $ORNumber,
			'payment_applicant' => $bill['bill_applicant'],
			'payment_amount'    => $totalPaid,
			'payment_remarks'   => 'Deposit worker a total of P'.number_format( $totalPaid, 2 ).'.',
			'payment_createdby' => $_SESSION['admin']['user']['user_id'],
			'payment_updatedby' => $_SESSION['admin']['user']['user_id'],
			'payment_created'   => date( 'Y-m-d H:i:s', time() ),
			'payment_updated'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->insert( 'bill_payment_applicant', $paymentData );

		$paymentId = $this->db->insert_id();

		//Insert payment details
		foreach ( $paymentDetails as $key => $detail ) {
			$paymentDetails[$key]['detail_payment'] = $paymentId;
		}

		$this->db->flush_cache();
		$this->db->insert_batch( 'payment_worker_detail', $paymentDetails );

		//Insert OR
		$ORData = [
			'or_number'    => $ORNumber,
			'or_amount'    => (float) $totalPaid,
			'or_employer'  => $bill['bill_employer'],
			'or_applicant' => $bill['bill_applicant'],
			'or_remarks'   => 'Deposit worker a total of P'.number_format( $totalPaid, 2 ).'.',
			'or_date'      => date( 'Y-m-d', time() ),
			'or_createdby' => $_SESSION['admin']['user']['user_id'],
			'or_created'   => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->insert( 'or', $ORData );
		$ORId = $this->db->insert_id();

		//Get fresh copy of payment
		$this->db->flush_cache();
		$payment = $this->db
			->get_where( 'bill_payment_applicant', [
				'payment_id' => $paymentId
			])->row_array();

		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $payment) {
			$this->db->trans_rollback();
			return false;
		}

		//Commit transaction
		$this->db->trans_commit();

		return $payment;
	}

	public function updatePlacementFee( $applicantId )
	{
		$amount = (float) $_POST['placement-fee']['amount'];

		//Start transaction
		$this->db->trans_begin();

		$bill = $this->getBillByWorkerId( $applicantId );

		//Get placement fee
		$this->db->flush_cache();
		$this->db->from('bill_detail')
			->where([
				'detail_bill' => $bill['bill_id'],
				'detail_fee'  => 1, //Placement Fee
			]);

		$detail = $this->db->get()->row_array();

		//Update placement fee
		$detailData = [
			'detail_applicant_cost'    => $amount,
			'detail_applicant_balance' => $amount - $detail['detail_applicant_deposit'],
			'detail_remarks'           => 'Placement fee updated from P'.number_format($detail['detail_applicant_cost']).' to P'.number_format($amount),
		];

		$this->db->flush_cache();
		$updated = 
		$this->db->set( 'detail_amount', 'detail_employer_cost + '.$amount, false )
			->where([
				'detail_id' => $detail['detail_id'],
			])->update( 'bill_detail', $detailData);

		$this->db->flush_cache();
		$this->db->from('bill_detail')
			->where([
				'detail_bill' => $bill['bill_id'],
			]);

		$fees = $this->db->get()->result_array();

        $totalRevenue = $applicantCost = 0;

        foreach ( $fees as $fee ) {
        	$totalRevenue  += (float) $fee['detail_amount'];
        	$applicantCost += (float) $fee['detail_applicant_cost'];
        }

		$billData = [
			'bill_amount'            => $totalRevenue,
			'bill_applicant_cost'    => $applicantCost,
			'bill_updatedby'         => $_SESSION['admin']['user']['user_id'],
			'bill_updated'           => date( 'Y-m-d H:i:s', time() ),
        ];

        if ( $bill['bill_status'] && $amount - $detail['detail_applicant_deposit'] > 0 ) {
    		//Reset bill status to active
    		$billData['bill_status'] = 0;
    	}

    	$this->db->flush_cache();
    	$this->db->where([
    			'bill_id' => $bill['bill_id'],
    		])->update( 'bill', $billData );

    	$this->db->flush_cache();
    	$bill = 
    	$this->db->get_where('bill', [
    			'bill_id' => $bill['bill_id'],
    		])
    		->result_array();

		if ( ! $this->db->trans_status() ) {
			$this->db->trans_rollback();
			return false;
		}

		$this->db->trans_commit();

		return $updated;
	}
	
	/* Protected Methods
	-------------------------------*/
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
