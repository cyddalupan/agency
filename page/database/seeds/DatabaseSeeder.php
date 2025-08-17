<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

$GLOBALS["applicants_count"] = 500;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

        // $this->call('TrainingBranchTableSeeder');
        // $this->call('EmployerReservationTableSeeder');
         $this->call('ApplicantsTableSeeder');
        // $this->call('ApplicantCertificateTableSeeder');
        // $this->call('ApplicantRequirementTableSeeder');
        // $this->call('ApplicantExperiencesTableSeeder');
        // $this->call('ApplicantPassportTableSeeder');
        // $this->call('applicant_preferred_positions');
        // $this->call('JobTableSeeder');
        // $this->call('ApplicantEducationTableSeeder');
        // $this->call('UserTableSeeder');
        // $this->call('EmployerTableSeeder');
        // $this->call('RecruitmentAgentTableSeeder');
        // $this->call('ApplicantsLogTableSeeder');
        // $this->call('CustomFieldsTableSeeder');
        // $this->call('EmployerReservationTableSeeder');
        
	}
}


class TrainingBranchTableSeeder extends Seeder {

    public function run()
    {
        //add admin
        DB::table('training_branches')->truncate();
        for ($trainingloop=0; $trainingloop < 50; $trainingloop++) { 
            DB::table('training_branches')->insert(
                [
                    'branch_name' => str_random(10),
                ]
            );
        }
    }
}

class UserTableSeeder extends Seeder {

    public function run()
    {
        //add admin
        DB::table('user')->where('user_id',1)->delete(); 
        DB::table('user')->insert(
            [
                'user_id' => 1, 
                'user_name' => 'cyd',
                'user_password' => md5( sha1( '123'.env('sess_cookie_name') ) ),
                'user_fullname' => str_random(10),
                'user_email' => 'cydmdalupan@gmail.com',
                'user_type' => 4,
                'user_status' => 1,
            ]
        );
        //other admin
        for ($admin=2; $admin < 50; $admin++) {
            DB::table('user')->where('user_id',$admin)->delete(); 
            DB::table('user')->insert(
                [
                    'user_id' => $admin, 
                    'user_name' => 'admin_'.$admin,
                    'user_password' => md5( sha1( '123'.env('sess_cookie_name') ) ),
                    'user_fullname' => str_random(10),
                    'user_email' => str_random(7).'@'.str_random(5).'com',
                    'user_type' => rand(1,8),
                'user_status' => 1,
                ]
            );
        }
        //add team leads
        for ($teamLeads=51; $teamLeads < 120; $teamLeads++) {
            DB::table('user')->where('user_id',$teamLeads)->delete(); 
            DB::table('user')->insert(
                [
                    'user_id' => $teamLeads, 
                    'user_name' => 'teamlead'.$teamLeads,
                    'user_password' => md5( sha1( '123'.env('sess_cookie_name') ) ),
                    'user_fullname' => str_random(10),
                    'user_email' => str_random(7).'@'.str_random(5).'com',
                    'user_type' => 9,
                    'user_status' => 1,
                ]
            );
        }

        //add recruitment_specialist
        for ($recruitmentSpeicalist=121; $recruitmentSpeicalist < 210; $recruitmentSpeicalist++) {
            DB::table('user')->where('user_id',$recruitmentSpeicalist)->delete(); 
            DB::table('user')->insert(
                [
                    'user_id' => $recruitmentSpeicalist, 
                    'user_name' => 'rs'.$recruitmentSpeicalist,
                    'user_password' => md5( sha1( '123'.env('sess_cookie_name') ) ),
                    'user_fullname' => str_random(10),
                    'user_email' => str_random(7).'@yahoo.com',
                    'user_type' => 10,
                    'team_lead_id' => rand(15,25),
                    'user_status' => 1,
                ]
            );
        }
        for ($TrainingAdmin=211; $TrainingAdmin < 250; $TrainingAdmin++) {
            DB::table('user')->where('user_id',$TrainingAdmin)->delete(); 
            DB::table('user')->insert(
                [
                    'user_id' => $TrainingAdmin, 
                    'user_name' => 'training_admin'.$TrainingAdmin,
                    'user_password' => md5( sha1( '123'.env('sess_cookie_name') ) ),
                    'user_fullname' => str_random(10),
                    'user_email' => str_random(7).'@yahoo.com',
                    'user_type' => rand(11,12),
                    'branch_id' => rand(1,50),
                    'user_status' => 1,
                ]
            );
        }
        for ($employer=251; $employer < 300; $employer++) {
            DB::table('user')->where('user_id',$employer)->delete(); 
            DB::table('user')->insert(
                [
                    'user_id' => $employer, 
                    'user_name' => 'employer'.$employer,
                    'user_password' => md5( sha1( '123'.env('sess_cookie_name') ) ),
                    'user_fullname' => str_random(10),
                    'user_email' => str_random(7).'@yahoo.com',
                    'user_type' => 5,
                    'branch_id' => rand(1,50),
                    'user_status' => 1,
                ]
            );
        }
    }
}


