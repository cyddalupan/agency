<?php
class TrainingBranchModel extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function getAllTrainingBranch(){
        $query = $this->db->get('training_branches');
        return $query->result_array();
    }

    function getTrainingBranchById($id){
        $query = $this->db->get_where('training_branches', array('id' => $id));
        return $query->result_array();
    }

}