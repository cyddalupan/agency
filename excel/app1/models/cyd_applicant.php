<?php
class Cyd_Applicant extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function get_one($applicant_id)
    {
        $this->db->where('applicant_id',$applicant_id);
        $query = $this->db->get('applicant')->result_array();
        return $query[0];
    }

    function get_one_join_applicant_salary($applicant_id){
        $this->db->where('applicant.applicant_id',$applicant_id);
        $this->db->join('applicant_salary','applicant_salary.applicant_id = applicant.applicant_id');
        $query = $this->db->get('applicant')->result_array();
        return $query[0];
    }

    function get_one_join_applicant_requirement($applicant_id){
        $this->db->where('applicant_id',$applicant_id);
        $this->db->join('applicant_requirement','requirement_applicant = applicant_id');
        $query = $this->db->get('applicant')->result_array();
        return $query[0];
    }

    function get_all_join_applicant_salary(){
        
        $this->db->join('applicant_salary','applicant_salary.applicant_id = applicant.applicant_id');
        $query = $this->db->get('applicant')->result_array();
        return $query;
    }

}