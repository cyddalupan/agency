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

class m_country extends MY_Model {
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
	public function find( $countryId )
	{
		$this->db->flush_cache();
		$this->db->from( 'country' )
			->where([
				'country_id' => $countryId,
			]);

		$country = $this->db->get()->row_array();

		return $country;
	}

	public function delete( $countryId )
	{
		$country = $this->find( $countryId );

		$this->db->flush_cache();
		$this->db->where([
				'country_id' => $countryId,
			])
			->delete( 'country' );

		return $country ;
	}

	public function getCountries( $options = [], $limit = 0, $offset = 0, $sort = ['country_name', 'ASC'] )
	{
		$this->db->flush_cache();
		$this->db->from('country');

		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset)
			->setDBQueryOrders( $sort );

		$countries = $this->db->get()->result_array();
		
		return $this->indexArray( $countries, 'country_id' ); 
	}

	public function getCountriesCount( $options = [])
	{
		$this->db->flush_cache();
		$this->db->from('country');

		$this->setDBQueryOptions( $options );

		$countries = $this->db->count_all_results();

		return $countries;
	}
	
	public function getCountryById( $countryId )
	{
		$this->db->flush_cache();
		$this->db->from('country')
			->where([
				'country_id'	=> $countryId,
			]);

		$country = $this->db->get()->row_array();

		return $country;
	}
	
	public function addCountry()
	{
		$country = $_POST['country'];
		
		$countryData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Insert Category
		$countryData = [
			'country_name'			=> strtoupper( $country['name'] ),
			'country_abbr'			=> strtoupper( $country['abbr'] ),
			'country_code'			=> $country['code'],
			'country_createdby'		=> $_SESSION['admin']['user']['user_id'],
			'country_created'		=> date('Y-m-d H:i:s', time()),
		];
		
		$countryInserted	= $this->db->insert('country', $countryData);
		$countryId 			= $this->db->insert_id();
		//endOf: Insert Category
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $countryInserted) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$country = $this->getCountryById( $countryId );
		
		return $country;
	}
	
	public function updateCountry( $countryId )
	{
		$country = $_POST['country'];
		
		$countryData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Update Category
		$countryData = [
			'country_name'			=> strtoupper( $country['name'] ),
			'country_abbr'			=> strtoupper( $country['abbr'] ),
			'country_code'			=> $country['code'],
//			'category_photo'		=> '',
		];
		
		$countryUpdated = 
		$this->db->where([
				'country_id' => $countryId,
			])
			->update('country', $countryData);
		//endOf: Update Category
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $countryUpdated) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$country = $this->getCountryById( $countryId );
		
		return $country;
	}
	/* Protected Methods
	-------------------------------*/
	
	protected function endProcess()
	{
		if ( isset( $_SESSION['post']['admin']['settings/countries/add'] ) ) {
			unset( $_SESSION['post']['admin']['settings/countries/add'] );
		}
		
		if ( isset( $_SESSION['post']['admin']['settings/countries/edit'] ) ) {
			unset( $_SESSION['post']['admin']['settings/countries/edit'] );
		}

		return $this;
	}
	
	/* Private Methods
	-------------------------------*/
}
