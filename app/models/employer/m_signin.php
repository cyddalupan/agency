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

class m_signin extends MY_Model {
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
		
		$this->load->model( 'm_user' );
	}
	
	/* Public Methods
	-------------------------------*/
	/* Protected Methods
	-------------------------------*/
	public function login($userName, $password) {
		$condition = [
			'user_name' 	=> $userName,
			'user_password' => $this->m_user->encryptPass($password),
		];
		
		$this->db->flush_cache();
		$user = $this->db->from('user')
			->where($condition)
            ->join( 'employer', 'employer.employer_user = user.user_id' )
			->get()->row_array(); 

		if ( empty( $user ) ) { //user not found
			return false; 
		} elseif ( $user['user_type'] != 5 ) { //wrong user
			return 1;
		} elseif ( $user['user_status'] == 0) { //inactive user
			return 0; 
		}
		
		//login succesfully, update last login
		$this->db->flush_cache();
		$this->db->where([
			'user_id'   => $user['user_id'],
		])
		->update('user', [
			'user_lastlogin' => date('Y-m-d H:i:s', time()),
		]);

		$_SESSION['employer']['user'] = $user;
		
		return true;
	}
	
	
	/* Private Methods
	-------------------------------*/
}
