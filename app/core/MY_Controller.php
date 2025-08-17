<?php //-->
use \Application\Message as Message;

/**
 * MY_Controller
 *
 * @package		ICS-IPAC
 * @author		Clemente B. Quiñones Jr. <clemquinones@gmail.com>
 * @copyright	Copyright (c) 2014, iWebPhilippines (http://iwebphilippines.com/)
 * @since		Version 1.0
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller
 */
class MY_Controller extends CI_Controller {
	/* Constants	
	------------------------------------------------------*/
	const PAGE_PUBLIC   = 0; //No valid access is required
	const PAGE_PRIVATE  = 1; //Valid access is required
	const PAGE_DYNAMIC  = 2; //User-defined
	const PAGE_NOUSER   = 3; //No valid access

	public $system   = null; //admin | employer

	public $data        = [];

	protected $title    = '';
	protected $path     = [];
	protected $info     = [];

	protected $templateDir      = '/';
	public    $modelDir            = '/';
	protected $controllerDir    = '/';

	protected $variables    = [];

	protected $styles       = [];
	protected $scripts      = [];

	protected $headerTpl    = '_header';
    protected $menuTpl      = '_menu';
	protected $sidebarTpl   = '_sidebar';
	protected $alertsTpl    = '_alerts';
	protected $template     = '';
	protected $pageLayout   = '_layout';
	protected $modalsTpl    = '';
	protected $footerTpl    = '_footer';
    
    protected $user         = null;

	public function __construct() 
	{
		parent::__construct(); 

		$this->load->driver('cache');

		$directories = explode( '/', uri_string() );
		$system = rtrim( $directories[0] , '/'); 

		//make sure page has a settings
		if(!isset($_SESSION["settings"])){
			$this->session->set_flashdata('prev_url', current_url());
			redirect('add_settings');
		}

		if ( $system == 'admin' && count( $directories ) == 1 ) {
			if ( !isset( $_SESSION['admin']['user'] ) ) {
				redirect( site_url('admin/signin') );
				exit;
			}

			redirect( site_url('admin/dashboard') );
			exit;
		}
		
		if ( $system == 'employer' && count( $directories ) == 1 ) {
			if ( ! isset( $_SESSION['employer']['user'] ) ) {
				redirect( site_url('employer/signin') );
				exit;
			}
			
			redirect( site_url('employer/dashboard') );
			exit;
		}
		
		if ( $system == 'public' && count( $directories ) == 1 ) {
			redirect( site_url('public/applicants') );
			exit;
		}
		
		//Truncate system info
		$this->info = [
			'applicationName'          => $_SESSION["settings"]['client_full'],
			'applicationDescription'   => $_SESSION["settings"]['client_full'],
			'author'                   => '',
			'email'                    => '',
		];
		
		$this->path =  [
			'assets'    => base_url().'assets/',
			'images'	=> base_url().'assets/images/',
			'styles'	=> base_url().'assets/styles/',
			'scripts'	=> base_url().'assets/scripts/',
			'fonts'		=> base_url().'assets/fonts/',
			'plugins'	=> base_url().'assets/plugins',
			'sounds'	=> base_url().'assets/sounds/',
		];

		$this->variables['app']    = $this;		
		
		$this->setSessionName( $this->config->item('sess_cookie_name') );
		
		$this->load->model('m_generic');
	}
	
