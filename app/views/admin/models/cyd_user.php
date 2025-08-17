<?php
class Cyd_User extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function get_one($user_id)
    {
        $this->db->where('user_id',$user_id);

        $query = $this->db->get('user')->result_array();
        return $query[0];
    }

}