<?php
use \Application\Message as Message;

defined('BASEPATH') OR exit('No direct script access allowed');

class Ng_signin extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PUBLIC;
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct() 
	{
		parent::__construct();

	}

	/**
	 * Ge Here Before going to dashboard (inside page)
	 * to save the userdata to laravel session and angular cookie
	 */
	public function index()
	{	
		$this->load->view('admin/ng_login');
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/Welcome.php */