	//for SET and GET anonymous method
	public function __call($method, $value)
	{
		if ( strlen($method) <= 3 ) {
			throw new Exception('Undefine method '.$method.' under class '.__CLASS__.'.');
		}

		$process = strtolower( substr($method, 0, 3) );
		$property = strtolower( substr($method, 3) );

		if ( $process == 'set' ) {

			//Lowercase property
			if ( isset($this->{$property}) ) {
				
				if ( ! is_null( $this->{$property} ) && is_array($this->{$property}) ) {
					$this->{$property} = array_merge( $this->{$property}, $value[0]);
					return $this; 
				}
				
				$this->{$property} = $value[0];
				return $this;
			}
			
			$oldValue = $this->{'get'.$property}();

			if ( ! is_null($oldValue) && is_array($oldValue) ) {
				$this->data[$property] = array_merge( $oldValue, $value[0]);
				
				return $this; 
			}
			
			$this->data[$property] = $value[0];
			return $this;
		}

		if ($process == 'get')  {
			
			if ( isset( $this->{$property} ) ) {
				return $this->{$property}; 
			}
			
			if ( isset( $this->data[$property] ) ) {
				return $this->data[$property];
			}
			
			return null;
		}
		
		if ($process == 'add')  {
			$this->data[$property][] = $value[0];
		}		
 	}
	
	/**
	 * Abstract methods
	 *
	*/
	//abstract protected function renderPage($template, $fullPage = false);
	
	/**
	 * Public methods
	 *
	*/
	public function renderHeader()
	{
		if ( empty( $this->headerTpl) ) {
			return null;
		}

		$this->load->view($this->templateDir . $this->headerTpl);
	}

	public function renderStyles()
	{
		if ( count( $this->styles ) == 0 ) {
			echo '';
			return;
		}

		foreach ($this->styles as $style) {
			echo sprintf("<link href=\"%s\" type=\"text/css\" rel=\"stylesheet\">\n", $style);
		}
	}
    
    public function renderMenus()
	{
		if ( empty( $this->menuTpl ) ) {
			return null;
		}

		$this->load->view($this->templateDir . $this->menuTpl);
	}
	
	public function renderSideBar()
	{
		if ( empty( $this->sidebarTpl ) ) {
			return null;
		}

		$this->load->view($this->templateDir . $this->sidebarTpl);
	}

	public function renderAlerts()
	{
		if ( empty( $this->alertsTpl ) ) {
			return null;
		}

		$this->load->view($this->templateDir . $this->alertsTpl);
	}

	public function renderTemplate()
	{
		if ( empty( $this->template ) ) {
			return null;
		}

		$this->load->view($this->templateDir . $this->template);
	}

	public function renderModals()
	{
		if ( empty( $this->modalsTpl ) ) {
			return null;
		}

		$this->load->view( $this->templateDir . $this->modalsTpl );
	}

	public function renderFooter()
	{
		if ( empty( $this->footerTpl ) ) {
			return null;
		}

		$this->load->view($this->templateDir . $this->footerTpl);
	}

	public function renderScripts()
	{
		if ( count( $this->scripts ) == 0 ) {
			echo '';
			return;
		}

		foreach ($this->scripts as $script) {
			echo sprintf("<script type=\"text/javascript\" src=\"%s\"></script>\n", $script);
		}
	}
	
	/**
	 * Protected methods
	 *
	*/
	protected function setVariables($data)
	{
		$this->variables = array_merge($this->variables, $data);

		return $this;
	}

	protected function setTitle($title, $withTrailingAppName = true)
	{
		$this->title = $title . ( $withTrailingAppName ? ' - '. $this->info['applicationName'] : '' );
		return $this;
	}
}


/**
 * admin_Controller
 *
 * Control Panel End
 */
class Admin_Controller extends MY_Controller {
	
