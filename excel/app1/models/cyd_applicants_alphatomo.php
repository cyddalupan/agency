<?php
class Cyd_Applicants_Alphatomo extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function getApplicantsAlphatomoById( $applicantId ){
        if($this->checkApplicantOnDB( $applicantId )){
            $this->db->where('applicant_id', $applicantId); 
            $query = $this->db->get('applicants_others');
            $result = $query->result();
            return $result[0];
        }else{
            return 0;
        }
    }
    
    function updateApplicantProfile($applicantId ){

        $post      = $_POST['applicant'];
        $basic     = $post['basic'];

        $data = array(
           'pos_in_fam'         => $basic['pos_in_fam'] ,
           'no_of_bro'          => $basic['no_of_bro'] ,
           'no_of_sis'          => $basic['no_of_sis'] ,
           'nam_of_fat'         => $basic['nam_of_fat'] ,
           'occ_of_fat'         => $basic['occ_of_fat'] ,
           'occ_of_mom'         => $basic['occ_of_mom'] ,
           'relative_name'      => $basic['relative_name'] ,
           'relative_mobile'    => $basic['relative_mobile'],
           'partner_husband'    => $basic['partner_husband'],
           'partner_occupation' => $basic['partner_occupation']
        );

        if($this->checkApplicantOnDB( $applicantId )){
            $this->db->where('applicant_id', $applicantId);
            $this->db->update('applicants_others', $data); 
        }else{
            $data['applicant_id'] = $applicantId;
            $this->db->insert('applicants_others', $data); 
        }
    }

    function checkApplicantOnDB( $applicantId  ){
        $query = $this->db->get_where('applicants_others', array(
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