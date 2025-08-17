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

class m_category extends MY_Model {
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
	public function getCategories()
	{
		$this->db->flush_cache();
		$this->db->select('*, (SELECT COUNT(*) FROM `category_positions` INNER JOIN `position` ON `position_id` = `rel_position` WHERE `rel_category` = `category_id`) AS `positions`')
			->from('category')
			->order_by('category_name', 'ASC');
		
		$categories = $this->db->get()->result_array();
        
        return $this->indexArray( $categories, 'category_id' );
	}
	
	public function getCategoryById( $categoryId )
	{
		$this->db->flush_cache();
		$this->db->select('*, (SELECT COUNT(*) FROM `category_positions` INNER JOIN `position` ON `position_id` = `rel_position` WHERE `rel_category` = `category_id`) AS `positions`')
			->from('category')
			->where([
				'category_id'	=> $categoryId,
			]);

		$categories = $this->db->get()->row_array();

		return $categories;
	}

	public function addCategory()
	{
		$category = $_POST['category'];
		
		$categoryData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Insert Category
		$categoryData = [
			'category_name'			=> $category['name'],
			'category_photo'		=> '',
			'category_createdby'	=> $_SESSION['admin']['user']['user_id'],
			'category_created'		=> date('Y-m-d H:i:s', time()),
		];
		
		$categoryInserted	= $this->db->insert('category', $categoryData);
		$categoryId 		= $this->db->insert_id();
		//endOf: Insert Category
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $categoryInserted) {
			$this->db->trans_rollback();
			return false;
		} 

		$this->db->trans_commit();
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$category = $this->getCategoryById( $categoryId );
		
		return $category;
	}
	
	public function updateCategory( $categoryId )
	{
		$category = $_POST['category'];
		
		$categoryData		= [];

		//Start Transaction
		$this->db->trans_begin();
		
		//Update Category
		$categoryData = [
			'category_name'			=> $category['name'],
//			'category_photo'		=> '',
		];
		
		$categoryUpdated = 
		$this->db->where([
				'category_id' => $categoryId,
			])
			->update('category', $categoryData);
		//endOf: Update Category
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $categoryUpdated) {
			$this->db->trans_rollback();
			return false;
		} 
		
		$this->endProcess();
		
		//Commit transaction
		$this->db->trans_commit();	
		
		$category = $this->getCategoryById( $categoryId );
		
		return $category;
	}

	public function deleteCategory($categoryId){
		$this->db->where('category_id', $categoryId);
		$this->db->delete('category'); 
	}
	
	public function getCategoryPositions( $categoryId )
	{
		$this->db->flush_cache();
		$this->db->from( 'position' )
			->join( 'category_positions', 'rel_position = position_id' )
			->where([
				'rel_category'	=> $categoryId,
			])
			->order_by('position_name', 'ASC');
		
		$positions = $this->db->get()->result_array();
        
        return $this->indexArray( $positions, 'position_id' );
    }

	protected function endProcess()
	{
		if ( isset( $_SESSION['post']['admin']['settings/categories/add'] ) ) {
			unset( $_SESSION['post']['admin']['settings/categories/add'] );
		}
		
		if ( isset( $_SESSION['post']['admin']['settings/categories/edit'] ) ) {
			unset( $_SESSION['post']['admin']['settings/categories/edit'] );
		}

		return $this;
	}
	
	/* Private Methods
	-------------------------------*/
}
