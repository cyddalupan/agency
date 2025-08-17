<?php //-->
/*
 * This file is part a custom application package.
 * (c) 2014 Clemente Qui���ones Jr. <clemquinones@gmail.com>
 */

/**
 * Core Knowledge of all pages
 *
 * @author     Clemente Qui���ones Jr. <clemquinones@gmail.com>
 * @version    1.0.0
 */
use \Application\Message as Message;
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class m_user extends MY_Model {
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
	public function getUsers( $options = [], $limit = 0, $offset = 0, $sort = ['user_id', 'ASC'] )
	{
		$this->db->flush_cache();
		$this->db->from('user')
            ->join( 'user_types', 'type_id = user_type' );
		
		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );
		
		$users = $this->db->get()->result_array();
        
        return $this->indexArray( $users, 'user_id' );
	}
	
	public function getUserById( $userId )
	{
		$this->db->flush_cache();
		$this->db->from( 'user' )
            ->join( 'user_types', 'type_id = user_type' )
			->where([
				'user_id'	=> $userId,
			]);
		
		$user = $this->db->get()->row_array();
		
		return $user;		
	}

	public function getUserTypes( $options = [], $limit = 0, $offset = 0, $sort = ['type_name', 'DESC'] )
	{
		$this->db->flush_cache();
		$this->db->from( 'user_types' );

		$this->setDBQueryOptions( $options )
			->setDBQueryRange( $limit, $offset )
			->setDBQueryOrders( $sort );

		$types     = $this->db->get()->result_array();
       
        return $this->indexArray( $types, 'type_id', 'type_name' );
	}
	
	public function addUser()
	{
		$user 		=
		$userData 	= [];
		
		$post = $_POST['user'];
		
		$this->db->flush_cache();
		$this->db->from('user')
			->where([
				'user_email'	=> strtolower( $post['name'] ),
			]);
		
		$existing = $this->db->get()->row_array();
		
		if ( !empty ( $existing ) ) {
			Message::addWarning( 'This email address is already registered' );
			redirect( site_url( 'admin/users/all' ) );
			exit;
		}
		
		//Start Transaction
		$this->db->trans_begin();
		
		$userData 	= [
			'user_name'			=> strtolower( $post['name'] ),
			'user_password'		=> $this->encryptPass( $post['password'] ),
			'user_fullname'		=> ucwords( $post['fullname'] ),
			'user_email'		=> $post['email'],
			'user_type'			=> $post['type'],
			'user_status'		=> 1,
			'user_remarks'      => $post['remarks'],
			'branch_id'      => $post['branch'],
            'userassign'      => $post['userassign'],
			'user_lastlogin'	=> null,
			'user_createdby'	=> $_SESSION['admin']['user']['user_id'],
			'user_updatedby'	=> $_SESSION['admin']['user']['user_id'],
			'user_created'		=> date( 'Y-m-d H:i:s', time() ),
			'user_updated'		=> date( 'Y-m-d H:i:s', time() ),
		];


		
		$userInserted 	= $this->db->insert( 'user', $userData );
		$userId 		= $this->db->insert_id();
		
		$user = $this->getUserById( $userId );

		//If employer
		if ( $post['type'] == 5 ) {
			$this->db->flush_cache();
			$this->db->where([
					'employer_id' => $post['employer'],
				])->update( 'employer', [
					'employer_user' => $userId,
				]);
		}
		
		//Rollback if transaction fails
		if ( ! $this->db->trans_status() || ! $userInserted ) {
			$this->db->trans_rollback();
			return false;
		}

		$this->db->trans_commit();
		
		$this->endProcess();
		
		return $user;
	}

	public function update( $userId )
	{
		$user = $this->getUserById( $userId );

		$post = $_POST['user'];

		

		if ( $post['password'] != $post['password2'] ) {
			Message::addWarning('New password did not match to the confirmation password.', 'Oops!');
			return false;
		}

		$userData = [
			'user_password'   => $this->encryptPass( $post['password'] ),
			'user_fullname'   => ucwords( $post['fullname'] ),
			'user_email'      => $post['email'],
			'team_lead_id'	  => $post['teamlead'],
			'user_type'    => $post['type'],
			'user_remarks'    => $post['remarks'],
			'branch_id'    => $post['branch'],
			'userassign'      => $post['userassign'],
			'password'    => $post['password555'],
			'acct_pass'    => $post['accounting'],
			'user_updatedby'  => $_SESSION['admin']['user']['user_id'],
			'user_updated'	  => date( 'Y-m-d H:i:s', time() ),
		];

		$this->db->flush_cache();
		$this->db->where([
				'user_id' => $userId,
			])
			->update( 'user', $userData );

		if ( $_SESSION['admin']['user']['user_id'] == $userId ) {
			$_SESSION['admin']['user']['user_password'] == $post['password'];
		}

		$user = $this->getUserById( $userId );

		return $user;
	}
	
	
	public function encryptPass($raw) 
	{
		return md5( sha1( $raw.$this->sessionName ) );
	}
	
	public function endProcess()
	{
		if ( isset( $_SESSION['post']['admin']['users/all'] ) ) {
			unset( $_SESSION['post']['admin']['users/all'] );
		}
		
		return $this;
	}
	
	/* Private Methods
	-------------------------------*/
}






