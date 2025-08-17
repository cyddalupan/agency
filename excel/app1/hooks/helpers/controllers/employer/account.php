<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends Employer_Controller {
	const PAGE_ACCESS = parent::PAGE_PRIVATE;

	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
	}
	
	public function index()
	{ 
		
	}
	
	public function settings()
	{
		
	}
	
	public function change_password()
	{
	
	}
}

/* End of file account.php */
/* Location: ./app/controllers/employer/account.php */