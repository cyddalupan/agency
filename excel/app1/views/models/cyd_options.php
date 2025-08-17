<?php
class Cyd_Options extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function all_options()
    {
        $query = $this->db->get('options')->result_array();
        return $query;
    }

    function get_option_value_byname($option_name){
        
        $this->db->where('option_name',$option_name);
        $query = $this->db->get('options')->result_array();
        return $query[0]['option_value'];
    }

}