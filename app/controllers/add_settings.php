<?php
class add_settings extends CI_Controller {

	public function index()
	{
		$this->load->library('session');

		if(!$this->session->userdata('settings')){
			$settings = array();
			$query = $this->db->get('settings');
			foreach ($query->result() as $row)
			{
			    $settings[$row->key] = $row->value;
			}
			$this->session->set_userdata('settings', $settings);
		}

		redirect('landing');
	}
}
?>
