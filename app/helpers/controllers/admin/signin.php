<?php
use \Application\Message as Message;

defined('BASEPATH') OR exit('No direct script access allowed');

class Signin extends Admin_Controller {
	
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
		
		if ( isset( $_SESSION['admin']['user'] ) ) {
			redirect( site_url( 'employer/dashboard' ) );
			exit;
		}
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );

		$this->load->model( $this->modelDir . 'm_signin');
	}
	
	public function index()
	{	
		if ( isset( $_POST['login'] ) ) {
            
			$login     = $_POST['login'];
			$loggedIn  = $this->m_signin->login( $login['username'], $login['password'] );
            $returnUri = isset($_GET['return_uri']) ? '?return_uri='.$_GET['return_uri'] : '';
			
			if ( $loggedIn === true ) {
				$user = $_SESSION['admin']['user'];
				
				Message::addModalSuccess('Welcome back '.$user['user_fullname'] . '!');
				
                $url = site_url( ! empty( $returnUri ) ? $_GET['return_uri'] : 'admin/dashboard');
			    redirect( $url );
				exit;
			} elseif ( $loggedIn === false ) {
								
				Message::addModalWarning('User not found. Invalid username or password.');
				
                redirect( site_url('admin/signin') . $returnUri);
				exit;
			} elseif ( $loggedIn === 0 ) {
				Message::addDanger('Your user account is deactivated. Please contact administrator regarding this issue.');
				
				redirect( site_url('admin/signin') . $returnUri);
				exit;
			} elseif ( $loggedIn === 1 ) {
				Message::addModalWarning('Your user account is not belong to this system. Please login to this link: <a href="'.site_url('employer/signin').'">'.site_url('employer').'</a>');
				
				redirect( site_url('admin/signin') . $returnUri);
				exit;
			}
		}

		$this->styles[] = $this->getPath()['styles'] . 'pages/signin.css';
		
		$this->setTitle('Sign in to ICS IPAC Control Panel', false)
			->renderPage('signin', true); //Full Page
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/Welcome.php */