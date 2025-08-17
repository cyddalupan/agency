<?php //-->
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Userlogs  extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		
		$this->load->model( 'm_applicant' );
	}
	
	public function index()
	{
		if ( ! isset( $_POST['log']['country'], $_POST['log']['date-from'], $_POST['log']['date-to'] ) )	{
			show_404();
		}
		
		$log  = $_POST['log'];

		$this->load->model( 'm_userlog' );
		$applicants = ( new m_userlog )->allByApplicant( $log['country'], $log['date-from'], $log['date-to'] );

		$this->load->model( 'm_applicant' );
		$statusText = ( new m_applicant )->statusText;

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/userlogs.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/userlogs.js',
		];

		$this->setVariables([
				'applicants'  => $applicants,
				'statusText'  => $statusText,
				'country'     => $log['country'],
				'dateFrom'    => $log['date-from'],
				'dateTo'      => $log['date-to'],
			])
			->setTitle('STATUS REPORT', false)
			->renderPage('userlogs', true);
	}
	
	public function search()
	{
		$this->load->model( 'm_country' );
		$countries = ( new m_country )->getCountries();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/userlogs/search.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/userlogs/search.js',
		];

		$this->setVariables([
				'countries' => $countries,
			])
			->renderPage('userlogs/search', true);
	}
 
 
	
	
}

/* End of file applicants.php */
/* Location: ./app/controllers/admin/userlogs.php */