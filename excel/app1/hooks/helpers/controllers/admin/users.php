<?php
use \Application\Message as Message;

defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends Admin_Controller {
	
	const PAGE_ACCESS = parent::PAGE_PRIVATE;
	
	public function __construct() 
	{
		parent::__construct();
		
		//Check page permission
		$this->checkPageAccess( self::PAGE_ACCESS );
		
		$this->load->model( 'm_user' );
	}
	
	public function index()
	{
		show_404();
	}
	
	public function all()
	{
		$post = isset( $_SESSION['post']['admin']['users/all'] ) ? $_SESSION['post']['admin']['users/all'] : [];
		
		$options = [];

		if ( $_SESSION['admin']['user']['user_type'] != 4 ) {
			$options['where'][] = [
				'user_type !=' => 4,
			];
		}

		$users     = $this->m_user->getUsers( $options, 0, 0, ['user_type', 'ASC'] );
        $userTypes = $this->m_user->getUserTypes();

        $options['where'][] = [
        	'employer_user <=' => 0,
        ];

        $this->load->model( 'm_employer' );
        $employers = ( new m_employer )->all( $options );

		$this->scripts = [
			$this->getPath()['plugins'] . 'select2/select2.js',
			$this->getPath()['plugins'] . 'bootbox/bootbox.js',
			$this->getPath()['scripts'] . 'pages/users.js',
		];		
		
		$this->modalsTpl = 'users.modal.php';
		$this->setVariables([
				'users'            => $users,
                'userTypes'        => $userTypes,
                'employers'        => $employers,
				'post'             => $post,
			])
			->setTitle('All users')
			->renderPage('users');
	}

	public function add()
	{
		//Form Submitted
		if ( isset( $_POST['user'] ) ) {
			
			$_SESSION['post']['admin']['users/all'] = $_POST;
		
			self::checkDataAdd();

			$user = ( new m_user )->addUser();
			
			if ( $user ) {
				Message::addModalSuccess('New user record has been added successfully!', 'Success');
				
				redirect( site_url( $this->controllerDir . 'users/all' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', 'Oops!');
			redirect( site_url( $this->controllerDir . 'users/all' ) );
			exit;
		}
		//endOf: Form Submitted

		redirect( site_url( $this->controllerDir . 'users/all' ) );
		exit;
	}

	public function edit()
	{
		//Form Submitted
		if ( isset( $_POST['user'] ) ) {

			$userId = $_POST['user']['id'];
			
			$_SESSION['post']['admin']['users/edit'] = $_POST;
		
			$user = ( new m_user )->update( $userId );
			
			if ( $user ) {
				Message::addModalSuccess('Changes successfully saved!', 'Success');
				
				redirect( site_url( $this->controllerDir . 'users/all' ) );
				exit;
			}
			
			Message::addWarning('An unknown error has occur. Server not available. Please try again.', 'Oops!');
			redirect( site_url( $this->controllerDir . 'users/all' ) );
			exit;
		}
		//endOf: Form Submitted

		redirect( site_url( $this->controllerDir . 'users/all' ) );
		exit;
	}

	
	protected static function checkDataAdd()
	{
		$errors 	= [];
		$returnUrl 	= site_url( 'admin/users/all' );
		$user	 	= $_POST['user'];
 
		if ( empty( $user['fullname']  ) ) {
			$errors[] = '* <strong>Full name</strong> is required.';
		}
		
		if ( empty( $user['email']  ) ) {
			$errors[] = '* <strong>E-mail address</strong> is required.';
		}

		if ( empty( $user['name']  ) ) {
			$errors[] = '* <strong>Username</strong> is required.';
		}

		if ( empty( $user['password'] ) ) {
			$errors[] = '* <strong>Password</strong> is required.';
		}

		if ( count( $errors ) > 0 ) {
			Message::addWarning('Please check the following requirements:<br><br>' . implode( '<br>', $errors ), false, 'Oops!');

			redirect( $returnUrl );
			exit;
		}		
	}
	

}

/* End of file applicants.php */
/* Location: ./app/controllers/admin/applicants.php */