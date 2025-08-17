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

class m_notification extends MY_Model {
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
	public function getNotifications()
	{
        $notifications = [
        	'expired-total' => 0,
        	'expired'       => [
        		'reservation' => 0,
        		'medical'     => 0,
        		'visa'        => 0,
        		'passport'    => 0,
        	],        	
        ];

        //Expired : Applicants Reservation
        $this->load->model( 'm_applicant' );
        
        $this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            ->join( 'employer_reservation', 'reservation_applicant = applicant_id' )
            ->where([
                'applicant_status'          => $this->m_applicant->status['Reserved'],
                'reservation_expiration <=' => date( 'Y-m-d', time() ),
            ]);
      $this->db->query('SET SQL_BIG_SELECTS=1');
		$notifications['expired']['reservation'] = $this->db->count_all_results();

		//Expired : Medical
		$this->db->flush_cache();
        $this->db->from( 'applicant_view' )
        	->where([
                'certificate_medical_clinic !='     => '',
        		'certificate_medical_expiration <=' => date( 'Y-m-d', time() + 60*60*24*14 ),
        		'certificate_medical_expiration >'  => DATE_EMPTY,
        	]);
    		$notinclude = array(1,7,9);
            $this->db->where_not_in('applicant_status',$notinclude);

        $notifications['expired']['medical'] = $this->db->count_all_results();

        //Expired : Visa
        $this->db->flush_cache();
         $this->db->from( 'applicant_view' )
            ->where([
                'requirement_visa'               => 1,
                'requirement_visa_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30 ),
                'requirement_visa_expiration >'  => DATE_EMPTY,
            ]);

    		$notinclude = array(1,7,9);
            $this->db->where_not_in('applicant_status',$notinclude);

        $notifications['expired']['visa'] = $this->db->count_all_results();

        //Expired : Passport
        $this->db->flush_cache();
     $this->db->flush_cache();
        $this->db->from( 'applicant_view' )
            // ->where_in( 'applicant_status', [
            //     $this->m_applicant->status['Selected'],
            //     $this->m_applicant->status['Reserved'],
            //     $this->m_applicant->status['For Deployment'],
            // ])
            ->where([
                'passport_number !='     => '',
                'passport_expiration <=' => date( 'Y-m-d', time() + 60*60*24*30*8 ),
                'passport_expiration >'  => DATE_EMPTY,
            ]);

    		$notinclude = array(1,7,9);
            $this->db->where_not_in('applicant_status',$notinclude);
        $notifications['expired']['passport'] = $this->db->count_all_results();


        if ( $notifications['expired']['reservation'] > 0 ) {
        	$notifications['expired-total'] ++;
        }

        if ( $notifications['expired']['medical'] > 0 ) {
        	$notifications['expired-total'] ++;
        }

        if ( $notifications['expired']['visa'] > 0 ) {
            $notifications['expired-total'] ++;
        }

        if ( $notifications['expired']['passport'] > 0 ) {
            $notifications['expired-total'] ++;
        }

        return $notifications;
	}
	/* Protected Methods
	-------------------------------*/	
	/* Private Methods
	-------------------------------*/
}
