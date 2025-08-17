<?php //-->
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Applicant_Reports  extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );

		$this->load->model( 'm_report' );
	}
	
	public function index()
	{
		show_404();
	}

	public function applicants_search()
	{
		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/reports/applicants-search.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports/applicants-search.js',
		];

		$this->load->model( 'm_applicant' );

		$this->setVariables([
				'employers'  => $employers,
				'status'	 => ( new m_applicant )->status,
			])
			->renderPage('reports/applicants/search', true);
	}
	
	public function applicants()
	{   
		if ( ! isset( $_POST['applicant'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$post = $_POST['applicant'];

    	$applicants = ( new m_report )->getApplicants();

    	$employer   = [];

    	$this->load->model( 'm_applicant' );
    	$status = ( new m_applicant )->status;

    	if ( $post['employer'] > 0 ) {
    		$this->load->model( 'm_employer' );
    		$employer = ( new m_employer )->getEmployerById( $post['employer'] );
    	}

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/applicants.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'applicants'	    => $applicants,
				'employer'          => $employer,
				'groupByEmployer'   => ! $post['employer'] > 0,
				'dateFrom'          => $post['date-from'],
				'dateTo'            => $post['date-to'],
				'statusText'        => ( new m_applicant )->statusText,
			]);

		if ( $post['status'] == $status['Reserved'] ) {
			$this->setTitle('RESERVED APPLICANTS', false)
				->renderPage('reports/applicants/reserved', true);
		} else if ( $post['status'] == $status['Selected'] ) {
			$this->setTitle('SELECTED '.( isset( $post['selected'] ) ? 'but NO '.$post['selected'].' ' : ' ' ).'APPLICANTS', false)
				->renderPage('reports/applicants/selected', true);
		} else if ( $post['status'] == $status['Deployed'] ) {
			$this->setTitle('DEPLOYED APPLICANTS', false)
				->renderPage('reports/applicants/deployed', true);
		} else {
			$this->setTitle('ALL APPLICANTS', false)
				->renderPage('reports/applicants', true);
		}
	}

	public function employers()
	{
		if ( ! isset( $_POST['employer'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$post = $_POST['employer'];

		$employer = ( new m_report )->getEmployerById( $post['employer'] );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/employers.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employer' => $employer,
			])
			->setTitle( $employer['employer_name'] , false)
			->renderPage('reports/employers/job-offers', true);
	}

	public function employers_search()
	{
		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();

		$this->setVariables([
				'employers'  => $employers,
			])
			->renderPage('reports/employers/search', true);
	}

	public function marketing_agencies()
	{
		$agencies = ( new m_report )->getMarketingAgencies();

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'agencies' => $agencies,
			])
			->setTitle('ALL MARKETING AGENCIES', false)
			->renderPage('reports/marketing-agencies', true);
	}

	public function marketing_agents()
	{
		$agents = ( new m_report )->getMarketingAgents();

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'agents' => $agents,
			])
			->setTitle('ALL MARKETING AGENTS', false)
			->renderPage('reports/marketing-agents', true);
	}

	public function recruitment_agents()
	{
		$agents = ( new m_report )->getRecruitmentAgents();

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'agents' => $agents,
			])
			->setTitle('ALL RECRUITMENT AGENTS', false)
			->renderPage('reports/recruitment-agents', true);
	}
    
    
}

/* End of file reports.php */
/* Location: ./app/controllers/admin/reports.php */