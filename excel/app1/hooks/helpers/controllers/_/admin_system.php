<?php
//use System as System;

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_System extends CI_Controller {
	
	public function __construct() 
	{
		parent::__construct();
		
		header('Location: '.site_url('admin') );
		exit;
	}
	
}

/* End of file Admin_System.php */
/* Location: ./app/controllers/Admin_System.php */