class EmployerTableSeeder extends Seeder {
    public function run()
    {
        for ($employers=1; $employers < 40; $employers++) {
            DB::table('employer')->where('employer_id',$employers)->delete(); 
            DB::table('employer')->insert(
                [
                    'employer_id' => $employers, 
                    'employer_user' => 250+$employers,
                    'employer_no' => rand(1111,9999),
                    'employer_name' => 'name_'.str_random(10),
                    'employer_contact_person' => 'contact_person_'.str_random(10),
                    'employer_contact' => rand(10000000000,99999999999),
                    'employer_email' => 'employer_email_'.str_random(7).'@yahoo.com',
                    'employer_address' => 'address_'.str_random(10),
                    'employer_country' => rand(1,15),
                    'employer_agency_commission_from' => 'Placement Fee'
                ]
            );
        }
        
    }
}


class ApplicantsTableSeeder extends Seeder {
    public function run()
    {
        $genter[1] = 'Male';
        $genter[2] = 'Female';

        $civilStatus[1] = 'Single';
        $civilStatus[2] = 'Married';

        $photo[1] = '';
        $photo[2] = '../../assets/images/sample/profile-1.png';
        $photo[3] = '../../assets/images/sample/profile-2.png';
        $photo[4] = '../../assets/images/sample/profile-3.png';
        $photo[5] = '../../assets/images/sample/profile-4.png';

        DB::table('applicant')->truncate();
        DB::table('applicants_others')->truncate();
        for ($applicant=1; $applicant < $GLOBALS["applicants_count"]; $applicant++) {

            $updated_date = strtotime(date('Y-m-d').' -'.rand(1,999).' day');
            DB::table('applicant')->insert(
                [
                    'applicant_id' => $applicant, 
                    'applicantNumber' => rand(100000,999999),
                    'applicant_first' => 'first_'.str_random(5),
                    'applicant_middle' => 'middle_'.str_random(5),
                    'applicant_last' => 'last_'.str_random(5),
                    'applicant_birthdate' => date('Y-m-d', rand(1111111111,9999999999)),
                    'applicant_age' => rand(20,40),
                    'applicant_gender' => $genter[rand(1,2)],
                    'applicant_contacts' => rand(1111111111,9999999999),
                    'applicant_address' => 'address_'.str_random(25),
                    'applicant_email' => 'applicant_email_'.str_random(7).'@yahoo.com',
                    'applicant_civil_status' => $civilStatus[rand(1,2)],
                    'applicant_status' => rand(1,12),
                    'applicant_employer' => rand(251,300),
                    'applicant_employer_number' => rand(0,9999999),
                    'applicant_job'     => rand(0,50),
                    'applicant_source'  => rand(0,50),
                    'applicant_preferred_position' => rand(1,27),
                    'applicant_expected_salary' => rand(9999,99999),
                    'applicant_preferred_country' => rand(1,15),
                    'applicant_photo' => $photo[rand(1,5)],
                    'is_repat' => rand(0,1),
                    'repat_date' => date('Y-m-d', rand(1111111111,9999999999)),
                    'applicant_slug'    => $applicant.'/'.str_random(5),
                    'training_branches_id'    => rand(1,50),
                    'start_training_at'    => date('Y-m-d', $updated_date),
                    'end_training_at'    => date('Y-m-d', $updated_date+90),
                    'training_remarks'    => str_random(95),
                    'applicant_remarks'    => str_random(50).' '.str_random(50).' '.str_random(50),
                    'applicant_date_applied' => date('Y-m-d', $updated_date),
                    'applicant_created' => date('Y-m-d', $updated_date),
                    'applicant_updatedby' => rand(0,50),
                ]
            );
            DB::table('applicants_others')->insert(
                [
                    'applicant_id' => $applicant,
                    'pos_in_fam' => rand(1,9),
                    'no_of_bro' => rand(1,12)
                ]
            );
        }

        $hitcount = 0;
        for ($applicant=$GLOBALS["applicants_count"]; $applicant < ($GLOBALS["applicants_count"]+20); $applicant++) {
            $hitcount++;
            $updated_date = strtotime(date('Y-m-d').' -'.rand(1,999).' day');
            DB::table('applicant')->insert(
                [
                    'applicant_id' => $applicant, 
                    'applicantNumber' => rand(100000,999999),
                    'applicant_first' => 'first_hit'.$hitcount,
                    'applicant_middle' => 'middle_'.$hitcount,
                    'applicant_last' => 'last_'.$hitcount,
                    'applicant_birthdate' => date('Y-m-d', rand(1111111111,9999999999)),
                    'applicant_age' => rand(20,40),
                    'applicant_gender' => $genter[rand(1,2)],
                    'applicant_contacts' => rand(1111111111,9999999999),
                    'applicant_address' => 'address_'.str_random(25),
                    'applicant_email' => 'applicant_email_'.str_random(7).'@yahoo.com',
                    'applicant_civil_status' => $civilStatus[rand(1,2)],
                    'applicant_status' => rand(1,12),
                    'applicant_employer' => rand(1,99),
                    'applicant_job'     => rand(0,50),
                    'applicant_source'  => rand(0,50),
                    'applicant_preferred_position' => rand(1,27),
                    'applicant_expected_salary' => rand(9999,99999),
                    'applicant_preferred_country' => rand(1,15),
                    'applicant_photo' => $photo[rand(1,5)],
                    'applicant_slug'    => $applicant.'/'.str_random(5),
                    'applicant_remarks'    => str_random(50).' '.str_random(50).' '.str_random(50),
                    'applicant_date_applied' => date('Y-m-d', $updated_date),
                    'applicant_created' => date('Y-m-d', $updated_date),
                    'applicant_updatedby' => rand(0,50),
                ]
            );
        }

    }
}

