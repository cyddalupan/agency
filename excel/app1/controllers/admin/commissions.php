<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

defined('BASEPATH') OR exit('No direct script access allowed');

class Commissions extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		
		$this->load->model( 'm_employer' );
	}
	
	public function index()
	{
		show_404();
	}

	public function transactions()
	{
		$this->load->model( 'm_voucher' );

		if ( isset( $_GET['search']['voucher'] ) ) {
			$vouchers = ( new m_voucher )->searchVouchers();
		} else {
			$vouchers = ( new m_voucher )->getVouchers();
		}

		$this->scripts = [
			$this->getPath()['scripts'] . 'pages/commissions/transactions.js'
		];
 
		//Load modal
		$this->modalsTpl = 'transactions.modal.php';

		$this->setVariables([
				'vouchers' => $vouchers,
			])
			->setTitle('All transactions')
			->renderPage('commissions/transactions');
	}

	public function voucher_review( $voucherId )
	{
		$this->load->model( 'm_voucher' );

		if ( isset( $_POST['voucher'] ) ) {

			$voucher = ( new m_voucher )->updateVoucher( $voucherId );

			if ( $voucher ) {
				Message::addSuccess( 'Voucher# <strong>'.$voucher['voucher_number'].'</strong> has been updated.', 
									false, 'Success!' );
				
				redirect( site_url( 'admin/commissions/transactions' ) );
				exit;
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/commissions/transactions' ) );
			exit;
		}

		checkAJAX();

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] .'pages/commissions/voucher-review.js',
		];
		
		$voucher = ( new m_voucher )->getVoucherById( $voucherId, true );

		$this->setVariables([
				'voucher' => $voucher,
			])
			->renderPage('commissions/voucher-review', true);
	}

	public function voucher_revert( $voucherId )
	{
		$this->load->model( 'm_voucher' );
		$voucher = ( new m_voucher )->revertVoucher( $voucherId );

		if ( $voucher ) {
			Message::addSuccess( 'Transaction (#'.$voucher['voucher_number'].') has been removed.', false, 'Success!' );
			redirect( site_url( 'admin/commissions/transactions' ) );
			exit;
		}

		Message::addWarning( 'There was an error while processing your request. Please try again.' );
		redirect( site_url( 'admin/commissions/transactions' ) );
		exit;
	}
	
	public function marketing_agencies()
	{
		$from = $to = null;
		$queryString = '';

		if ( isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ) {
			$from = $_GET['filter']['from'];
			$to   = $_GET['filter']['to'];

			$queryString = '?filter[from]='.$from.'&filter[to]='.$to;
		}

		$this->load->model( 'm_commission_marketing_agency' );
		$agencies = ( new m_commission_marketing_agency )->getAgencies( false, $from, $to );

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/commissions/marketing-agencies.js'
		];

		$this->setVariables([
				'agencies' => $agencies,
				'queryString'=> $queryString,
			])
			->setTitle('Marketing Agencies commissions')
			->renderPage('commissions/marketing-agencies');
	}

	public function marketing_agents()
	{	
		$from = $to = null;
		$queryString = '';

		if ( isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ) {
			$from = $_GET['filter']['from'];
			$to   = $_GET['filter']['to'];

			$queryString = '?filter[from]='.$from.'&filter[to]='.$to;
		}
		
		$this->load->model( 'm_commission_marketing_agent' );
		$agents = ( new m_commission_marketing_agent )->getAgents( false, $from, $to );

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/commissions/marketing-agents.js'
		];

		$this->setVariables([
				'agents' => $agents,
				'queryString'=> $queryString,
			])
			->setTitle('Marketing Agents commissions')
			->renderPage('commissions/marketing-agents');
	}

	public function recruitment_agents()
	{
		$from = $to = null;
		$queryString = '';

		if ( isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ) {
			$from = $_GET['filter']['from'];
			$to   = $_GET['filter']['to'];

			$queryString = '?filter[from]='.$from.'&filter[to]='.$to;
		}

		$this->load->model( 'm_commission_recruitment_agent' );
		$agents = ( new m_commission_recruitment_agent )->getAgents( false, $from, $to );

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/commissions/recruitment-agents.js'
		];

		$this->setVariables([
				'agents' => $agents,
				'queryString'=> $queryString,
			])
			->setTitle('Recruitment Agents commissions')
			->renderPage('commissions/recruitment-agents');
	}

	public function marketing_agency_all( $agencyId )
	{
		$from = $to = null;
		$queryString = '';

		if ( isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ) {
			$from = $_GET['filter']['from'];
			$to   = $_GET['filter']['to'];

			$queryString = '?filter[from]='.$from.'&filter[to]='.$to;
		}

		$this->load->model( 'm_commission_marketing_agency' );

		if ( isset( $_GET['paid-all'] ) ) {

			$voucher     = ( new m_commission_marketing_agency )->paidAll( $agencyId, $from, $to );

			if ( $voucher ) {
				Message::addSuccess( 'All commission of <strong>'.$voucher['marketing-agency']['agency_name'].'</strong> '
									.'from '.fdate( 'M-d-Y', $from ).' to '.fdate( 'M-d-Y', $to ).' has been mark as paid.'
									.'<br>Total amount paid: <strong>&#8369;&nbsp;'.number_format($voucher['voucher_amount'], 2).'</strong>.'
									.'<br>Generated voucher#: <strong>'.$voucher['voucher_number'].'</strong>', 
									false, 'Success!' );
				Message::addModalSuccess( '<br>All commission of <strong>'.$voucher['marketing-agency']['agency_name'].'</strong> '
										.'from '.fdate( 'M-d-Y', $from ).' to '.fdate( 'M-d-Y', $to ).' has been mark as paid.'
										.'<br><br>Total amount paid: <strong>&#8369;&nbsp;'.number_format($voucher['voucher_amount'], 2).'</strong>.', 
										'Generated voucher# '.$voucher['voucher_number'].'.' );
				
				redirect( site_url( 'admin/commissions/marketing-agency-all/'.$agencyId .$queryString ) );
				exit;
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/commissions/marketing-agency-all/'.$agencyId .$queryString ) );
			exit;
		}

		if ( isset( $_GET['paid'] ) && is_numeric($_GET['paid'] ) ) {
			$commissionId = (int) $_GET['paid'];

			$voucher = ( new m_commission_marketing_agency )->markAsPaid( $commissionId );

			if ( $voucher ) {
				Message::addSuccess( 'Commission to worker #'.$voucher['voucher_applicant'].' has been closed. <br>Generated voucher#: <strong>'.$voucher['voucher_number'].'</strong>', 
						false, 'Success!' );
				Message::addModalSuccess( '<br>Commission to worker #'.$voucher['voucher_applicant'].' has been closed.', 'Generated voucher# '.$voucher['voucher_number'].'.' );
				redirect( site_url( 'admin/commissions/marketing-agency-all/'.$agencyId . $queryString ) );
				exit;
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/commissions/marketing-agency-all/'.$agencyId . $queryString ) );
			exit;
		}

		$this->load->model( 'm_marketing_agency' );
		$agency  = ( new m_marketing_agency )->getMarketingAgencyById( $agencyId );
		$details = ( new m_commission_marketing_agency )->getCommissionDetails( $agencyId, false, $from, $to );

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/commissions/marketing-agencies.js'
		];

		$this->setVariables([
				'agency' => $agency,
				'details'=> $details,
			])
			->setTitle($agency['agency_name'])
			->renderPage('commissions/marketing-agency-all');
	}

	public function marketing_agent_all( $agentId )
	{
		$from = $to = null;
		$queryString = '';

		if ( isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ) {
			$from = $_GET['filter']['from'];
			$to   = $_GET['filter']['to'];

			$queryString = '?filter[from]='.$from.'&filter[to]='.$to;
		}

		$this->load->model( 'm_commission_marketing_agent' );

		if ( isset( $_GET['paid-all'] ) ) {

			$voucher     = ( new m_commission_marketing_agent )->paidAll( $agentId, $from, $to );

			if ( $voucher ) {
				
				Message::addSuccess( 
					'All commission of <strong>'.$voucher['marketing-agent']['agent_first'].' '.$voucher['marketing-agent']['agent_last'].'</strong> '
					.'from '.fdate( 'M-d-Y', $from ).' to '.fdate( 'M-d-Y', $to ).' has been mark as paid.'
					.'<br>Total amount paid: <strong>&#8369;&nbsp;'.number_format($voucher['voucher_amount'], 2).'</strong>.'
					.'<br>Generated voucher#: <strong>'.$voucher['voucher_number'].'</strong>', 
					false, 'Success!' );

				Message::addModalSuccess( 
					'<br>All commission of <strong>'.$voucher['agent_first'].' '.$voucher['agent_last'].'</strong> '
					.'from '.fdate( 'M-d-Y', $from ).' to '.fdate( 'M-d-Y', $to ).' has been mark as paid.'
					.'<br><br>Total amount paid: <strong>&#8369;&nbsp;'.number_format($voucher['voucher_amount'], 2).'</strong>.', 
					'Generated voucher# '.$voucher['voucher_number'].'.' );

				redirect( site_url( 'admin/commissions/marketing-agent-all/'.$agentId .$queryString ) );
				exit;
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/commissions/marketing-agent-all/'.$agentId .$queryString ) );
			exit;
		}

		if ( isset( $_GET['paid'] ) && is_numeric($_GET['paid'] ) ) {
			$commissionId = (int) $_GET['paid'];

			$voucher = ( new m_commission_marketing_agent )->markAsPaid( $commissionId );

			if ( $voucher ) {

				Message::addSuccess( 
					'Commission to worker #'.$voucher['voucher_applicant'].' has been closed. '
					.'<br>Generated voucher#: <strong>'.$voucher['voucher_number'].'</strong>', 
					false, 'Success!' );

				Message::addModalSuccess( '<br>Commission to worker #'.$voucher['voucher_applicant'].' has been closed.', 
					'Generated voucher# '.$voucher['voucher_number'].'.' );
				redirect( site_url( 'admin/commissions/marketing-agent-all/'.$agentId . $queryString ) );
				exit;
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/commissions/marketing-agent-all/'.$agentId . $queryString ) );
			exit;
		}

		$this->load->model( 'm_marketing_agent' );
		$agent   = ( new m_marketing_agent )->getMarketingAgentById( $agentId );
		$details = ( new m_commission_marketing_agent )->getCommissionDetails( $agentId, false, $from, $to );

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/commissions/marketing-agents.js'
		];

		$this->setVariables([
				'agent' => $agent,
				'details'=> $details,
			])
			->setTitle( $agent['agent_first'].' '.$agent['agent_last'] )
			->renderPage('commissions/marketing-agent-all');
	}

	public function recruitment_agent_all( $agentId )
	{
		$from = $to = null;
		$queryString = '';

		if ( isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ) {
			$from = $_GET['filter']['from'];
			$to   = $_GET['filter']['to'];

			$queryString = '?filter[from]='.$from.'&filter[to]='.$to;
		}

		$this->load->model( 'm_commission_recruitment_agent' );

		if ( isset( $_GET['paid'] ) && is_numeric($_GET['paid'] ) ) {
			$commissionId = (int) $_GET['paid'];

			$commission = ( new m_commission_recruitment_agent )->markAsPaid( $commissionId );

			if ( $commission ) {
				Message::addSuccess( 'Commission to worker #'.$commission['commission_applicant'].' has been closed.' );
				redirect( site_url( 'admin/commissions/recruitment-agent-all/'.$agentId . $queryString ) );
				exit;
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/commissions/recruitment-agent-all/'.$agentId . $queryString ) );
			exit;
		}

		$this->load->model( 'm_recruitment_agent' );
		$agent   = ( new m_recruitment_agent )->getRecruitmentAgentById( $agentId );
		$details = ( new m_commission_recruitment_agent )->getCommissionDetails( $agentId, false, $from, $to );

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] . 'pages/commissions/recruitment-agents.js'
		];

		$this->setVariables([
				'agent' => $agent,
				'details'=> $details,
			])
			->setTitle( $agent['agent_first'].' '.$agent['agent_last'] )
			->renderPage('commissions/recruitment-agent-all');
	}

	public function recruitment_agent_payment( $commissionId )
	{
		$this->load->model( 'm_commission_recruitment_agent' );

		if ( isset( $_POST['payment']['amount'] ) ) {

			$commission = ( new m_commission_recruitment_agent )->paid( $commissionId );

			if ( $commission ) {
				Message::addSuccess( 'Commission to worker #'.$commission['commission_applicant'].' has been closed.' );
				redirect( site_url( 'admin/commissions/recruitment-agent-payment/'.$commissionId ) );
				exit;
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/commissions/recruitment-agent-payment/'.$commissionId ) );
			exit;
		}

		$commission = ( new m_commission_recruitment_agent )->getCommissionById( $commissionId );
		$worker     = ( new m_commission_recruitment_agent )->getWorker( $commission['commission_applicant'] );

		$this->load->model( 'm_recruitment_agent' );
		$agent      = ( new m_recruitment_agent )->getRecruitmentAgentById( $commission['commission_agent'] );

		$this->load->model( 'm_voucher' );
		$voucher = ( new m_voucher )->generate();
		
		$this->setVariables([
				'commission' => $commission,
				'voucher'    => $voucher,
				'agent'      => $agent,
				'worker'     => $worker,
			])
			->setTitle( $worker['applicant_first'].' '.$worker['applicant_last'] )
			->renderPage('commissions/recruitment-agent-payment');
	}

	public function recruitment_agent_payment_all( $agentId )
	{
		$from = $to = null;
		$queryString = '';

		if ( isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ) {
			$from = $_GET['filter']['from'];
			$to   = $_GET['filter']['to'];

			$queryString = '?filter[from]='.$from.'&filter[to]='.$to;
		}

		$this->load->model( 'm_commission_recruitment_agent' );

		if ( isset( $_POST['payment']['amount'] ) ) {

			$voucher = ( new m_commission_recruitment_agent )->paidAll( $agentId, $from, $to );

			if ( $voucher ) {
				
				Message::addSuccess( 
					'All commission of <strong>'.$voucher['recruitment-agent']['agent_first'].' '.$voucher['recruitment-agent']['agent_last'].'</strong> '
					.'from '.fdate( 'M-d-Y', $from ).' to '.fdate( 'M-d-Y', $to ).' has been mark as paid.'
					.'<br>Total amount paid: <strong>&#8369;&nbsp;'.number_format($voucher['voucher_amount'], 2).'</strong>.'
					.'<br>Generated voucher#: <strong>'.$voucher['voucher_number'].'</strong>', 
					false, 'Success!' );
				
				Message::addModalSuccess( 
					'<br>All commission of <strong>'.$voucher['recruitment-agent']['agent_first'].' '.$voucher['recruitment-agent']['agent_last'].'</strong> '
					.'from '.fdate( 'M-d-Y', $from ).' to '.fdate( 'M-d-Y', $to ).' has been mark as paid.'
					.'<br><br>Total amount paid: <strong>&#8369;&nbsp;'.number_format($voucher['voucher_amount'], 2).'</strong>.', 
					'Generated voucher# '.$voucher['voucher_number'].'.' );

				redirect( site_url( 'admin/commissions/recruitment-agent-all/'.$agentId . $queryString ) );
				exit; 
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/commissions/recruitment-agent-payment-all/'. $agentId . $queryString ) );
			exit;
		}

		$this->load->model( 'm_recruitment_agent' );
		$agent      = ( new m_recruitment_agent )->getRecruitmentAgentById( $agentId );

		$workers    = ( new m_commission_recruitment_agent )->getWorkers( $agentId, $from, $to );

		//If no more workers with commisison, redirect back
		if ( count( $workers ) == 0 ) {
			redirect( site_url( 'admin/commissions/recruitment-agent-all/'.$agentId . $queryString ) );
			exit;
		}

		$this->load->model( 'm_voucher' );
		$voucher = ( new m_voucher )->generate();
		
		$this->setVariables([
				'voucher'     => $voucher,
				'agent'       => $agent,
				'workers'     => $workers,
				'queryString' => $queryString,
			])
			->setTitle( $agent['agent_first'].' '.$agent['agent_last'] )
			->renderPage('commissions/recruitment-agent-payment-all');
	}
    
    public function employer()
    {
        Pagination::init( 50 );
        
        $sqlLimit  = Pagination::getPerPage();
        $sqlOffset = Pagination::getRecordCursor();
        
        $this->load->model( 'm_commission_employer' );

        $commissionOptions['where'][] = [
            'commission_status' => 0,
        ];
        $commissions      = ( new m_commission_employer )
							->getCommissions( $commissionOptions, $sqlLimit, $sqlOffset );
                        
        $commissionsCount = ( new m_commission_employer )
                            ->getCommissionsCount( $commissionOptions );
		
		Pagination::setOptions([
			'total-records' => $commissionsCount,
		]);

        $this->styles = [
			$this->getPath()['styles'] . 'pages/commissions/employer.css',
		];
		
		$this->scripts = [
			$this->getPath()['scripts'] . 'pages/commissions/employer.js',
		];

		$this->setVariables([
				'commissions'	    => $commissions,
                'commissionsCount'	=> $commissionsCount,
				'paginationHTML'    => Pagination::generateHTML(),
				'paginationCounter' => Pagination::getCounters(),
			])
			->setTitle('Agent\'s Commissions')
			->renderPage('commissions/employer');
    }
}

/* End of file commissions.php */
/* Location: ./app/controllers/admin/commissions.php */