	public function __construct() 
	{
		parent::__construct(); 
		$this->system = 'admin';
        
        $this->user = isset( $_SESSION['admin']['user'] ) ? $_SESSION['admin']['user'] : null;
		
		//If logged out triggered
		if ( isset( $_GET['logout'] ) ) {
			unset ( $_SESSION['admin']['user'] );

			session_regenerate_id();

			Message::addWarning('You have been logged out.', 10);

			redirect( site_url('admin/signin') );
			exit;
		}
		
		$this->templateDir 		= 'admin/';
		$this->modelDir 		= 'admin/';
		$this->controllerDir 	= 'admin/';

		$this->setPath([
			'images'	=> base_url().'assets/images/admin/',
			'styles'	=> base_url().'assets/styles/admin/',
			'scripts'	=> base_url().'assets/scripts/admin/',
			'fonts'		=> base_url().'assets/fonts/admin/',
			'plugins'	=> base_url().'assets/plugins',
			'sounds'	=> base_url().'assets/sounds/admin/',
		]);
		
		$this->info['applicationName']	= 'Control Panel'; 
		
		//Make sure to have a trailing slash
		$this->templateDir      = rtrim( $this->templateDir, '/' ) . '/';
		$this->modelDir         = rtrim( $this->modelDir, '/' ) . '/';
		$this->controllerDir    = rtrim( $this->controllerDir, '/' ) . '/';
		
		foreach ($this->path as $key => $path) {
			$this->path[$key] = rtrim( $path, '/' ) . '/';
		}
	}
    
   protected function setVariables($data)
	{
		parent::setVariables( $data );
        
        if ( empty( $this->headerTpl ) ) {
            return $this;
        }

		if ( ! $notification = $this->cache->file->get('notification'))
		{

	        $this->load->model( 'm_notification' );

	        $notification = $this->m_notification->getNotifications();

	        //count for review
	        $this->db->where('applicant_status', 10); 
	        $this->db->from('applicant');
			$notification['4reviewCount'] =  $this->db->count_all_results();


			$this->cache->file->save('notification', $notification, 300);
		}
	    
		parent::setVariables([
            'notification' => $notification,
        ]);

		return $this;
	}
	
	protected function renderPage($template, $fullPage = false)
	{
		$this->template = $template;

		if ($fullPage === true) {
			$this->pageLayout = $template;
		}
		
		//Capture all alert messages
		$messages = Message::all();
		$this->setMessages($messages);

		$this->load->view($this->templateDir . $this->pageLayout, $this->variables);
	}
	
	protected function checkPageAccess($pageAccess) 
	{
		$access = [
            parent::PAGE_PUBLIC, 
            parent::PAGE_PRIVATE, 
            parent::PAGE_DYNAMIC, 
            parent::PAGE_NOUSER
        ];
		
		if ( !in_array( $pageAccess, $access ) ) {
			throw new Exception('No page access defined for controller.');
		}
		
		switch ( true ) {
			case $pageAccess == parent::PAGE_PRIVATE && ! isset( $_SESSION['admin']['user'] ):
							
				Message::addWarning('Log in is required');
				
				$returnUri = uri_string();
				
				redirect(site_url('admin/signin') . '?return_uri=' . ( $returnUri ? $returnUri : '' ) );
				break;
			case $pageAccess == parent::PAGE_PUBLIC:
				return;
				//no break
			case $pageAccess == parent::PAGE_DYNAMIC: 
				return;
			case $pageAccess == parent::PAGE_NOUSER && isset( $_SESSION['admin']['user'] ):
				return;
		}
	}
}
/* End of class admin_Controller */




/**
 * Employer_Controller
 *
 * Employer End
 */

class Employer_Controller extends MY_Controller {
	
	public function __construct() 
	{
		parent::__construct(); 
		$this->system = 'employer';
        
        $this->user = isset( $_SESSION['employer']['user'] ) ? $_SESSION['employer']['user'] : null;
		
		//If logged out triggered
		if ( isset( $_GET['logout'] ) ) {
			unset ( $_SESSION['employer']['user'] );

			Message::addWarning('You have been logged out.', 10);

			redirect( site_url('employer/signin') );
			exit;
		}
		
		$this->templateDir 		= 'employer/';
		$this->modelDir 		= 'employer/';
		$this->controllerDir 	= 'employer/';

		$this->setPath([
			'images'	=> base_url().'assets/images/employer/',
			'styles'	=> base_url().'assets/styles/employer/',
			'scripts'	=> base_url().'assets/scripts/employer/',
			'fonts'		=> base_url().'assets/fonts/employer/',
			'plugins'	=> base_url().'assets/plugins',
			'sounds'	=> base_url().'assets/sounds/employer/',
		]);
		
		$this->info['applicationName']	= $_SESSION["settings"]['client_full']; 
		
		//Make sure to have a trailing slash
		$this->templateDir 		= rtrim( $this->templateDir, '/' ) . '/';
		$this->modelDir 		= rtrim( $this->modelDir, '/' ) . '/';
		$this->controllerDir 	= rtrim( $this->controllerDir, '/' ) . '/';
		
		foreach ($this->path as $key => $path) {
			$this->path[$key] = rtrim( $path, '/' ) . '/';
		}
	}
	
