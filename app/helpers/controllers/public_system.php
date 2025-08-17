<?php
//use System as System;

defined('BASEPATH') OR exit('No direct script access allowed');

class Public_System extends CI_Controller {
	
	public function __construct() 
	{
		parent::__construct();
		
		header('Location: '.site_url('public') );
		exit;
	}
	
}

/* End of file Public_System.php */
/* Location: ./app/controllers/Public_System.php */