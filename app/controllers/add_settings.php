<?php
class add_settings extends CI_Controller {

	public function index()
	{
		$this->load->library('session');

		if(!isset($_SESSION["settings"])){
			$settings = array();
			$query = $this->db->get('settings');
			foreach ($query->result() as $row)
			{
			    $settings[$row->key] = $row->value;
			}
			$_SESSION["settings"] = $settings;
		}

		redirect($this->session->flashdata('prev_url'));
	}
}
?>
