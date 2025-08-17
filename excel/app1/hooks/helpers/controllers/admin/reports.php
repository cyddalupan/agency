<?php //-->
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports  extends Admin_Controller {
	
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

		$post     = $_POST['employer'];
		$dateFrom = $dateTo = null;

		if ( isset( $post['date-from'], $post['date-to'] ) ) {
			$dateFrom   = fdate( 'Y-m-d', $post['date-from'] );
			$dateTo     = fdate( 'Y-m-d', $post['date-to'] );
		}

		if ( $post['employer'] > 0 ) {

			$this->employer( $post['employer'], $dateFrom, $dateTo );
			return;
		}

		$employers = ( new m_report )->getEmployers( true, $dateFrom, $dateTo );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/employers.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers' => $employers,
				'dateFrom'  => $dateFrom,
				'dateTo'    => $dateTo,
			])
			->setTitle( 'All Employers' , false)
			->renderPage('reports/employers/job-offers', true);
	}

	public function employer()
	{
		if ( ! isset( $_POST['employer'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$post = $_POST['employer'];

		if ( ! $post['employer'] ) {
			echo 'You didn\'t specify an employer.';
			exit;	
		}

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
			->renderPage('reports/employers/employer-job-offers', true);
	}

	public function employers_search()
	{
		$this->load->model( 'm_employer' );
		$employers = ( new m_employer )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers'  => $employers,
			])
			->renderPage('reports/employers/search', true);
	}

	public function marketing_agencies_employers_search()
	{
		$agencies = ( new m_report )->getMarketingAgencies();

		$this->setVariables([
				'agencies' => $agencies,
			])
			->renderPage('reports/marketing-agencies/employers-search', true);
	}

	public function marketing_agencies_employers()
	{
		if ( ! isset( $_POST['employer']['agency'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['employer']['agency'] > 0 ) {
			$this->marketing_agency_employers( $_POST['employer']['agency'] );
			return;
		}

		$agencies = ( new m_report )->getMarketingAgencies( true );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agencies' => $agencies,
			])
			->setTitle('ALL EMPLOYERS', false)
			->renderPage('reports/marketing-agencies/agencies-employers', true);
	}

	public function marketing_agency_employers( $agencyId )
	{
		if ( ! isset( $_POST['employer']['agency'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['employer']['agency'] == 0 ) {
			echo 'You didn\'t specify an agency.';
			exit;
		}

		$agency = ( new m_report )->getMarketingAgency( $agencyId );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agency' => $agency,
			])
			->setTitle( $agency['agency_name'], false)
			->renderPage('reports/marketing-agencies/agency-employers', true);
	}

	public function marketing_agencies_deployed_search()
	{
		$this->load->model( 'm_marketing_agency' );
		$agencies  = ( new m_marketing_agency )->getMarketingAgencies();

		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'agencies'  => $agencies,
				'employers' => $employers,
			])
			->renderPage('reports/marketing-agencies/deployed-search', true);
	}

	public function marketing_agencies_deployed_applicants()
	{
		if ( ! isset( $_POST['applicant']['agency'], $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$agencyId   = (int) $_POST['applicant']['agency'];

		if ( $agencyId > 0 ) {
			$this->marketing_agency_deployed_applicants( $agencyId );
			return;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agencies = ( new m_report )->getDeployedApplicantsGroupByMarketingAgencies( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agencies' => $agencies,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Deployed Applicants grouped by Marketing Agencies', false)
			->renderPage('reports/marketing-agencies/deployed-applicants', true);
	}

	public function marketing_agency_deployed_applicants( $agencyId )
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agency = ( new m_report )->getDeployedApplicantsByMarketingAgency( $agencyId, $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agency'   => $agency,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( $agency['agency_name'].' : Deployed Applicants', false)
			->renderPage('reports/marketing-agencies/agency-deployed-applicants', true);
	}

	public function marketing_agencies_selected_search()
	{
		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers' => $employers,
			])
			->renderPage('reports/marketing-agencies/selected-search', true);
	}

	public function marketing_agencies_selected_applicants()
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agencies = ( new m_report )->getSelectedApplicantsGroupByMarketingAgencies( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agencies' => $agencies,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Selected Applicants grouped by Marketing Agencies', false)
			->renderPage('reports/marketing-agencies/selected-applicants', true);
	}

	public function marketing_agents_employers_search()
	{
		$agents = ( new m_report )->getMarketingAgents();

		$this->setVariables([
				'agents' => $agents,
			])
			->renderPage('reports/marketing-agents/employers-search', true);
	}

	public function marketing_agents_employers()
	{
		if ( ! isset( $_POST['employer']['agent'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['employer']['agent'] > 0 ) {
			$this->marketing_agent_employers( $_POST['employer']['agent'] );
			return;
		}

		$agents = ( new m_report )->getMarketingAgents( true );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agents' => $agents,
			])
			->setTitle('ALL EMPLOYERS', false)
			->renderPage('reports/marketing-agents/agents-employers', true);
	}

	public function marketing_agent_employers( $agentId )
	{
		if ( ! isset( $_POST['employer']['agent'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		if ( $_POST['employer']['agent'] == 0 ) {
			echo 'You didn\'t specify an agent.';
			exit;
		}

		$agent = ( new m_report )->getMarketingAgent( $agentId );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'agent' => $agent,
			])
			->setTitle( $agent['agent_first'].' '.$agent['agent_last'], false)
			->renderPage('reports/marketing-agents/agent-employers', true);
	}

	public function marketing_agents_deployed_search()
	{
		$this->load->model( 'm_marketing_agent' );
		$agents  = ( new m_marketing_agent )->getMarketingAgents();

		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'agents'    => $agents,
				'employers' => $employers,
			])
			->renderPage('reports/marketing-agents/deployed-search', true);
	}

	public function marketing_agents_deployed_applicants()
	{
		if ( ! isset( $_POST['applicant']['agent'], $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$agentId   = (int) $_POST['applicant']['agent'];

		if ( $agentId > 0 ) {
			$this->marketing_agent_deployed_applicants( $agentId );
			return;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agents = ( new m_report )->getDeployedApplicantsGroupByMarketingAgents( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agents' => $agents,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Deployed Applicants grouped by Marketing Agents', false)
			->renderPage('reports/marketing-agents/deployed-applicants', true);
	}

	public function marketing_agent_deployed_applicants( $agentId )
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agent = ( new m_report )->getDeployedApplicantsByMarketingAgent( $agentId, $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agent'    => $agent,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( $agent['agent_first'].' '.$agent['agent_last'].' : Deployed Applicants', false)
			->renderPage('reports/marketing-agents/agent-deployed-applicants', true);
	}

	public function marketing_agents_selected_search()
	{
		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers' => $employers,
			])
			->renderPage('reports/marketing-agents/selected-search', true);
	}

	public function marketing_agents_selected_applicants()
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agents = ( new m_report )->getSelectedApplicantsGroupByMarketingAgents( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/marketing-agencies.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agents' => $agents,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Selected Applicants grouped by Marketing Agents', false)
			->renderPage('reports/marketing-agents/selected-applicants', true);
	}

    public function recruitment_agents_deployed_search()
	{
		$this->load->model( 'm_recruitment_agent' );
		$agents  = ( new m_recruitment_agent )->getRecruitmentAgents();

		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'agents'    => $agents,
				'employers' => $employers,
			])
			->renderPage('reports/recruitment-agents/deployed-search', true);
	}

	public function recruitment_agents_deployed_applicants()
	{
		if ( ! isset( $_POST['applicant']['agent'], $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$agentId = $_POST['applicant']['agent'];

		if ( $agentId > 0 ) {
			$this->recruitment_agent_deployed_applicants( $agentId );
			return;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agents = ( new m_report )->getDeployedApplicantsGroupByRecruitmentAgents( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agents'   => $agents,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Deployed Applicants grouped by Recruitment Agents', false)
			->renderPage('reports/recruitment-agents/deployed-applicants', true);
	}

	public function recruitment_agent_deployed_applicants( $agentId )
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agent = ( new m_report )->getDeployedApplicantsByRecruitmentAgent( $agentId, $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agent'    => $agent,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( $agent['agent_first'].' '.$agent['agent_last'].' : Deployed Applicants', false)
			->renderPage('reports/recruitment-agents/agent-deployed-applicants', true);
	}

	public function recruitment_agents_selected_search()
	{
		$employers = ( new m_report )->getEmployers();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/reports.js',
		];

		$this->setVariables([
				'employers' => $employers,
			])
			->renderPage('reports/recruitment-agents/selected-search', true);
	}

	public function recruitment_agents_selected_applicants()
	{
		if ( ! isset( $_POST['applicant']['employer'], $_POST['applicant']['date-from'], $_POST['applicant']['date-to'] ) ) {
			echo 'No direct access allowed.';
			exit;
		}

		$employerId = (int) $_POST['applicant']['employer'];
		$dateFrom   = fdate( 'Y-m-d', $_POST['applicant']['date-from'] );
		$dateTo     = fdate( 'Y-m-d', $_POST['applicant']['date-to'] );

		$employer = [];
		if ( $employerId ) {
			$this->load->model( 'm_employer' );
			$employer = ( new m_employer )->getEmployerById( $employerId );
		}

		$agents = ( new m_report )->getSelectedApplicantsGroupByRecruitmentAgents( $employerId, $dateFrom, $dateTo );
		
		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/recruitment-agents.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
		];

		$this->setVariables([
				'employer' => $employer,
				'agents'   => $agents,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			])
			->setTitle( 'Selected Applicants grouped by Recruitment Agents', false)
			->renderPage('reports/recruitment-agents/selected-applicants', true);
	}

	public function voucher( $voucherId )
	{   
		$this->load->model('m_voucher');
		$voucher = ( new m_voucher )->getVoucherbyId( $voucherId, true );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/voucher.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports/voucher.js',
		];

		$this->setVariables([
				'voucher'	    => $voucher,
			]);

		$this->setTitle('Voucher# '.str_pad( $voucherId, 5, '0', STR_PAD_LEFT ), false)
			->renderPage('reports/commissions/voucher', true);
	}

	public function OReceipt($orId )
	{   
		$this->load->model('m_or');
		$or = ( new m_or )->getORbyId( $orId );

		$this->styles = [
			$this->getPath()['plugins'] . 'print-area/PrintArea.css',
			$this->getPath()['styles'] . 'pages/reports/or.css',
		];

		$this->scripts = [
			$this->getPath()['plugins'] . 'print-area/jquery.PrintArea.js',
			$this->getPath()['scripts'] . 'pages/reports/or.js',
		];

		$this->setVariables([
				'or'	    => $or,
			]);

		$this->setTitle('OR# '.str_pad( $orId, 5, '0', STR_PAD_LEFT ), false)
			->renderPage('reports/billing/or', true);
	}
    
}

/* End of file reports.php */
/* Location: ./app/controllers/admin/reports.php */