class JobTableSeeder extends Seeder {
    public function run()
    {

        $genter[1] = 'Male';
        $genter[2] = 'Female';
        $genter[3] = 'Any';

        DB::table('job')->truncate();
        for ($job=1; $job <= $GLOBALS["applicants_count"]; $job++) {
            $updated_date = strtotime(date('Y-m-d').' -'.rand(1,50).' day');
            DB::table('job')->insert(
                [
                    'job_id' => $job, 
                    'job_employer' => rand(0,99), 
                    'job_position' =>rand(0,27), 
                    'job_gender' => $genter[rand(1,3)], 
                    'job_salary' => rand(0,9999), 
                    'job_salary_from' => rand(0,9999),
                    'job_salary_to' => rand(0,9999), 
                    'job_total' => rand(0,499), 
                    'job_occupied' => rand(0,499), 
                    'job_name' => 'jobname_'.str_random(10), 
                    'job_content' => str_random(100),
                    'job_dollar_exchange' => rand(0,199),
                    'job_revenue' => rand(0,999),
                    'job_status' => 1,
                    'job_sstatus' => str_random(20),
                    'job_remarks' => str_random(40),
                    'job_createdby' => rand(0,50),
                ]
            );
        }
    }
}


class ApplicantCertificateTableSeeder extends Seeder {
    public function run()
    {
        $medical_result[1] = 'UNFIT';
        $medical_result[2] = 'FIT TO WORK';
        $medical_result[3] = '';

        DB::table('applicant_certificate')->truncate();
        for ($applicant_certificate=1; $applicant_certificate <= $GLOBALS["applicants_count"]; $applicant_certificate++) {
            $updated_date = strtotime(date('Y-m-d').' -'.rand(1,999).' day');
            DB::table('applicant_certificate')->insert(
                [
                    'certificate_id' => $applicant_certificate, 
                    'certificate_applicant' => $applicant_certificate, 
                    'certificate_medical_exam_date' => date('Y-m-d', $updated_date), 
                    'certificate_medical_result' => $medical_result[rand(1,3)], 
                    'certificate_medical_remarks' => str_random(10), 
                    'certificate_medical_expiration' => date('Y-m-d', $updated_date), 
                    'certificate_authenticated_nbi' => rand(0,1),
                    'insurance_no' => str_random(10),
                ]
            );
        }
    }
}