	protected function renderPage($template, $fullPage = false)
	{
		$this->template = $template;

		if ($fullPage === true) {
			$this->pageLayout = $template;
		}
		
		//Capture all alert messages
		$messages = Message::all();
		$this->setMessages($messages);

		$this->load->view($this->templateDir . $this->pageLayout, $this->variables);
	}
    
    protected function checkPageAccess($pageAccess) 
	{
		$access = [
            parent::PAGE_PUBLIC, 
            parent::PAGE_PRIVATE, 
            parent::PAGE_DYNAMIC, 
            parent::PAGE_NOUSER
        ];
		
		if ( ! in_array( $pageAccess, $access ) ) {
			throw new Exception('No page access defined for controller.');
		}
		
		switch ( true ) {
			case $pageAccess === parent::PAGE_PRIVATE && ! isset( $_SESSION['employer']['user'] ):
							
				Message::addWarning('Log in is required');
				
				$returnUri = uri_string();
				
				redirect(site_url('employer/signin') . '?return_uri=' . ( $returnUri ? $returnUri : '' ) );
				break;
			case $pageAccess == parent::PAGE_PUBLIC:
				return;
				//no break
			case $pageAccess == parent::PAGE_DYNAMIC: 
				return;
			case $pageAccess == parent::PAGE_NOUSER && isset( $_SESSION['employer']['user'] ):
				return;
		}
	}
}
/* End of class Public_Controller */

/**
 * Public_Controller
 *
 * Front End
 */

class Public_Controller extends MY_Controller {
	
	public function __construct() 
	{
		parent::__construct(); 
		
		$this->templateDir 		= 'public/';
		$this->modelDir  		= 'public/';
		$this->controllerDir 	= 'public/';

		$this->setPath([
			'images'	=> base_url().'assets/images/admin/',
			'styles'	=> base_url().'assets/styles/admin/',
			'scripts'	=> base_url().'assets/scripts/admin/',
			'fonts'		=> base_url().'assets/fonts/admin/',
			'plugins'	=> base_url().'assets/plugins',
			'sounds'	=> base_url().'assets/sounds/admin/',
		]);
		
		$this->info['applicationName']	= $_SESSION["settings"]['client_full']; 
		
		//Make sure to have a trailing slash
		$this->templateDir 		= rtrim( $this->templateDir, '/' ) . '/';
		$this->modelDir 		= rtrim( $this->modelDir, '/' ) . '/';
		$this->controllerDir 	= rtrim( $this->controllerDir, '/' ) . '/';
		
		foreach ($this->path as $key => $path) {
			$this->path[$key] = rtrim( $path, '/' ) . '/';
		}
	}
	
	protected function renderPage($template, $fullPage = false)
	{
		$this->template = $template;

		if ($fullPage === true) {
			$this->pageLayout = $template;
		}

		//Capture all alert messages
		$messages = Message::all();
		
		$this->setMessages($messages);

		$this->load->view($this->templateDir . $this->pageLayout, $this->variables);
	}

}
/* End of class Public_Controller */


/* End of file MY_Controller.php */
/* Location: ./app/core/MY_Controller.php */