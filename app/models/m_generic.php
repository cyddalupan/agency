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

class m_generic extends MY_Model {
 	/* Constants
	-------------------------------*/
	/* Public Properties
	-------------------------------*/ 	
	/*
	public $applicantStatus = [
		1 => 'Not Selected',
		2 => 'Pending',
		3 => 'Sent',
		0 => 'Cancelled',
		5 => 'Selected',
		6 => 'For Booking',
		7 => 'Deployed',
		8 => 'Blocklist',
	];
	
	public $applicantStatusColors = [
		1 => 'default',
		2 => 'info',
		3 => 'success',
		0 => 'danger',
		5 => 'default',
		6 => 'primary',
		7 => 'primary',
		8 => 'default',
	];
	*/
	/*
	public $applicantStatus = [
		0 => 'Cancelled/Backout',
		1 => 'Available', //Not Selected
		2 => 'Pre-Selected',
		3 => 'Qualified', //status will be line up (for selected)
		4 => 'Not Qualified',
		5 => 'Line Up', //For Selected
		6 => 'Selected',
		7 => 'For Deployment',//have ticket/waiting for air ticket
		8 => 'Deployed',
	];
	
	public $applicantStatusColors = [
		0 => 'danger',
		1 => 'default',
		2 => 'info',
		3 => 'success',
		4 => 'warning',
		5 => 'default',
		6 => 'primary',
		7 => 'info',
		8 => 'default',
	]; 
	*/
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
	public function addMeta($parent, $type, $key, $value) 
	{
		$metaData = [
			'meta_parent'	=> $parent,
			'meta_type'		=> $type,
			'meta_key'		=> $key,
			'meta_value'	=> $value,
		];
		
		$this->db->flush_cache();
		$this->db->insert('meta', $metaData);
	}
	
	public function addMetas($parent, $type, $key, $data = []) 
	{
		$metaData = []; 
		foreach ($data as $value) {
			$metaData[] = [
				'meta_parent'	=> $parent,
				'meta_type'		=> $type,
				'meta_key'		=> $key,
				'meta_value'	=> $value,
			];
		}
		
		$this->db->flush_cache();
		$this->db->insert_batch('meta', $metaData);
	}
	
	public function getMeta($parent, $type, $key) 
	{
		$options = [
			'meta_parent'	=> $parent,
			'meta_type'		=> $type,
			'meta_key'		=> $key,
		];
		
		$this->db->flush_cache();
		$this->db->from('meta')
			->where($options);
		
		$meta = $this->db->get()->row_array();
		
		return $meta['meta_value'];
	}
	
	public function getMetas($parent, $type, $key) 
	{
		$options = [
			'meta_parent'  => $parent,
			'meta_type'    => $type,
			'meta_key'     => $key,
		];
		
		$this->db->flush_cache();
		$this->db->from('meta')
			->where($options);
		
		$metas = $this->db->get()->result_array();
		
		$results = [];
		
		foreach ($metas as $meta) {
			$results[] = $meta['meta_value'];
		}
		
		return $results;
	}
	
	public function getHandledBy($userId) 
	{
		$this->db->flush_cache();
		
		$user = $this->db->from('user')
			->where(['user_id' => $userId])
			->get()->row_array();
		
		if(empty($user)) {
			$user = [
				'user_fullname'	=> 'Unknown',
				'user_name'		=> 'unknown',
				'user_type'		=> -1,
			];
		}
		
		return $user;
	}
		
	/* Protected Methods
	-------------------------------*/ 
	
	/* Private Methods
	-------------------------------*/
}
