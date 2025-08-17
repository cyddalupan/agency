<?php
class Cyd_Multiple_Lineup extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    

    public function by_employer($employer_id){

        $this->db->where('applicant_employer', $employer_id);
        $mL_query = $this->db->get('multiple_lineups');
        return $mL_query->result();
        
    }
}