<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Public_System extends CI_Controller {
	
	public function __construct() 
	{
		parent::__construct();
		
		header('Location: '.site_url('public/applicants/registration') );
		exit;
	}
	
}

/* End of file public_system.php */
/* Location: ./app/controllers/public_system.php */