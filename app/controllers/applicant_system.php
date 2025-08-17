<?php
use \Application\Message as Message;
use \Application\Pagination as Pagination;

class applicant_system extends Admin_Controller {

	const PAGE_ACCESS = parent::PAGE_PRIVATE;

	public function __construct() 
	{
		parent::__construct();
		$this->load->library('session');
		
		$this->load->model( 'm_applicant' );
		$this->load->model( 'cyd_currency' );
		$this->load->model( 'Cyd_Applicants_Alphatomo' );
		$this->load->model( 'Custom_Fields' );
		$this->load->model( 'Custom_Fields_Value' );
	}

	public function index()
	{
		$applicant_id = $this->session->userdata('applicant_id');
		//check login
		if(empty($applicant_id)){
			redirect('applicant_system/applicant-login');
			die();
		}
		
		$_SESSION['admin']['user']['user_id'] = 9999;
		$_SESSION['admin']['user']['user_type'] = 9999;

    	$data['pageTitle'] = 'Applicant Review';
		$data['applicant_id'] = $applicant_id;

		$this->load->view('header',$data);
		$this->load->view('applicant-review',$data);
		$this->load->view('footer',$data);

	}

	public function applicant_login(){
		
    	$data['pageTitle'] = 'Applicant Login';
    	$data['loginError'] = $this->session->flashdata('login_error');

		$this->load->view('header',$data);
		$this->load->view('applicant-login',$data);
		$this->load->view('footer');
	}

	public function applicant_logout(){
		
    	$this->session->sess_destroy();
    	redirect('applicant_system/applicant-login');
	}

	public function applicant_login_submit(){
		

		$query = $this->db->get_where('applicant', array('applicant_email' => $this->input->post('email')));

		//validate email
		if(count($query->result()) == 0){
			$this->session->set_flashdata('login_error', 'Email does not exist');
			redirect('applicant_system/applicant-login');
			die();
		}

		//pass data
		$userdata = $query->result();

		//test if no password
		if($userdata[0]->password == ''){
			//Welcome!1 as password should work
			if($this->input->post('password') == 'Welcome!1'){
				$this->session->set_userdata('applicant_id',$userdata[0]->applicant_id);
				redirect('applicant_system');
				die();
			}
			//send error message
			else{
				$this->session->set_flashdata('login_error', 'Incorrect password (user has default password)');
				redirect('applicant_system/applicant-login');
				die();
			}
		}

		//check password
		if($this->input->post('password') == $userdata[0]->password){
			$this->session->userdata('applicant_id',$userdata[0]->applicant_id);
			redirect('applicant_system');
			die();
		}
		//send error message
		else{
			$this->session->set_flashdata('login_error', 'Incorrect password');
			redirect('applicant_system/applicant-login');
			die();
		}

	}
}
?>
