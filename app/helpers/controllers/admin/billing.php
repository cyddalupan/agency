<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

require_once __DIR__.'/../../third_party/htmltopdf/html2pdf.class.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Billing extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		$this->load->model( 'm_billing');
	}
	
	public function index()
	{
		show_404();
	}

	public function transactions()
	{
		$this->load->model( 'm_or' );

		if ( isset( $_GET['search']['or'] ) ) {
			$ORs = ( new m_or )->searchORs();
		} else {
			$ORs = ( new m_or )->getORs();
		}

		$this->scripts = [
			$this->getPath()['scripts'] . 'pages/billing/transactions.js'
		];

		$this->setVariables([
				'ORs' => $ORs,
			])
			->setTitle('All transactions')
			->renderPage('billing/transactions');
	}

	public function or_review( $ORId )
	{
		$this->load->model( 'm_or' );

		if ( isset( $_POST['or'] ) ) {

			$OR = ( new m_or )->updateOR( $ORId );

			if ( $OR ) {
				Message::addSuccess( 'OR# <strong>'.$OR['or_number'].'</strong> has been updated.', 
									false, 'Success!' );
				
				redirect( site_url( 'admin/billing/transactions' ) );
				exit;
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/billing/transactions' ) );
			exit;
		}

		checkAJAX();
		
		$OR = ( new m_or )->getORById( $ORId, true );

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] .'pages/billing/or-review.js',
		];

		$this->setVariables([
				'or' => $OR,
			])
			->renderPage('billing/or-review', true);
	}

	public function or_revert( $ORId )
	{
		$this->load->model( 'm_or' );
		$OR = ( new m_or )->revertOR( $ORId );

		if ( $OR ) {
			Message::addSuccess( 'Transaction (#'.$OR['or_number'].') has been removed.', false, 'Success!' );
			redirect( site_url( 'admin/billing/transactions' ) );
			exit;
		}

		Message::addWarning( 'There was an error while processing your request. Please try again.' );
		redirect( site_url( 'admin/billing/transactions' ) );
		exit;
	}
	
	public function employers()
	{	
		$from = $to = null;
		$queryString = '';

		if ( isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ) {
			$from = $_GET['filter']['from'];
			$to   = $_GET['filter']['to'];

			$queryString = '?filter[from]='.$from.'&filter[to]='.$to;
		}

		$employers = ( new m_billing )->getEmployers( $from, $to );

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] .'pages/billing/employers.js',
		];
		//dd($employers);
		$this->setVariables([
				'employers'  => $employers,
				'queryString'=> $queryString,
			])
			->setTitle( 'Billing due to employers' )
			->renderPage( 'billing/employers' );
	}
	
	public function workers()
	{	

		$workers = ( new m_billing )->getWorkers( false );
		
		$this->setVariables([
				'workers'  => $workers,
			])
			->setTitle( 'Billing due to workers' )
			->renderPage( 'billing/workers' );
	}
	
	public function employer_soa( $employerId )
	{
		$from = $to = null;
		$queryString = '';

		if ( isset( $_GET['filter']['from'], $_GET['filter']['to'] ) ) {
			$from = $_GET['filter']['from'];
			$to   = $_GET['filter']['to'];

			$queryString = '?filter[from]='.$from.'&filter[to]='.$to;
		}

		if ( isset( $_GET['paid-all'] ) ) {

			$OR     = ( new m_billing )->paidAllEmployer( (int) $employerId, $from, $to );
 
			if ( $OR ) {
				Message::addSuccess( 'All remaining balance of <strong>'.$OR['employer_name'].'</strong> '
									.'from '.fdate( 'M-d-Y', $from ).' to '.fdate( 'M-d-Y', $to ).' has been mark as paid.'
									.'<br>Total amount paid: <strong>&#8369;&nbsp;'.number_format($OR['or_amount'], 2).'</strong>.'
									.'<br>Generated OR#: <strong>'.$OR['or_number'].'</strong>', 
									false, 'Success!' );
				Message::addModalSuccess( '<br>All remaining balance of <strong>'.$OR['employer_name'].'</strong> '
										.'from '.fdate( 'M-d-Y', $from ).' to '.fdate( 'M-d-Y', $to ).' has been mark as paid.'
										.'<br><br>Total amount paid: <strong>&#8369;&nbsp;'.number_format($OR['or_amount'], 2).'</strong>.', 
										'Generated OR# '.$OR['or_number'].'.' );
				
				redirect( site_url( 'admin/billing/employer-soa/'.$employerId .$queryString ) );
				exit;
			}

			Message::addWarning( 'There was an error while processing your request. Please try again.' );
			redirect( site_url( 'admin/billing/employer/'.$employerId .$queryString ) );
			exit;
		}
		
		$this->load->model( 'm_employer' );
		$employer = ( new m_employer )->getEmployerById( $employerId );

		$workers = ( new m_billing )->getEmployerWorkers( $employerId, false, $from, $to );
		
		foreach ( $workers as $applicantId => $worker ) {
			$workers[$applicantId]['breakdown'] = ( new m_billing )->getWorkerBreakdown( $applicantId, false );
		}

		$this->styles[] = $this->getPath()['styles'] .'pages/billing/employer-soa.css';

		$this->scripts = [
			$this->getPath()['plugins'] . 'datetime/bootstrap-datepicker.js',
			$this->getPath()['scripts'] .'pages/billing/employers.js',
		];

		$this->setVariables([
				'employer'  => $employer,
				'workers'   => $workers,
			])
			->setTitle( $employer['employer_name'] )
			->renderPage( 'billing/employers-soa' );
	}

	public function payment_employer( $billId )
	{
		if ( isset( $_POST['payment'] ) ) {

			$payment = ( new m_billing )->makePaymentEmployer( $billId );

			if ( ! empty( $payment ) ) {
				Message::addSuccess( 'Payment submitted.', false, 'OR#'.$payment['payment_or']);				
				redirect( site_url( 'admin/billing/payment-employer/'.$billId ) );
				exit;
			}

			Message::addWarning( 'Server not available. Please try again later.' );
			redirect( site_url( 'admin/billing/payment-employer/'.$billId ) );
			exit;
		}

		$bill    = ( new m_billing )->getBillById( $billId );
		$details = ( new m_billing )->getEmployerBillDetails( $billId );

		$this->load->model( 'm_employer' );
		$employer = ( new m_employer )->getEmployerById( $bill['bill_employer'] );
		$worker   = ( new m_billing )->getWorker( $bill['bill_applicant'] );

		$this->load->model( 'm_or' );
		$ORId     = (new m_or )->generate( (int) $billId );

		$this->setVariables([
				'employer' => $employer,
				'worker'   => $worker,
				'bill'     => $bill,
				'ORId'     => $ORId,
				'fees'     => $details,
			])
			->setTitle( $employer['employer_name'] )
			->renderPage( 'billing/payment-employer' );
	}

	public function payment_worker( $billId )
	{
		if ( isset( $_POST['payment'] ) ) {

			$payment = ( new m_billing )->makePaymentWorker( $billId );

			if ( ! empty( $payment ) ) {
				Message::addSuccess( 'Payment submitted.', false, 'OR#'.$payment['payment_or']);
				redirect( site_url( 'admin/billing/payment-worker/'.$billId ) );
				exit;
			}

			Message::addWarning( 'Server not available. Please try again later.' );
			redirect( site_url( 'admin/billing/payment-worker/'.$billId ) );
			exit;
		}

		$bill     = ( new m_billing )->getBillById( $billId );
		$details  = ( new m_billing )->getWorkerBillDetails( $billId );
		$worker   = ( new m_billing )->getWorker( $bill['bill_applicant'] );
		
		$this->load->model( 'm_or' );
		$ORId     = (new m_or )->generate( (int) $billId );

		$this->setVariables([
				'worker'   => $worker,
				'bill'     => $bill,
				'ORId'     => $ORId,
				'fees'     => $details,
			])
			->setTitle( $worker['applicant_first'].' '.$worker['applicant_last'] )
			->renderPage( 'billing/payment-worker' );
	}
	
	public function worker_soa( $applicantId )
	{	
		$worker  = ( new m_billing )->getWorker( $applicantId );

		if ( isset( $_POST['placement-fee']['amount'] ) ) {
			$placementFee = ( new m_billing )->updatePlacementFee( $applicantId );

			if ( $placementFee ) {
				Message::addSuccess('Placement fee was changed.');
				redirect( site_url( 'admin/billing/worker-soa/'.$worker['applicant_slug'] ) );
				exit;
			}

			Message::addDanger('Cannot edit the placement fee.');
			redirect( site_url( 'admin/billing/worker-soa/'.$worker['applicant_slug'] ) );
			exit;
		}

		$bill    = ( new m_billing )->getBillByWorkerId( $applicantId );
		$details = ( new m_billing )->getWorkerBillDetails( $bill['bill_id'], false );

		$this->styles[] = $this->getPath()['styles'] .'pages/billing/worker-soa.css';
		$this->scripts[] = $this->getPath()['scripts'] .'pages/billing/worker-soa.js';

		$this->setVariables([
				'worker'  => $worker,
				'bill'    => $bill,
				'fees'    => $details,
			])
			->setTitle( $worker['applicant_first'].' '.$worker['applicant_last'] )
			->renderPage( 'billing/worker-soa' );
	}
    
    public function receipt( $orNumber )
    {
    	$this->load->model( 'm_or' );
    	$or = ( new m_or )->getOR( $orNumber );
    	
	    ob_start();
        $this->renderPage('applicants/pdf', true);
        $content = ob_get_clean();

		try {
		    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
		    $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
		    $html2pdf->Output('or-pdf-'.time().'.pdf');
		} catch( HTML2PDF_exception $e ) {
		    echo $e;
		    exit;
		}
    }
     
}

/* End of file commissions.php */
/* Location: ./app/controllers/admin/commissions.php */