class applicant_preferred_positions extends Seeder {
    public function run()
    {

        DB::table('applicant_preferred_positions')->truncate();
        for ($applicant_preferred_positions=1; $applicant_preferred_positions <= ($GLOBALS["applicants_count"] * 2); $applicant_preferred_positions++) {
            $updated_date = strtotime(date('Y-m-d').' -'.rand(1,999).' day');
            DB::table('applicant_preferred_positions')->insert(
                [
                    'position_applicant' => rand(1,$GLOBALS["applicants_count"]),
                    'position_position' => rand(1,27),
                ]
            );
        }
    }
}

class ApplicantRequirementTableSeeder extends Seeder {
    public function run()
    {
        DB::table('applicant_requirement')->truncate();
        for ($applicant_requirement=1; $applicant_requirement <= $GLOBALS["applicants_count"]; $applicant_requirement++) {
            $requirement_contract = strtotime(date('Y-m-d').' +'.rand(1,999).' day');
            $requirement_visa_release_date = strtotime(date('Y-m-d').' +'.rand(1,999).' day');
            $requirement_visa_expiration = strtotime(date('Y-m-d').' +'.rand(1,5555).' day');
            DB::table('applicant_requirement')->insert(
                [
                    'requirement_id' => $applicant_requirement, 
                    'requirement_applicant' => $applicant_requirement,
                    'requirement_offer_salary' => rand(0,9999),
                    'requirement_visa_release_date' => date('Y-m-d', $requirement_visa_release_date),
                    'requirement_visa_expiration' => date('Y-m-d', $requirement_visa_expiration),
                    'requirement_trade_test' => rand(0,1),
                    'requirement_coe' => rand(0,1),
                    'requirement_picture_status' => str_random(10),
                    'requirement_school_records' => str_random(10),
                    'requirement_visa' => rand(0,1),
                    'requirement_ticket' => str_random(10),
                    'requirement_oec_number' => str_random(10),
                    'requirement_owwa_certificate' => str_random(10),
                    'requirement_contract' => date('Y-m-d', $requirement_contract),
                ]
            );
        }
    }
}

class ApplicantExperiencesTableSeeder extends Seeder {
    public function run()
    {
        DB::table('applicant_experiences')->truncate();
        for ($applicant_experiences=1; $applicant_experiences <= (2 * $GLOBALS["applicants_count"]); $applicant_experiences++) {
            $experienc_from = date('Y-m-d',strtotime(date('Y-m-d').' -'.rand(1,5555).' day'));
            $experienc_to = date('Y-m-d',strtotime(date('Y-m-d').' -'.rand(1,999).' day'));
            DB::table('applicant_experiences')->insert(
                [
                    'experience_applicant' => rand(1,$GLOBALS["applicants_count"]),
                    'experience_company' => str_random(25),
                    'experience_position' => str_random(25),
                    'experience_salary' => rand(0,2000),
                    'hospital_level' => rand(1,20),
                    'experience_from' => $experienc_from,
                    'experience_to' => $experienc_to,
                    'experience_years' => rand(1,20),
                    'experience_position' => str_random(25),
                ]
            );
        }
    }
}

class ApplicantPassportTableSeeder extends Seeder {
    public function run()
    {
        DB::table('applicant_passport')->truncate();
        for ($applicant_passport=1; $applicant_passport <= $GLOBALS["applicants_count"]; $applicant_passport++) {
            $passport_issue = date('Y-m-d',strtotime(date('Y-m-d').' -'.rand(1,5555).' day'));
            $passport_expiration = date('Y-m-d',strtotime(date('Y-m-d').' -'.rand(1,999).' day'));
            DB::table('applicant_passport')->insert(
                [
                    'passport_applicant' => $applicant_passport,
                    'passport_number' => str_random(10),
                    'passport_issue' => $passport_issue,
                    'passport_issue_place' => str_random(10),
                    'passport_expiration' => $passport_expiration,
                ]
            );
        }
    }
}

