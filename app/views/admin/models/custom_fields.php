<?php
class Custom_Fields extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function get_all(){
        $query = $this->db->get('custom_fields');
        return $query->result_array();
    }
    
}