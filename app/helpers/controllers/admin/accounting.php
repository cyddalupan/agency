<?php //-->
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounting  extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );

		$this->load->model( 'm_accounting' );
	}
	
	public function index()
	{
		show_404();
	}

	public function workers_search()
	{
		$this->load->model( 'm_employer');
		$employers = ( new m_employer )->getEmployers();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/accounting/workers.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/accounting/workers.js',
		];

		$this->setVariables([
				'employers' => $employers,
			])
			->renderPage('accounting/workers/search', true);
	}

	public function employers_search()
	{
		$employers = ( new m_accounting )->getEmployers();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/accounting/employers.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/accounting/employers.js',
		];

		$this->setVariables([
				'employers' => $employers,
			])
			->renderPage('accounting/employers/search', true);
	}

	public function marketing_agencies_search()
	{
		$this->load->model( 'm_marketing_agency');
		$agencies = ( new m_marketing_agency )->getMarketingAgencies();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/accounting/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/accounting/marketing-agencies.js',
		];

		$this->setVariables([
				'agencies' => $agencies,
			])
			->renderPage('accounting/marketing-agencies/search', true);
	}

	public function marketing_agents_search()
	{
		$this->load->model( 'm_marketing_agent');
		$agents = ( new m_marketing_agent )->getMarketingAgents();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/accounting/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/accounting/marketing-agents.js',
		];

		$this->setVariables([
				'agents' => $agents,
			])
			->renderPage('accounting/marketing-agents/search', true);
	}

	public function recruitment_agents_search()
	{
		$this->load->model( 'm_recruitment_agent');
		$agents = ( new m_recruitment_agent )->getRecruitmentAgents();

		$this->styles = [
			$this->getPath()['styles'] . 'pages/accounting/marketing-agents.css',
		];

		$this->styles = [
			$this->getPath()['styles'] . 'pages/accounting/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/accounting/recruitment-agents.js',
		];

		$this->setVariables([
				'agents' => $agents,
			])
			->renderPage('accounting/recruitment-agents/search', true);
	}

	public function workers()
	{
		if ( ! isset( $_POST['worker']['employer'], $_POST['worker']['status'], $_POST['worker']['date-from'], $_POST['worker']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employer = [];

		if ( $_POST['worker']['employer'] > 0 ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $_POST['worker']['employer'], false );
		}

		$status = 1;
		$title  = 'Workers';

		if ( ! $_POST['worker']['status'] ) {
			$status = 0;
			$title  = 'Not approved transactions';
		}

		$dateFrom = $_POST['worker']['date-from'];
		$dateTo   = $_POST['worker']['date-to'];

		$transactions = (new m_accounting)->getWorkersTransactions( $status, $dateFrom, $dateTo, $_POST['worker']['employer'] );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/accounting/workers.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'transactions' => $transactions,
				'employer'     => $employer,
				'dateFrom'     => $dateFrom,
				'dateTo'       => $dateTo,
			])
			->settitle( $title, false)
			->renderPage( 'accounting/workers', true );
	}

	public function employers()
	{
		if ( ! isset( $_POST['employer']['id'], $_POST['employer']['status'], $_POST['employer']['date-from'], $_POST['employer']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$status = 1;
		$title  = 'Employers';

		if ( ! $_POST['employer']['status'] ) {
			$status = 0;
			$title  = 'Not approved transactions';
		}

		$employer = [];

		$dateFrom = $_POST['employer']['date-from'];
		$dateTo   = $_POST['employer']['date-to'];

		$employerId = null;

		if ( $_POST['employer']['id'] > 0 ) {
			$employerId = $_POST['employer']['id'];
		}

		$transactions = (new m_accounting)->getEmployersTransactions( $employerId, $status, $dateFrom, $dateTo );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/accounting/employers.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'transactions' => $transactions,
				'dateFrom'     => $dateFrom,
				'dateTo'       => $dateTo,
			])
			->settitle( $title, false)
			->renderPage( 'accounting/employers', true );
	}

	public function marketing_agencies()
	{
		if ( ! isset( $_POST['agency']['agency'], $_POST['agency']['status'], $_POST['agency']['date-from'], $_POST['agency']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['agency']['agency'] > 0 ) {
			$this->marketing_agency( (int) $_POST['agency']['agency'] );
			return;
		}

		$status = 1;
		$title  = 'Marketing Agencies';

		if ( ! $_POST['agency']['status'] ) {
			$status = 0;
			$title  = 'Not approved transactions';
		}

		$dateFrom = $_POST['agency']['date-from'];
		$dateTo   = $_POST['agency']['date-to'];

		$agencies = (new m_accounting)->getMarketingAgencies( $status, $dateFrom, $dateTo );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/accounting/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agencies' => $agencies,
				'dateFrom'     => $dateFrom,
				'dateTo'       => $dateTo,
			])
			->settitle( $title, false)
			->renderPage( 'accounting/marketing-agencies', true );
	}

	public function marketing_agency( $agencyId )
	{
		$dateFrom = $_POST['agency']['date-from'];
		$dateTo   = $_POST['agency']['date-to'];

		$this->load->model('m_marketing_agency');
		$agency = ( new m_marketing_agency )->getMarketingAgencyById( $agencyId );

		$status = 1;
		$title  = $agency['agency_name'];

		if ( ! $_POST['agency']['status'] ) {
			$status = 0;
			$title  = 'Not approved transactions';
		}

		$agency['transactions'] = (new m_accounting)->getMarketingAgencyTransactions( $agencyId, $status, $dateFrom, $dateTo );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/accounting/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agency'       => $agency,
				'dateFrom'     => $dateFrom,
				'dateTo'       => $dateTo,
			])
			->settitle( $title, false)
			->renderPage( 'accounting/marketing-agencies/detail', true );
	}

	public function marketing_agents()
	{
		if ( ! isset( $_POST['agent']['date-from'], $_POST['agent']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['agent']['agent'] > 0 ) {
			$this->marketing_agent( (int) $_POST['agent']['agent'] );
			return;
		}

		$status = 1;
		$title  = 'Marketing Agents';

		if ( ! $_POST['agent']['status'] ) {
			$status = 0;
			$title  = 'Not approved transactions';
		}

		$dateFrom = $_POST['agent']['date-from'];
		$dateTo   = $_POST['agent']['date-to'];

		$agents = (new m_accounting)->getMarketingAgents( $status, $dateFrom, $dateTo );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/accounting/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agents'       => $agents,
				'dateFrom'     => $dateFrom,
				'dateTo'       => $dateTo,
			])
			->settitle( $title, false)
			->renderPage( 'accounting/marketing-agents', true );
	}

	public function marketing_agent( $agentId )
	{
		$dateFrom = $_POST['agent']['date-from'];
		$dateTo   = $_POST['agent']['date-to'];

		$this->load->model('m_marketing_agent');
		$agent = ( new m_marketing_agent )->getMarketingAgentById( $agentId );

		$status = 1;
		$title  = $agent['agent_first'].' '.$agent['agent_last'];

		if ( ! $_POST['agent']['status'] ) {
			$status = 0;
			$title  = 'Not approved transactions';
		}

		$agent['transactions'] = (new m_accounting)->getMarketingAgentTransactions( $agentId, $status, $dateFrom, $dateTo );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/accounting/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agent'        => $agent,
				'dateFrom'     => $dateFrom,
				'dateTo'       => $dateTo,
			])
			->settitle($title, false)
			->renderPage( 'accounting/marketing-agents/detail', true );
	}

	public function recruitment_agents()
	{
		if ( ! isset( $_POST['agent']['date-from'], $_POST['agent']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['agent']['agent'] > 0 ) {
			$this->recruitment_agent( (int) $_POST['agent']['agent'] );
			return;
		}

		$status = 1;
		$title  = 'Recruitment Agents';

		if ( ! $_POST['agent']['status'] ) {
			$status = 0;
			$title  = 'Not approved transactions';
		}

		$dateFrom = $_POST['agent']['date-from'];
		$dateTo   = $_POST['agent']['date-to'];

		$agents = (new m_accounting)->getRecruitmentAgents( $status, $dateFrom, $dateTo );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/accounting/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agents'       => $agents,
				'dateFrom'     => $dateFrom,
				'dateTo'       => $dateTo,
			])
			->settitle($title, false)
			->renderPage( 'accounting/recruitment-agents', true );
	}

	public function recruitment_agent( $agentId )
	{
		$dateFrom = $_POST['agent']['date-from'];
		$dateTo   = $_POST['agent']['date-to'];

		$this->load->model('m_recruitment_agent');
		$agent = ( new m_recruitment_agent )->getRecruitmentAgentById( $agentId );

		$status = 1;
		$title  = $agent['agent_first'].' '.$agent['agent_last'];

		if ( ! $_POST['agent']['status'] ) {
			$status = 0;
			$title  = 'Not approved transactions';
		}

		$agent['transactions'] = (new m_accounting)->getRecruitmentAgentTransactions( $agentId, $status, $dateFrom, $dateTo );
 
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/accounting/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agent'        => $agent,
				'dateFrom'     => $dateFrom,
				'dateTo'       => $dateTo,
			])
			->settitle( $title, false)
			->renderPage( 'accounting/recruitment-agents/detail', true );
	}

	 
	
}

/* End of file accounting.php */
/* Location: ./app/controllers/admin/accounting.php */