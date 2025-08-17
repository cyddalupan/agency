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

class m_fee extends MY_Model {
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
	/* Protected Methods
	-------------------------------*/
	public function getFeeById( $feeId )
	{	
		//Get Job Offer Info
		$this->db->flush_cache();
		$this->db->from('fee')
			->where([
				'fee_id'	=> $feeId,
			]);
			
		$fee = $this->db->get()->row_array();
		
		return $fee;
	}
	
	public function getFees( $options = [], $limit = 0, $offset = 0, $sort = ['fee_id', 'ASC'] )
	{
		//Get Get Job Offer Info
		$this->db->flush_cache();
		$this->db->from('fee');
		
		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
			
		$fees = $this->db->get()->result_array();
        
        return $this->indexArray( $fees, 'fee_id' );
	}
	
	public function getFeesCount( $options = [] )
	{
		//Get Applicant Info
		$this->db->flush_cache();
		$this->db->from('fee');
		
		$this->setDBQueryOptions( $options );
		
		$fees = $this->db->count_all_results(); 
		
		return $fees;
	}

	public function getDollarExchange()
	{
		$dollar = 0;
		$this->db->flush_cache();
		$this->db->from( 'meta' )
			->where([
				'meta_type'   => 'fee',
				'meta_key'    => 'dollar-exchange',
			]);

		$meta = $this->db->get()->row_array();

		if( $meta ) {
			$dollar = (float) $meta['meta_value'];
		}

		return $dollar;
	}
    
	/* Protected Methods
	-------------------------------*/
	/* Private Methods
	-------------------------------*/
}
