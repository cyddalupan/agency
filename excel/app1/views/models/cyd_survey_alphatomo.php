<?php
class Cyd_Survey_Alphatomo extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function getSurveyAlphatomoById( $applicantId ){
      if($this->checkApplicantOnDB( $applicantId )){
        $this->db->where('applicant_id', $applicantId); 
        $query = $this->db->get('survey_alphatomo');
        $result = $query->result_array();
        return $result[0];
      }
    }

    function updateApplicantProfile($applicantId ){
        $post      = $_POST['applicant'];
        
        if(!isset($post['survey']))
            return false;

        $survey     = $post['survey'];

        // Get Json Files to array
        $survey_array = cydGetJson("survey.json");
        $wexp_array = cydGetJson("working_experience.json");
        $wabl_array = cydGetJson("working_ability.json");

        foreach ($survey_array->survey as $survey_value) {
            if(isset($survey[$survey_value->string]))
                $data[$survey_value->string] = $survey[$survey_value->string];
        }

        foreach ($wexp_array->working_experience as $wexp_value) {
            if(isset($survey[$wexp_value->string]))
            $data[$wexp_value->string] = $survey[$wexp_value->string];
        }

        foreach ($wabl_array->working_ability as $wabl_value) {
            if(isset($survey[$wabl_value->exp]))
                $data[$wabl_value->exp] = $survey[$wabl_value->exp];
            else
                $data[$wabl_value->exp] = 0;

            if(isset($survey[$wabl_value->will]))
                $data[$wabl_value->will] = $survey[$wabl_value->will];
            else
                $data[$wabl_value->will] = 0;
        }

        
        $data['future_plans'] = $survey['future_plans'];

        if($this->checkApplicantOnDB( $applicantId )){
            $this->db->where('applicant_id', $applicantId);
            $this->db->update('survey_alphatomo', $data); 
        }else{
            $data['applicant_id'] = $applicantId;
            $this->db->insert('survey_alphatomo', $data); 
        }
    }

    function checkApplicantOnDB( $applicantId  ){
        $query = $this->db->get_where('survey_alphatomo', array(
            'applicant_id' => $applicantId
        ));
        $count = $query->num_rows(); //counting result from query
        if ($count === 0) {
            return 0;
        }else{
            return 1;
        }
    }
}