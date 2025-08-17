<?php
class Cyd_Currency extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function get_all()
    {
         $query = $this->db->get('currencies');
         return $query->result();
    }

}