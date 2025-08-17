<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Agencies extends Admin_Controller {
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
	}
	
	public function index()
	{ 
		show_404();
	}
	
	public function marketing_agencies()
	{ 
		Pagination::init( 50 );

		$this->load->model( 'm_marketing_agency');
		
		//Form Submitted
		if ( isset( $_POST['agency'], $_POST['flag'] ) && $_POST['flag'] == 'add' ) {
			$_SESSION['post']['admin']['agencies/marketing-agencies'] = $_POST;
		
			self::checkMarketingAgencyDataAdd();
			
			$agency = $this->m_marketing_agency->addMarketingAgency();
			
			if ( !empty( $agency ) ) {
				Message::addSuccess('New marketing agency has been added successfully!', false, 'Success');
				redirect( site_url( 'admin/agencies/marketing-agencies' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/agencies/marketing-agencies' ) );
			exit;
		}
		
		if ( isset( $_POST['agency'], $_POST['flag'] ) && $_POST['flag'] == 'edit' ) {
			
			$_SESSION['post']['admin']['agencies/marketing-agencies'] = $_POST;
		
			self::checkMarketingAgencyDataEdit();
			
			$agency = $this->m_marketing_agency->updateMarketingAgency( $_POST['agency']['agency_id'] );

			if ( ! empty( $agency ) ) {
				Message::addSuccess('<strong>'.$agency['agency_name'].'</strong> has been updated!', false, 'Success');
				redirect( site_url( 'admin/agencies/marketing-agencies' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/agencies/marketing-agencies' ) );
			exit;
		}
		//endOf: Form Submitted
		
		$options = [];

		$agencies      = $this->m_marketing_agency->getMarketingAgencies( $options, Pagination::getPerPage(), Pagination::getRecordCursor() );
		$agenciesCount = $this->m_marketing_agency->getMarketingAgenciesCount( $options );

		Pagination::setOptions([
			'total-records'   => $agenciesCount,
			'html'            => [
				'pagination_open_tag'  => '<ul class="pull-right pagination">',
			    'previous_open_tag'    => '<li class="prev"><a href="{link}">',
			],
		]);
		
		$this->scripts = [
            $this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['scripts'] . 'pages/agencies/marketing-agencies.js',
		];        
		
		$this->modalsTpl = 'agencies/marketing-agencies.modal.php';
		
		$this->setVariables([
			'agencies'          => $agencies,
			'paginationHTML'    => Pagination::generateHTML(),
			'paginationCounter' => Pagination::getCounters(),
		])
		->setTitle('Marketing Agencies')
		->renderPage('agencies/marketing-agencies');
	}

	public function marketing_agents()
	{ 
		Pagination::init( 50 );

		$this->load->model( 'm_marketing_agent');
		
		//Form Submitted
		if ( isset( $_POST['agent'], $_POST['flag'] ) && $_POST['flag'] == 'add' ) {
			$_SESSION['post']['admin']['agencies/marketing-agents'] = $_POST;
		
			//self::checkMarketingAgentDataAdd();
			
			$agent = $this->m_marketing_agent->addMarketingAgent();
			
			if ( !empty( $agent ) ) {
				Message::addSuccess('New marketing agent has been added successfully!', false, 'Success');
				redirect( site_url( 'admin/agencies/marketing-agents' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/agencies/marketing-agents' ) );
			exit;
		}
		
		if ( isset( $_POST['agent'], $_POST['flag'] ) && $_POST['flag'] == 'edit' ) {
			
			$_SESSION['post']['admin']['agencies/marketing-agents'] = $_POST;
		
			//self::checkMarketingAgentDataEdit();
			
			$agent = $this->m_marketing_agent->updateMarketingAgent( $_POST['agent']['agent_id'] );

			if ( ! empty( $agent ) ) {
				Message::addSuccess('<strong>'.$agent['agent_first'].' '.$agent['agent_last'].'</strong> has been updated!', false, 'Success');
				redirect( site_url( 'admin/agencies/marketing-agents' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/agencies/marketing-agents' ) );
			exit;
		}
		//endOf: Form Submitted
		
		$options = [];

		$agents      = $this->m_marketing_agent->getMarketingAgents( $options, Pagination::getPerPage(), Pagination::getRecordCursor() );
		$agentsCount = $this->m_marketing_agent->getMarketingAgentsCount( $options );

		Pagination::setOptions([
			'total-records'   => $agentsCount,
			'html'            => [
				'pagination_open_tag'  => '<ul class="pull-right pagination">',
			    'previous_open_tag'    => '<li class="prev"><a href="{link}">',
			],
		]);
		
		$this->scripts = [
            $this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['scripts'] . 'pages/agencies/marketing-agents.js',
		];        
		
		$this->modalsTpl = 'agencies/marketing-agents.modal.php';
		
		$this->setVariables([
			'agents'            => $agents,
			'paginationHTML'    => Pagination::generateHTML(),
			'paginationCounter' => Pagination::getCounters(),
		])
		->setTitle('Marketing Agencies')
		->renderPage('agencies/marketing-agents');
	}

	public function recruitment_agents()
	{ 
		Pagination::init( 50 );

		$this->load->model( 'm_recruitment_agent');
		
		//Form Submitted
		if ( isset( $_POST['agent'], $_POST['flag'] ) && $_POST['flag'] == 'add' ) {
			$_SESSION['post']['admin']['agencies/recruitment-agents'] = $_POST;
		
			self::checkRecruitmentAgencyDataAdd();
			
			$agent = $this->m_recruitment_agent->addRecruitmentAgent();
			
			if ( !empty( $agent ) ) {
				Message::addSuccess('New recruitment agent has been added successfully!', false, 'Success');
				redirect( site_url( 'admin/agencies/recruitment-agents' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/agencies/recruitment-agents' ) );
			exit;
		}
		
		if ( isset( $_POST['agent'], $_POST['flag'] ) && $_POST['flag'] == 'edit' ) {
			
			$_SESSION['post']['admin']['agencies/recruitment-agents'] = $_POST;
		
			self::checkRecruitmentAgencyDataEdit();
			
			$agent = $this->m_recruitment_agent->updateRecruitmentAgent( $_POST['agent']['agent_id'] );

			if ( ! empty( $agent ) ) {
				Message::addSuccess('<strong>'.$agent['agent_name'].'</strong> has been updated!', false, 'Success');
				redirect( site_url( 'admin/agencies/recruitment-agents' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', false, 'Oops!');
			redirect( site_url( 'admin/agencies/recruitment-agents' ) );
			exit;
		}
		//endOf: Form Submitted
		
		$options = [];

		$agents      = $this->m_recruitment_agent->getRecruitmentAgents( $options, Pagination::getPerPage(), Pagination::getRecordCursor() );
		$agentsCount = $this->m_recruitment_agent->getRecruitmentAgentsCount( $options );

		Pagination::setOptions([
			'total-records'   => $agentsCount,
			'html'            => [
				'pagination_open_tag'  => '<ul class="pull-right pagination">',
			    'previous_open_tag'    => '<li class="prev"><a href="{link}">',
			],
		]);
		
		$this->scripts = [
            $this->getPath()['plugins'] . 'tagsinput/bootstrap-tagsinput.js',
			$this->getPath()['scripts'] . 'pages/agencies/recruitment-agents.js',
		];        
		
		$this->modalsTpl = 'agencies/recruitment-agents.modal.php';
		
		$this->setVariables([
			'agents'            => $agents,
			'paginationHTML'        => Pagination::generateHTML(),
			'paginationCounter' => Pagination::getCounters(),
		])
		->setTitle('Recruitment Agencies')
		->renderPage('agencies/recruitment-agents');
	}

	protected static function checkMarketingAgencyDataAdd()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/agencies/marketing-agencies' );
		$agency 	= $_POST['agency'];
 
		if ( empty( $agency['name']  ) ) {
			$errors[] = '* <strong>Marketing agency name</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
	
	protected static function checkMarketingAgencyDataEdit()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/agencies/marketing-agencies' );
		$agency 	= $_POST['agency'];
 
		if ( empty( $agency['name']  ) ) {
			$errors[] = '* <strong>Marketing agency name</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
}

/* End of file settings.php */
/* Location: ./app/controllers/admin/settings.php */