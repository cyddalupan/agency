<?php
class Cyd_Applicant_Requirement extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function update_offer_salary($userid,$offer_salary){
    	$data = array(
		   'requirement_offer_salary' => $offer_salary
		);

		//$this->db->insert('applicant_requirement', $data);
		$this->db->where('requirement_applicant', $userid);
		$this->db->update('applicant_requirement', $data);  
    }

}