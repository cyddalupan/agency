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

class m_position extends MY_Model {
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
	public function find( $positionId )
	{
		$this->db->flush_cache();
		$this->db->from( 'position' )
			->where([
				'position_id' => $positionId,
			]);

		$position = $this->db->get()->row_array();

		return $position;
	}

	public function getActivePositionsGroupByCategory()
	{
		$this->db->flush_cache();
		$this->db->from('category')
			->order_by('category_name');
		
		$categories = $this->db->get()->result_array();
		
		$categoriesIndexed = [];
		foreach ($categories as $key => $category) {
			$this->db->flush_cache();
			$this->db->from('position')
				->join('category_positions', 'rel_position = position_id')
				->where([
					'rel_category' 		=> $category['category_id'],
					'position_status'	=> 1,
				])
				->order_by('position_name', 'ASC');
			
			$positions = $this->db->get()->result_array();
			
            $this->indexArray( $positions, 'position_id' );
			
			$categoriesIndexed[$category['category_id']] = $category;
			$categoriesIndexed[$category['category_id']]['positions'] = $positions;
		}
		
		return $categoriesIndexed;
	}
	
	public function getPositionsGroupByCategory()
	{
		$this->db->flush_cache();
		$this->db->from('category')
			->order_by('category_name');
		
		$categories = $this->db->get()->result_array();
		
		$categoriesIndexed = [];
		foreach ($categories as $key => $category) {
			$this->db->flush_cache();
			$this->db->from('position')
				->join('category_positions', 'rel_position = position_id')
				->where([
					'rel_category' 		=> $category['category_id'],
				])
				->order_by('position_name', 'ASC');
			
			$positions = $this->db->get()->result_array();
			
			$this->indexArray( $positions, 'position_id' );
			
			$categoriesIndexed[$category['category_id']] = $category;
			$categoriesIndexed[$category['category_id']]['positions'] = $positions;
		}
		
		return $categoriesIndexed;
	}
	
	public function getPositions()
	{
		$this->db->flush_cache();
		$this->db->from('position')
			->order_by('position_name', 'ASC');
		
		$positions = $this->db->get()->result_array();
		
		return $this->indexArray( $positions, 'position_id' );
	}
	
	public function getPositionById( $positionId )
	{
		$this->db->flush_cache();
		$this->db->from('position')
			->where([
				'position_id' => $positionId,
			]);
		
		$position = $this->db->get()->result_array();
		 
		return $position;
	}
	
	public function addPosition()
	{
		$position = $_POST['position'];
		
		//Start Transaction
		$this->db->trans_begin();
		
		//Insert position
		$positionData = [
			'position_name'       => $position['name'],
			'position_status'     => 1,
			'position_createdby'  => $_SESSION['admin']['user']['user_id'],
			'position_updatedby'  => $_SESSION['admin']['user']['user_id'],
			'position_created'    => date( 'Y-m-d H:i:s', time() ),
			'position_updated'    => date( 'Y-m-d H:i:s', time() ),	
		];
		
		$this->db->flush_cache();
		$positionInserted = $this->db->insert( 'position', $positionData );
		$positionId       = $this->db->insert_id();
		
		//Insert relational category position
		$categoryPositionData = [
			'rel_category'  => $position['category'],
			'rel_position'  => $positionId,
			'rel_createdby' => $_SESSION['admin']['user']['user_id'],
			'rel_created'   => date( 'Y-m-d H:i:s', time() ),
		];
		
		$this->db->flush_cache();
		$relPositionInserted = $this->db->insert( 'category_positions', $categoryPositionData );
		$relId               = $this->db->insert_id();
		
		$position = $this->getPositionById( $positionId );
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $positionInserted) {
			$this->db->trans_rollback();
			return false;
		}
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$this->endProcess();
		
		return $position;
	}

	public function delete( $positionId )
	{
		$position = $this->find( $positionId );

		$this->db->flush_cache();
		$this->db->where([
				'position_id' => $positionId,
			])
			->delete( 'position' );

		$this->db->flush_cache();
		$this->db->where([
				'rel_position' => $positionId,
			])
			->delete( 'category_positions' );	

		return $position ;
	}
	
	/* Protected Methods
	-------------------------------*/	
	protected function endProcess()
	{
		if (isset($_SESSION['post']['admin']['settings/categories/add-position'])) {
			unset($_SESSION['post']['admin']['settings/categories/add-position']);
		}
		 
		return $this;		
	}
	
	/* Private Methods
	-------------------------------*/
}