class ApplicantEducationTableSeeder extends Seeder {
    public function run()
    {
        DB::table('applicant_education')->truncate();
        for ($applicant_education=1; $applicant_education <= $GLOBALS["applicants_count"]; $applicant_education++) {
            DB::table('applicant_education')->insert(
                [
                    'education_applicant' => $applicant_education,
                    'education_mba' => str_random(25),
                    'education_mba_course' => str_random(25),
                    'education_mba_year' => rand(1990,date('Y')),
                    'education_college' => str_random(25),
                    'education_college_skills' => str_random(25),
                    'education_college_year' => rand(1990,date('Y')),
                    'education_highschool' => str_random(25),
                    'education_highschool_year' => rand(1990,date('Y')),
                ]
            );
        }
    }
}


class ApplicantsLogTableSeeder extends Seeder {
    public function run()
    {

        DB::table('applicant_log')->truncate();
        for ($applicant_log=1; $applicant_log <= (2 * $GLOBALS["applicants_count"]); $applicant_log++) {
            $updated_date = strtotime(date('Y-m-d').' -'.rand(1,999).' day');
            DB::table('applicant_log')->insert(
                [
                    'log_id' => $applicant_log, 
                    'log_applicant' => rand(1,$GLOBALS["applicants_count"]),
                    'log_employer' => rand(1,99),
                    'log_status' => rand(1,12),
                    'log_country' => rand(1,15),
                    'repat_date' =>   date('Y-m-d',$updated_date),
                    'log_date' =>   date('Y-m-d',$updated_date),
                    'log_remarks' => str_random(25),
                    'log_createdby' => rand(1,50),
                    'log_created' => date('Y-m-d')
                ]
            );
        }

    }
}


class RecruitmentAgentTableSeeder extends Seeder {
    public function run()
    {   
        
        DB::table('recruitment_agent')->truncate();
        for ($recruitment_agent=1; $recruitment_agent <= 50; $recruitment_agent++) {
            DB::table('recruitment_agent')->insert(
                [
                    'agent_id' => $recruitment_agent, 
                    'agent_first' => str_random(10), 
                    'agent_last' => str_random(10), 
                    'agent_contacts' => rand(10000000000,99999999999), 
                    'agent_email' => 'agent_email_'.str_random(7).'@yahoo.com', 
                    'agent_commission' => rand(0,999999), 
                    'cash_advance' => rand(0,99999), 
                    'balance' => rand(0,99999), 
                ]
            );
        }

    }
}

class CustomFieldsTableSeeder extends Seeder {
    public function run()
    {
        DB::table('custom_fields')->truncate();

        $locations = [
            //Profile
            'Preferred Designation',
            'Basic Information',
            'Incase of Emergency',
            'Passport Information',
            'Educational Background',
            'Personal Abilities',
            'Other',
            //Requirements
            'Examination Taken',
            'Medical Examination',
            'Aunthenticated Documents',
            'Other Requirements',
            'customCategory1',
            'customCategory2',
            'customCategory3',
        ];

        for ($customFieldLoop=0; $customFieldLoop < 60; $customFieldLoop++) {    
            DB::table('custom_fields')->insert(
                [
                    'name' => str_random(10), 
                    'location' => $locations[rand(0,13)], 
                    'description' => str_random(10), 
                ]
            );
        }
    }
}

class EmployerReservationTableSeeder extends Seeder {
    public function run()
    {
        DB::table('employer_reservation')->truncate();

        $expireDate = strtotime(date('Y-m-d').' '.rand(-100,100).' day');
        $reserveDate = strtotime(date('Y-m-d').' '.rand(0,100).' day');

        for ($employerReservationLoop=0; $employerReservationLoop < 150; $employerReservationLoop++) {   

            $expireDate = strtotime(date('Y-m-d').' '.rand(-100,100).' day');
            $reserveDate = strtotime(date('Y-m-d').' '.rand(0,100).' day'); 
            
            DB::table('employer_reservation')->insert(
                [
                    'reservation_employer' => rand(1,99), 
                    'reservation_applicant' => rand(1, $GLOBALS["applicants_count"]), 
                    'reservation_expiration' => date('Y-m-d',$expireDate), 
                    'reservation_status'    => rand(0,1),
                    'reservation_remarks'    => str_random(25),
                    'reservation_date'    => date('Y-m-d',$reserveDate), 
                ]
            );
        }
    }
}

