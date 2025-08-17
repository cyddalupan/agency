<?php //-->
use \Application\Message as Message;

/*
 * This file is part a custom application package.
 * (c) 2014 Clemente Quiè´–ones Jr. <clemquinones@gmail.com>
 */

/**
 * Core Knowledge of all pages
 *
 * @author     Clemente Quiè´–ones Jr. <clemquinones@gmail.com>
 * @version    1.0.0
 */
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class m_userlog extends MY_Model {
 	/* Constants
	-------------------------------*/
    const RESERVED_DAYS_EXPIRATION = 7; //7-days, update also on the MySQL trigger
    
	/* Public Properties
	-------------------------------*/    
	public function allByApplicant( $country = null, $from = null, $to = null )
	{
		$this->db->flush_cache();
		$this->db->from( 'applicant_log' )
			->join( 'applicant', 'applicant_id = log_applicant', 'inner' )
			->join( 'user', 'user_id = log_createdby', 'inner' )
			->join( 'country', 'country_id = log_country', 'inner' );


		if ( $from && $to ) {
			$this->db->where( "DATE(log_created) BETWEEN '".date( 'Y-m-d', strtotime( $from ) )."' AND '".date( 'Y-m-d', strtotime( $to ) )."'", null, false );
		}

		if ( $country ) {
			$this->db->where([
				'log_country' => $country,
			]);
		}

		$this->db->order_by( 'applicant_id', 'DESC' );
		$this->db->order_by( 'applicant_date_applied', 'DESC' );
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$logs = $this->db->get()->result_array();

		$applicants = [];

		foreach ( $logs as $log ) {
			if ( ! isset( $applicants[ $log['log_applicant'] ] ) ) {
				$applicants[ $log['log_applicant'] ] = $log;	
			}
			
			$applicants[ $log['log_applicant'] ]['logs'][] = $log;
		}

		return $applicants;
	}

	/* Protected Properties
	-------------------------------*/
}
