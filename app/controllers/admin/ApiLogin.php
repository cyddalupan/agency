<?php
class ApiLogin extends CI_Controller {

	/** 
	 * Get Logged User From Session
	 * Add email and auth to session
	 * auth is the hash that use the username
	 * Get All hits Applicant
	 * Send Hits to casemanagement for view
	 */
	public function index(){
		$option_array = cydGetJson("option.json");

		if($GLOBALS['env']['APP_DEBUG'] == 'true'){
			$api_url = $option_array->api_local;
		}else{
			$api_url = $option_array->api_live;
		}


		$log_array['email'] = $_SESSION['admin']['user']['user_email'];
		$log_array['auth'] = md5(date('YmdH').$_SESSION['admin']['user']['user_name']);
		$log_array['path'] = str_replace('/','-mincode-','case-list/hit');
		$log_array['return_url'] = str_replace('/','-mincode-',site_url('admin/dashboard'));

		$query = $this->db->get_where('applicant', array('hit_status' => 'hit') );
		foreach($query->result() as $hitApplicant){
			$log_array['hit_ids'][] = $hitApplicant->hit_id;
		}

		$log_json =  json_encode($log_array);
		$log_url = urlencode($log_json);

		redirect($api_url.'api/login/'.$log_url);

	}

}