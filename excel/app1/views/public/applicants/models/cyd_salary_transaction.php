<?php
class Cyd_Salary_Transaction extends CI_Model {
    
    function insert($salary_transaction){

        //avoid double input. insert datetime and compare
        if($this->session->userdata('datetime') != date('Y-m-d H:i:s') ){
            //add time to session to avoid repeat
            $this->session->set_userdata('datetime', date('Y-m-d H:i:s'));
            
            //insert to salary_transactions database
            $this->db->insert('salary_transactions', $salary_transaction);

            //return the id of inserted
            $this->db->order_by('salary_transaction_id',"desc");
            $last_insert = $this->db->get('salary_transactions')->result_array();
            return $last_insert[0]['salary_transaction_id'];
        }

        
    }

    function update($last_insert_id,$salary_transaction){

        $this->db->update('salary_transactions', $salary_transaction,'salary_transaction_id = '.$last_insert_id);
       
        
    }

}