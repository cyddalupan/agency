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

class m_marketing_agency extends MY_Model {
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
	public function getMarketingAgencies( $options = [], $limit = 0, $offset = 0, $sort = ['agency_name', 'ASC'] )
	{
		$this->db->flush_cache();
		$this->db->select( '*, 
			(SELECT COUNT(*) FROM `employer` WHERE `employer_source_agency` = `agency_id`) AS `employers`', false)
			->from('marketing_agency');

		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset)
			->setDBQueryOrders( $sort );

		$agencies = $this->db->get()->result_array();

        return $this->indexArray( $agencies, 'agency_id' );
	}

	public function getMarketingAgenciesCount( $options = [])
	{
		$this->db->flush_cache();
		$this->db->from('marketing_agency');

		$this->setDBQueryOptions( $options );

		$agencies = $this->db->count_all_results();

		return $agencies;
	}
	
	public function getMarketingAgencyById( $agencyId )
	{
		$this->db->flush_cache();
		$this->db->select( '*, 
			(SELECT COUNT(*) FROM `employer` WHERE `employer_source_agency` = `agency_id`) AS `employers`', false)
			->from('marketing_agency')
			->where([
				'agency_id'	=> $agencyId,
			]);

		$agency = $this->db->get()->row_array();

		return $agency;
	}
    
    public function getCommissions( $agencyId, $options = [], $limit = 0, $offset = 0, $sort = ['commission_created', 'ASC'] )
    {
        $this->db->flush_cache();
        $this->db->from( 'commission_employer' )
            ->where([
                'commission_agency' => $agencyId,
            ]);
        
        $this->setDBQueryOptions( $options )
            ->setDBQueryRange( $limit, $offset )
            ->setDBQueryOrders( $sort );
        
        $commissions = $this->db->get()->result_array();
        
        return $this->indexArray( $commissions, 'commission_id' );
    }
	
	public function addMarketingAgency()
	{
		$agency = $_POST['agency'];
		$agencyData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Insert Marketing Agency
		$agencyData = [
            'agency_name'            => ucwords( $agency['name'] ),
			'agency_contact_person'  => ucwords( $agency['contact-person'] ),
            'agency_contacts'        => $agency['contacts'],
            'agency_address'         => $agency['address'],
            'agency_email'           => $agency['email'],
            'agency_createdby'       => $_SESSION['admin']['user']['user_id'],
            'agency_updatedby'       => $_SESSION['admin']['user']['user_id'],
            'agency_created'         => date( 'Y-m-d H:i:s', time() ),
            'agency_updated'         => date( 'Y-m-d H:i:s', time() ),
		];
		
		$agencyInserted	= $this->db->insert('marketing_agency', $agencyData);
		$agencyId 			= $this->db->insert_id();
		//endOf: Insert Marketing Agency
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $agencyInserted) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$agency = $this->getMarketingAgencyById( $agencyId );
		
		return $agency;
	}
	
	public function updateMarketingAgency( $agencyId )
	{
		$agency         = $_POST['agency'];
		$agencyData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Update Marketing Agency
		$agencyData = [
            'agency_name'            => ucwords( $agency['name'] ),
			'agency_contact_person'  => ucwords( $agency['contact-person'] ),
            'agency_contacts'        => $agency['contacts'],
            'agency_address'         => $agency['address'],
            'agency_email'           => $agency['email'],
            'agency_updatedby'       => $_SESSION['admin']['user']['user_id'],
            'agency_updated'         => date( 'Y-m-d H:i:s', time() ),
		];
		
		$agencyUpdated = 
		$this->db->where([
				'agency_id' => $agencyId,
			])
			->update('marketing_agency', $agencyData);
		//endOf: Update Category
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $agencyUpdated) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$agency = $this->getMarketingAgencyById( $agencyId );

		return $agency;
	}
	
	protected function endProcess()
	{
		if ( isset( $_SESSION['post']['admin']['marketing-agencies/add'] ) ) {
			unset( $_SESSION['post']['admin']['marketing-agencies/add'] );
		}
		
		if ( isset( $_SESSION['post']['admin']['marketing-agencies/edit'] ) ) {
			unset( $_SESSION['post']['admin']['marketing-agencies/edit'] );
		}

		return $this;
	}
	
	/* Private Methods
	-------------------------------*/
}
