<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Skilled extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->database();
        $this->load->model('m_skilled_applicant');
        $this->load->model('m_country');
        $this->load->model('m_position');
        $this->load->model('cyd_currency');
        $this->load->model('Custom_Fields');
        $this->load->model('Cyd_Applicants_Alphatomo');
    }

    public function index()
    {
        $this->load->view('skilled/index');
    }

    public function register()
    {
        // 1. Retrieve Form Data
        $firstName = $this->input->post('firstName');
        $middleName = $this->input->post('middleName');
        $lastName = $this->input->post('lastName');
        $age = (int)$this->input->post('age');
        $contactNumber = $this->input->post('contactNumber');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $remarks = $this->input->post('remarks');
        $workLocation = $this->input->post('workLocation');
        $workDetails = $this->input->post('workDetails');
        $status = 73; // "Online" status ID

        // Calculate approximate birthdate from age
        $birthYear = date('Y') - $age;
        $birthdate = $birthYear . "-01-01";

        // 2. Handle File Upload
        $resume_path = '';
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
            $target_dir = "uploads/resumes/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $target_file = $target_dir . basename($_FILES["resume"]["name"]);
            
            if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
                $resume_path = $target_file;
            }
        }

        // 3. Prepare data for insertion
        $data = [
            'applicant_first' => $firstName,
            'applicant_middle' => $middleName,
            'applicant_last' => $lastName,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'applicant_age' => $age,
            'applicant_birthdate' => $birthdate,
            'applicant_contacts' => $contactNumber,
            'applicant_email' => $email,
            'applicant_remarks' => $remarks,
            'applicant_cv' => $resume_path,
            'applicant_position_type' => 'skilled', // Set applicant type to skilled
            'applicant_status' => $status,
            'applicant_created' => date('Y-m-d H:i:s'),
            'applicant_updated' => date('Y-m-d H:i:s'),
            // TODO: Fill in other fields with default or collected values
            'fra_ftw' => 0, 'agent_ppt' => 0, 'fra_visa' => 0, 'fra_deployed' => 0, 'fra_before' => 0, 'fra_sent' => 0, 'agent_ftw' => 0, 'agent_contract' => 0, 'agent_deployed' => 0,
            'fra_remarks' => '', 'applicantNumber' => '', 'sub_employer' => '',
            'applicant_suffix' => '', 'applicant_gender' => 'Male', 'applicant_contacts_2' => '',
            'applicant_contacts_3' => '', 'applicant_address' => '', 'applicant_nationality' => 'Filipino', 'applicant_civil_status' => 'Single',
            'applicant_religion' => 'Christian', 'applicant_languages' => 'English', 'applicant_height' => '170cm', 'applicant_weight' => '60kg',
            'applicant_preferred_position' => 0, 'currency' => 'PHP', 'applicant_mothers' => '', 'applicant_children' => '', 'applicant_expected_salary' => 0.0,
            'applicant_preferred_country' => 0, 'applicant_other_skills' => $workDetails, 'personalAbilities' => '', 'applicant_photo' => '',
            'sub_status' => '', 'applicant_paid' => 0, 'applicant_employer' => 0, 'applicant_employer_number' => '', 'applicant_job' => 0,
            'applicant_source' => 0, 'applicant_incase_name' => '', 'applicant_incase_relation' => '', 'applicant_incase_contact' => '', 'applicant_incase_address' => '',
            'is_repat' => 0, 'repat_date' => '1970-01-01', 'other_source' => '', 'applicant_slug' => '', 'training_remarks' => '', 'end_training_at' => '1970-01-01', 'start_training_at' => '1970-01-01',
            'training_branches_id' => 0, 'optional_statuses_id' => 0, 'hit_id' => 0, 'hit_hearing_date' => '1970-01-01', 'hit_status' => '', 'hit_date' => '1970-01-01 00:00:00',
            'applicant_date_applied' => date('Y-m-d'), 'applicant_createdby' => 0, 'applicant_updatedby' => 0,
            'applicant_fb' => '', 'incc' => 0.0, 'singil' => 0.0, 'applicant_employer_address' => '', 'applicant_date_interview' => '1970-01-01', 'applicant_by_interview' => '',
            'agentcom' => 0.0, 'applicant_paid1' => 0, 'applicant_ex' => '', 'request1' => '', 'request2' => '', 'request3' => '', 'applicant_remarks_3' => '', 'applicant_employer_idno' => '',
            'applicant_remarks1' => '', 'numberone' => 0, 'applicant_jobs' => '', 'timesched' => '', 'passsched' => '1970-01-01', 'releases' => '1970-01-01', 'remarkspas' => '', 'locsched' => '',
            'applicant_ppt_pay' => '', 'applicant_ppt_stat' => '', 'applicant_remarks5' => '', 'applicant_remarks6' => '', 'typess' => 0, 'highest1' => '', 'applicant_children1' => '',
            'applicant_arabic' => '', 'applicant_engslish' => '', 'applicant_con' => '', 'applicant_data1' => '', 'applicant_data2' => '', 'applicant_data3' => '', 'mystatus' => 0,
            'hideme' => 0, 'selection_date' => '1970-01-01', 'repat_date11' => '1970-01-01', 'accomodation1' => '', 'accomodation2' => '1970-01-01', 'accomodation3' => '1970-01-01', 'accomodation4' => '', 'accomodation5' => '',
            'checkmet' => 0, 'pass_type' => '', 'pass_com' => '', 'locsched1' => '', 'userassign' => 0, 'typess1' => 0, 't1' => '', 't2' => '', 't3' => '', 't4' => '', 't5' => '',
            't6' => '', 't7' => '', 't8' => '', 'localflight2' => '', 'fb_link' => '', 'applicant_remarks2' => '', 'applicant_remarks3' => '', 'singil1' => 0, 'applicant_contacts_4' => ''
        ];

        // 4. Insert data into the database
        $this->db->insert('applicant', $data);

        // 5. Redirect to login page
        redirect('skilled');
    }

    public function login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $this->db->where('applicant_email', $email);
        $query = $this->db->get('applicant');
        $user = $query->row();

        if ($user && password_verify($password, $user->password)) {
            $this->session->set_userdata('user_id', $user->applicant_id);
            $this->session->set_userdata('user_email', $user->applicant_email);
            $this->session->set_userdata('user_name', $user->applicant_first . ' ' . $user->applicant_last);
            redirect('skilled/profile');
        } else {
            // TODO: Show error message
            redirect('skilled');
        }
    }

    public function profile()
    {
        if (!$this->session->userdata('user_id')) {
            redirect('skilled');
        }

        $applicant_id = $this->session->userdata('user_id');

        $applicant = (new m_skilled_applicant)->getApplicantById($applicant_id);
        
        // These are needed by the view
        $applicant_raw = (new m_skilled_applicant)->getApplicantRawById($applicant_id);
        $applicant_certificate_direct = (new m_skilled_applicant)->getApplicantCertificateById($applicant_id);
        $applicant_requirements_direct = (new m_skilled_applicant)->getApplicantRequirementsById($applicant_id);
        $skill_cyd = (new m_skilled_applicant)->get_skill_cyd($applicant_id);
        $statusText = (new m_skilled_applicant)->statusText;
        $statusColors = (new m_skilled_applicant)->statusColors;
        $countries  = ( new m_country )->getCountries();
        $categories = ( new m_position )->getActivePositionsGroupByCategory();
        $currencies = ( new cyd_currency )->get_all();
        $customFields = ( new Custom_Fields )->get_all();

        $data = [
            'applicant' => $applicant,
            'applicant_raw' => $applicant_raw,
            'applicant_alphatomo'   => ( new Cyd_Applicants_Alphatomo )->getApplicantsAlphatomoById( $applicant_id ),
            'applicant_certificate_direct' => $applicant_certificate_direct,
            'applicant_requirements_direct' => $applicant_requirements_direct,
            'skill_cyd' => $skill_cyd,
            'statusText' => $statusText,
            'statusColors' => $statusColors,
            'countries' => $countries,
            'categories' => $categories,
            'currencies' => $currencies,
            'customFields' => $customFields,
            'app' => $this, // The view uses $app for rendering styles and scripts
        ];

        $this->load->view('skilled/profile', $data);
    }

    public function update_profile()
    {
        if (!$this->session->userdata('user_id')) {
            redirect('skilled');
        }

        $applicant_id = $this->session->userdata('user_id');

        // Handle form submission and update the database
        if ($this->input->post()) {
            $flag = $this->input->post('flag');
            $applicant_data = $this->input->post('applicant');

            switch ($flag) {
                case 'profile':
                    (new m_skilled_applicant)->updateApplicantProfile($applicant_id, $applicant_data);
                    break;
                case 'certificate':
                    (new m_skilled_applicant)->updateApplicantCertificates($applicant_id, $applicant_data);
                    break;
                case 'requirement':
                    (new m_skilled_applicant)->updateApplicantRequirements($applicant_id, $applicant_data);
                    break;
                case 'file':
                    // Handle file upload
                    break;
            }
        }

        redirect('skilled/profile');
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('skilled');
    }
}
