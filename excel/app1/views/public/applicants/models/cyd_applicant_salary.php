<?php
class Cyd_Applicant_Salary extends CI_Model {
    
    function update_salary($applicant_id,$cyd_applicant_salary_data)
    {
        if($cyd_applicant_salary_data['period_pay'] != ''){
            //test if id exist
            //select the id
            $this->db->where('applicant_id',$applicant_id);
            $is_set_applicant_id_query = $this->db->get('applicant_salary')->result_array();


            //update if already exist
            if(isset($is_set_applicant_id_query[0]['applicant_id']))
                $this->db->update('applicant_salary', $cyd_applicant_salary_data, 'applicant_id = '.$applicant_id);
            //insert new if not exist
            else    
                $this->db->insert('applicant_salary', $cyd_applicant_salary_data); 
        }
            
    }

    function get_applicant_salary_by_applicant_id($applicant_id){
        $this->db->where('applicant_id',$applicant_id);
        $query = $this->db->get('applicant_salary')->result_array();
        if(isset($query[0]))
            return $query[0];
    }

    
    function update_last_salary($applicant_id){
        $update_last_salary = array(
            'last_salary' => date('Y-m-d H:i:s')
        );
        $this->db->update('applicant_salary', $update_last_salary, 'applicant_id = '.$applicant_id);
    }

}