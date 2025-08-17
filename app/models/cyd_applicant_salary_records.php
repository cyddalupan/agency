<?php
class Cyd_Applicant_Salary_Records extends CI_Model {
    
    function update_salary_records($cyd_applicant_salary_data)
    {
        if($cyd_applicant_salary_data['period_pay'] != ''){
            $this->db->insert('applicant_salary_record', $cyd_applicant_salary_data); 
        }
            
    }

}