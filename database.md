# Database Schema

This file contains the schema for all tables in the `iwebphil_everlast` database.

## Table: `accomodation`

| Field       | Type         | Null | Key | Default           | Extra |
|-------------|--------------|------|-----|-------------------|-------|
| acc_id      | int(11)      | NO   |     | NULL              |       |
| acc_name    | varchar(60)  | NO   |     | NULL              |       |
| acc_in      | date         | NO   |     | NULL              |       |
| acc_out     | date         | NO   |     | NULL              |       |
| acc_date    | datetime     | NO   |     | CURRENT_TIMESTAMP |       |
| acc_remarks | varchar(300) | NO   |     | NULL              |       |
| acc_app     | int(11)      | NO   |     | NULL              |       |
| acc_yes     | varchar(10)  | NO   |     | NULL              |       |
| acc_from    | date         | NO   |     | NULL              |       |
| acc_to      | date         | NO   |     | NULL              |       |
| acc_voucher | varchar(100) | NO   |     | NULL              |       |

## Table: `agent_agreement`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| agree_status | int(3) | NO |  | NULL |  |
| agree_country | int(11) | NO |  | NULL |  |
| agent_id | double | NO |  | NULL |  |
| agree_ftw | double | NO |  | NULL |  |
| agree_contract | double | NO |  | NULL |  |
| agree_deployed | double | NO |  | NULL |  |
| agree_id | int(11) | NO |  | NULL |  |
| agree_ppt | int(11) | NO |  | NULL |  |
## Table: `applicant`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| applicant_id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| fra_ftw | double | NO |  | NULL |  |
| agent_ppt | double | NO |  | NULL |  |
| fra_visa | double | NO |  | NULL |  |
| fra_deployed | double | NO |  | NULL |  |
| fra_before | double | NO |  | NULL |  |
| fra_sent | double | NO |  | NULL |  |
| agent_ftw | double | NO |  | NULL |  |
| agent_contract | double | NO |  | NULL |  |
| agent_deployed | double | NO |  | NULL |  |
| fra_remarks | varchar(60) | NO |  | NULL |  |
| applicantNumber | varchar(255) | NO |  | NULL |  |
| sub_employer | varchar(255) | NO |  | NULL |  |
| applicant_first | varchar(100) | NO |  | NULL |  |
| applicant_middle | varchar(100) | YES |  | NULL |  |
| applicant_last | varchar(100) | NO |  | NULL |  |
| password | varchar(255) | NO |  | NULL |  |
| applicant_suffix | varchar(10) | YES |  | NULL |  |
| applicant_birthdate | date | YES |  | NULL |  |
| applicant_age | int(10) unsigned | YES |  | NULL |  |
| applicant_gender | varchar(10) | YES |  | NULL |  |
| applicant_contacts | varchar(255) | YES |  | NULL |  |
| applicant_contacts_2 | varchar(255) | YES |  | NULL |  |
| applicant_contacts_3 | varchar(255) | NO |  | NULL |  |
| applicant_address | varchar(255) | YES |  | NULL |  |
| applicant_email | varchar(100) | YES |  | NULL |  |
| applicant_nationality | varchar(100) | YES |  | NULL |  |
| applicant_civil_status | varchar(100) | YES |  | NULL |  |
| applicant_religion | varchar(100) | YES |  | NULL |  |
| applicant_languages | varchar(255) | YES |  | NULL |  |
| applicant_height | varchar(10) | YES |  | NULL |  |
| applicant_weight | varchar(10) | YES |  | NULL |  |
| applicant_position_type | varchar(10) | YES |  | NULL |  |
| applicant_preferred_position | int(10) unsigned | YES |  | NULL |  |
| currency | varchar(255) | NO |  | NULL |  |
| applicant_mothers | varchar(255) | YES |  | NULL |  |
| applicant_children | varchar(255) | YES |  | NULL |  |
| applicant_expected_salary | float unsigned | YES |  | NULL |  |
| applicant_preferred_country | int(5) | YES |  | NULL |  |
| applicant_other_skills | text | YES |  | NULL |  |
| personalAbilities | varchar(255) | NO |  | NULL |  |
| applicant_cv | varchar(255) | YES |  | NULL |  |
| applicant_photo | varchar(255) | YES |  | NULL |  |
| applicant_status | int(10) unsigned | YES |  | 0 |  |
| sub_status | varchar(255) | NO |  | NULL |  |
| applicant_paid | tinyint(4) unsigned | NO |  | NULL |  |
| applicant_employer | int(10) unsigned | YES |  | 0 |  |
| applicant_employer_number | varchar(255) | NO |  | NULL |  |
| applicant_job | int(10) unsigned | YES |  | 0 |  |
| applicant_source | int(11) | YES |  | 0 |  |
| applicant_incase_name | varchar(255) | YES |  | NULL |  |
| applicant_incase_relation | varchar(255) | NO |  | NULL |  |
| applicant_incase_contact | varchar(255) | NO |  | NULL |  |
| applicant_incase_address | varchar(255) | NO |  | NULL |  |
| is_repat | tinyint(1) | NO |  | NULL |  |
| repat_date | date | NO |  | NULL |  |
| other_source | varchar(255) | NO |  | NULL |  |
| applicant_slug | varchar(255) | YES |  | NULL |  |
| training_remarks | varchar(255) | NO |  | NULL |  |
| end_training_at | date | NO |  | NULL |  |
| start_training_at | date | NO |  | NULL |  |
| training_branches_id | int(11) | NO |  | NULL |  |
| optional_statuses_id | int(11) | NO |  | NULL |  |
| applicant_remarks | text | YES |  | NULL |  |
| hit_id | int(11) | NO |  | NULL |  |
| hit_hearing_date | date | NO |  | NULL |  |
| hit_status | varchar(255) | NO |  | NULL |  |
| hit_date | datetime | NO |  | NULL |  |
| applicant_date_applied | date | YES |  | NULL |  |
| applicant_createdby | int(10) unsigned | YES |  | NULL |  |
| applicant_updatedby | int(10) unsigned | YES |  | NULL |  |
| applicant_created | timestamp | YES |  | NULL |  |
| applicant_updated | timestamp | NO |  | CURRENT_TIMESTAMP |  |
| applicant_fb | varchar(60) | NO |  | NULL |  |
| incc | double | NO |  | NULL |  |
| singil | double | NO |  | NULL |  |
| applicant_employer_address | varchar(60) | NO |  | NULL |  |
| applicant_date_interview | date | YES |  | NULL |  |
| applicant_by_interview | varchar(30) | NO |  | NULL |  |
| agentcom | double | NO |  | NULL |  |
| applicant_paid1 | int(3) | NO |  | NULL |  |
| applicant_ex | varchar(20) | NO |  | NULL |  |
| request1 | varchar(20) | NO |  | NULL |  |
| request2 | varchar(20) | NO |  | NULL |  |
| request3 | varchar(20) | NO |  | NULL |  |
| applicant_remarks_3 | tinytext | NO |  | NULL |  |
| applicant_employer_idno | varchar(40) | NO |  | NULL |  |
| applicant_remarks1 | tinytext | NO |  | NULL |  |
| numberone | int(3) | NO |  | NULL |  |
| applicant_jobs | text | NO |  | NULL |  |
| timesched | varchar(60) | NO |  | NULL |  |
| passsched | date | NO |  | NULL |  |
| releases | date | NO |  | NULL |  |
| remarkspas | varchar(60) | NO |  | NULL |  |
| locsched | varchar(40) | NO |  | NULL |  |
| applicant_ppt_pay | varchar(25) | YES |  | NULL |  |
| applicant_ppt_stat | varchar(25) | NO |  | NULL |  |
| applicant_remarks5 | tinytext | NO |  | NULL |  |
| applicant_remarks6 | tinytext | NO |  | NULL |  |
| typess | int(3) | NO |  | NULL |  |
| highest1 | varchar(50) | NO |  | NULL |  |
| applicant_children1 | varchar(15) | NO |  | NULL |  |
| applicant_arabic | varchar(30) | NO |  | NULL |  |
| applicant_engslish | varchar(30) | NO |  | NULL |  |
| applicant_con | varchar(10) | NO |  | NULL |  |
| applicant_data1 | varchar(50) | NO |  | NULL |  |
| applicant_data2 | varchar(50) | NO |  | NULL |  |
| applicant_data3 | varchar(100) | NO |  | NULL |  |
| mystatus | int(3) | NO |  | NULL |  |
| hideme | int(3) | NO |  | NULL |  |
| selection_date | date | NO |  | NULL |  |
| repat_date11 | date | NO |  | NULL |  |
| accomodation1 | varchar(10) | NO |  | NULL |  |
| accomodation2 | date | NO |  | NULL |  |
| accomodation3 | date | NO |  | NULL |  |
| accomodation4 | varchar(10) | NO |  | NULL |  |
| accomodation5 | tinytext | NO |  | NULL |  |
| checkmet | int(3) | NO |  | NULL |  |
| pass_type | varchar(60) | NO |  | NULL |  |
| pass_com | varchar(60) | NO |  | NULL |  |
| locsched1 | varchar(60) | NO |  | NULL |  |
| userassign | int(11) | NO |  | NULL |  |
| typess1 | int(3) | NO |  | NULL |  |
| t1 | varchar(100) | NO |  | NULL |  |
| t2 | varchar(100) | NO |  | NULL |  |
| t3 | varchar(100) | NO |  | NULL |  |
| t4 | varchar(100) | NO |  | NULL |  |
| t5 | varchar(100) | NO |  | NULL |  |
| t6 | varchar(100) | NO |  | NULL |  |
| t7 | varchar(100) | NO |  | NULL |  |
| t8 | varchar(100) | NO |  | NULL |  |
| localflight2 | varchar(30) | NO |  | NULL |  |
| fb_link | varchar(50) | NO |  | NULL |  |
| applicant_remarks2 | varchar(50) | NO |  | NULL |  |
| applicant_remarks3 | varchar(50) | NO |  | NULL |  |
| singil1 | int(11) | NO |  | NULL |  |
| applicant_contacts_4 | varchar(20) | NO |  | NULL |  |
## Table: `applicant_certificate`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| certificate_id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| certificate_applicant | int(10) unsigned | YES |  | NULL |  |
| certificate_tesda | tinyint(3) unsigned | YES |  | NULL |  |
| certificate_mmr | varchar(255) | NO |  | NULL |  |
| certificate_tesda_date | varchar(255) | NO |  | NULL |  |
| certificate_training | int(11) | YES |  | NULL |  |
| certificate_info_sheet | int(3) unsigned | YES |  | NULL |  |
| certificate_authenticated | tinyint(3) unsigned | YES |  | NULL |  |
| red_ribbon_file_date | date | NO |  | NULL |  |
| red_ribbon_expired_date | date | NO |  | NULL |  |
| certificate_authenticated_nbi | tinyint(3) unsigned | YES |  | NULL |  |
| nbi_expired_date | date | NO |  | NULL |  |
| certificate_insurance | varchar(255) | YES |  | NULL |  |
| insurance_no | varchar(255) | NO |  | NULL |  |
| insurance_date | date | NO |  | NULL |  |
| certificate_medical_clinic | varchar(255) | YES |  | NULL |  |
| certificate_medical_exam_date | date | YES |  | 0000-00-00 |  |
| certificate_medical_result | varchar(255) | NO |  | NULL |  |
| certificate_medical_remarks | text | YES |  | NULL |  |
| certificate_medical_expiration | date | YES |  | 0000-00-00 |  |
| certificate_pdos | tinyint(3) unsigned | YES |  | NULL |  |
| certificate_owwa_from | varchar(255) | NO |  | NULL |  |
| certificate_owwa_to | varchar(255) | NO |  | NULL |  |
| certificate_owwa | int(11) | NO |  | NULL |  |
| certificate_pdos_date | varchar(255) | NO |  | NULL |  |
| fra_pdos | varchar(100) | NO |  | NULL |  |
| certificate_pt_result | varchar(255) | NO |  | NULL |  |
| certificate_pt_result_date | date | YES |  | 0000-00-00 |  |
| certificate_philhealth | tinyint(3) unsigned | YES |  | NULL |  |
| certificate_m1b | tinyint(3) unsigned | YES |  | NULL |  |
| certificate_mc | tinyint(3) | NO |  | NULL |  |
| certificate_cgfns | varchar(255) | NO |  | NULL |  |
| certificate_cgfns_exam | varchar(255) | NO |  | NULL |  |
| certificate_cgfns_id | varchar(255) | NO |  | NULL |  |
| certificate_saudi_id | varchar(255) | NO |  | NULL |  |
| certificate_ielts | varchar(255) | NO |  | NULL |  |
| certificate_ielts_overall | varchar(255) | NO |  | NULL |  |
| certificate_ielts_exam | varchar(255) | NO |  | NULL |  |
| certificate_dha | varchar(255) | NO |  | NULL |  |
| certificate_nclex | varchar(255) | NO |  | NULL |  |
| certificate_nclex_exam | varchar(255) | NO |  | NULL |  |
| certificate_qatar | varchar(255) | NO |  | NULL |  |
| certificate_haad | varchar(255) | NO |  | NULL |  |
| certificate_ksa | varchar(255) | NO |  | NULL |  |
| certificate_bc | tinyint(3) | NO |  | NULL |  |
| certificate_coe | varchar(255) | NO |  | NULL |  |
| certificate_prc_rating | varchar(255) | NO |  | NULL |  |
| certificate_prc_type | varchar(255) | NO |  | NULL |  |
| certificate_prc_id | varchar(255) | NO |  | NULL |  |
| applicant_certificate_no_marriage | date | NO |  | NULL |  |
| certificate_prc_take | date | NO |  | NULL |  |
| certificate_prc_cert | tinyint(3) | NO |  | NULL |  |
| certificate_tor | tinyint(3) | NO |  | NULL |  |
| certificate_createdby | int(10) unsigned | YES |  | NULL |  |
| certificate_updatedby | int(10) unsigned | YES |  | NULL |  |
| certificate_created | timestamp | YES |  | NULL |  |
| certificate_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
| certificate_vsh | varchar(255) | NO |  | NULL |  |
| certificate_vsh_exam | varchar(255) | NO |  | NULL |  |
| certificate_pdos_no | varchar(40) | NO |  | NULL |  |
| owwa_number | varchar(40) | NO |  | NULL |  |
| omma | varchar(20) | NO |  | NULL |  |
| omma_date | date | YES |  | NULL |  |
| swab | varchar(25) | NO |  | NULL |  |
| swab_date | date | YES |  | NULL |  |
| polio | varchar(25) | NO |  | NULL |  |
| polio_date | date | YES |  | NULL |  |
| certificate_lineup_remarks | varchar(50) | NO |  | NULL |  |
| certificate_goback | varchar(25) | NO |  | NULL |  |
| certificate_pre_remarks | varchar(50) | NO |  | NULL |  |
| certificate_process_remarks | varchar(50) | NO |  | NULL |  |
| certificate_tesda_name | varchar(50) | NO |  | NULL |  |
| certificate_tesda_release | date | YES |  | NULL |  |
| certificate_visa_remarks | varchar(50) | NO |  | NULL |  |
| certificate_owwaremarks | varchar(60) | NO |  | NULL |  |
| certificate_oecremarks | varchar(50) | NO |  | NULL |  |
| certificate_bookingremarks | varchar(60) | NO |  | NULL |  |
| certificate_owwa_file | varchar(50) | NO |  | NULL |  |
| medical_fit | date | NO |  | NULL |  |
| medical_cert | varchar(20) | NO |  | NULL |  |
| medical_certdate | date | NO |  | NULL |  |
| medicaltype | varchar(30) | NO |  | NULL |  |
| certificate_tesda_assest | date | NO |  | NULL |  |
| localflight | date | NO |  | NULL |  |
| certificate_tesda_expired | date | NO |  | NULL |  |
| certificate_peos | tinyint(3) | NO |  | NULL |  |
| certificate_ereg | tinyint(3) | NO |  | NULL |  |
| certificate_peosd | date | NO |  | NULL |  |
| pagibig | varchar(25) | NO |  | NULL |  |
| red1 | date | NO |  | NULL |  |
| red2 | date | NO |  | NULL |  |
| price | double | NO |  | NULL |  |
| cashbilled | varchar(30) | NO |  | NULL |  |
| eregnum | varchar(30) | NO |  | NULL |  |
| eregemail | varchar(30) | NO |  | NULL |  |
| eregpassword | varchar(30) | NO |  | NULL |  |
| eregpeos | varchar(30) | NO |  | NULL |  |
| pagibig1 | int(3) | NO |  | NULL |  |
| localflight1 | varchar(30) | NO |  | NULL |  |
| localflight2 | varchar(30) | NO |  | NULL |  |
## Table: `applicant_experiences`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| experience_id | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| experience_applicant | int(10) unsigned | NO |  | NULL |  |
| experience_company | varchar(255) | NO |  | NULL |  |
| experience_position | varchar(255) | NO |  | NULL |  |
| experience_salary | varchar(255) | NO |  | NULL |  |
| hospital_level | varchar(255) | NO |  | NULL |  |
| bed_capacity | varchar(255) | NO |  | NULL |  |
| experience_country | varchar(100) | NO |  | NULL |  |
| reasonOfLeaving | varchar(255) | NO |  | NULL |  |
| extraExperience12 | varchar(255) | NO |  | NULL |  |
| extraExperience11 | varchar(255) | NO |  | NULL |  |
| extraExperience10 | varchar(255) | NO |  | NULL |  |
| salary | varchar(255) | NO |  | NULL |  |
| typeOfResidence | varchar(255) | NO |  | NULL |  |
| nationality | varchar(255) | NO |  | NULL |  |
| NoFamilyMembers | varchar(255) | NO |  | NULL |  |
| experience_from | varchar(100) | NO |  | NULL |  |
| experience_to | varchar(100) | NO |  | NULL |  |
| experience_years | varchar(10) | NO |  | NULL |  |
| experience_createdby | int(10) unsigned | NO |  | NULL |  |
| experience_updatedby | int(10) unsigned | NO |  | NULL |  |
| experience_created | timestamp | NO |  | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
| experience_updated | timestamp | NO |  | 0000-00-00 00:00:00 |  |
## Table: `applicant_education`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| education_id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| education_applicant | int(10) unsigned | NO |  | NULL |  |
| education_mba | varchar(255) | NO |  | NULL |  |
| education_mba_course | varchar(255) | NO |  | NULL |  |
| education_mba_year | varchar(10) | NO |  | NULL |  |
| education_college | varchar(255) | NO |  | NULL |  |
| education_college_skills | varchar(255) | NO |  | NULL |  |
| education_college_year | varchar(10) | NO |  | NULL |  |
| education_others | varchar(255) | NO |  | NULL |  |
| education_others_year | varchar(10) | NO |  | NULL |  |
| education_highschool | varchar(255) | NO |  | NULL |  |
| education_highschool_year | varchar(10) | NO |  | NULL |  |
| education_createdby | int(10) unsigned | NO |  | NULL |  |
| education_updatedby | int(10) unsigned | NO |  | NULL |  |
| education_created | timestamp | NO |  | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
| education_updated | timestamp | NO |  | 0000-00-00 00:00:00 |  |
## Table: `applicant_files`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| file_id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| file_applicant | int(10) unsigned | YES |  | NULL |  |
| file_name | varchar(255) | YES |  | NULL |  |
| file_type | varchar(100) | YES |  | NULL |  |
| file_size | varchar(100) | YES |  | NULL |  |
| file_mime | varchar(10) | YES |  | NULL |  |
| file_path | varchar(255) | YES |  | NULL |  |
| file_status | int(11) | YES |  | 0 |  |
| file_createdby | int(10) unsigned | YES |  | NULL |  |
| file_created | timestamp | YES |  | NULL |  |
## Table: `applicant_log`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| log_id | bigint(20) unsigned | NO |  | NULL |  |
| log_applicant | int(10) unsigned | NO |  | NULL |  |
| log_employer | int(10) unsigned | YES |  | NULL |  |
| log_status | int(10) unsigned | NO |  | NULL |  |
| log_country | int(10) unsigned | NO |  | NULL |  |
| repat_date | datetime | NO |  | NULL |  |
| log_date | date | YES |  | NULL |  |
| log_remarks | text | YES |  | NULL |  |
| log_createdby | int(10) unsigned | NO |  | NULL |  |
| log_created | timestamp | YES |  | NULL |  |
## Table: `applicant_message`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| message_id | int(11) | NO |  | NULL |  |
| me_app | int(3) | NO |  | NULL |  |
| me_agent | int(3) | NO |  | NULL |  |
| message | text | NO |  | NULL |  |
| me_user | varchar(30) | NO |  | NULL |  |
| me_date | datetime | NO |  | CURRENT_TIMESTAMP |  |
| me_contact | varchar(30) | NO |  | NULL |  |
## Table: `applicant_passport`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| passport_id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| passport_applicant | int(10) unsigned | NO |  | NULL |  |
| passport_number | varchar(100) | YES |  | NULL |  |
| passport_issue | date | YES |  | NULL |  |
| passport_issue_place | varchar(255) | YES |  | NULL |  |
| passport_expiration | date | YES |  | NULL |  |
| passport_visible | tinyint(4) | YES |  | 0 |  |
| passport_createdby | int(10) unsigned | YES |  | NULL |  |
| passport_updatedby | int(10) unsigned | YES |  | NULL |  |
| passport_created | timestamp | YES |  | NULL |  |
| passport_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `applicant_preferred_countries`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| country_id | int(10) unsigned | NO |  | NULL |  |
| country_applicant | int(10) unsigned | YES |  | NULL |  |
| country_country | int(10) unsigned | YES |  | NULL |  |
| country_createdby | int(10) unsigned | YES |  | NULL |  |
| country_created | timestamp | YES |  | NULL |  |
## Table: `applicant_preferred_positions`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| position_id | int(10) unsigned | NO |  | NULL |  |
| position_applicant | int(10) unsigned | YES |  | NULL |  |
| position_position | int(10) unsigned | YES |  | NULL |  |
| position_createdby | int(10) unsigned | YES |  | NULL |  |
| position_created | timestamp | YES |  | NULL |  |
## Table: `applicant_requirement`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| requirement_id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| requirement_applicant | int(10) unsigned | NO |  | NULL |  |
| requirement_trade_test | tinyint(3) unsigned | YES |  | NULL |  |
| requirement_trade_remarks | varchar(255) | NO |  | NULL |  |
| requirement_picture_status | varchar(100) | YES |  | NULL |  |
| requirement_coe | tinyint(3) unsigned | YES |  | NULL |  |
| requirement_school_records | varchar(255) | YES |  | NULL |  |
| offer_letter | varchar(255) | NO |  | NULL |  |
| requirement_visa | tinyint(4) unsigned | YES |  | NULL |  |
| applicant_requirement_visaremarks | varchar(255) | NO |  | NULL |  |
| requirement_visa_no | varchar(255) | NO |  | NULL |  |
| requirement_visa_date | date | YES |  | NULL |  |
| requirement_visa_stamp | date | NO |  | NULL |  |
| requirement_visa_release_date | date | YES |  | NULL |  |
| requirement_visa_expiration | date | YES |  | NULL |  |
| requirement_oec_number | varchar(100) | YES |  | NULL |  |
| requirement_oec_submission_date | date | YES |  | NULL |  |
| requirement_oec_release_date | date | YES |  | NULL |  |
| oec_expired | date | NO |  | NULL |  |
| requirement_owwa_certificate | varchar(255) | YES |  | NULL |  |
| requirement_owwa_schedule | date | YES |  | NULL |  |
| requirement_contract | date | NO |  | NULL |  |
| requirement_mofa | varchar(255) | YES |  | NULL |  |
| requirement_job_offer | int(11) | YES |  | NULL |  |
| requirement_job_received | date | NO |  | NULL |  |
| requirement_job_accepted | date | NO |  | NULL |  |
| requirement_ticket | varchar(255) | YES |  | NULL |  |
| ticket_remarks | varchar(255) | NO |  | NULL |  |
| flight_date | date | NO |  | NULL |  |
| ticket_no | varchar(255) | NO |  | NULL |  |
| requirement_offer_salary | float unsigned | YES |  | NULL |  |
| requirement_remarks | text | YES |  | NULL |  |
| requirement_createdby | int(10) unsigned | YES |  | NULL |  |
| requirement_updatedby | int(10) unsigned | YES |  | NULL |  |
| requirement_created | timestamp | YES |  | NULL |  |
| requirement_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
| requirement_visa_category | varchar(255) | NO |  | NULL |  |
| applicant_requirement_ecode | varchar(255) | NO |  | NULL |  |
| applicant_requirement_paid | varchar(255) | NO |  | NULL |  |
| applicant_requirement_rfp | varchar(255) | NO |  | NULL |  |
| applicant_requirement_oec_expired | varchar(255) | NO |  | NULL |  |
| visa_duration | int(11) | NO |  | NULL |  |
| applicant_requirement_eregd | date | YES |  | NULL |  |
| applicant_requirement_ereg | varchar(40) | NO |  | NULL |  |
| vfs | varchar(10) | NO |  | NULL |  |
| applicant_requirement_lastpage | varchar(40) | NO |  | NULL |  |
| applicant_requirement_mol | date | NO |  | NULL |  |
| applicant_requirement_kawala | varchar(40) | NO |  | NULL |  |
| applicant_requirement_peos | varchar(40) | NO |  | NULL |  |
| applicant_requirement_peosd | varchar(40) | NO |  | NULL |  |
| requirement_contract_sign | date | YES |  | NULL |  |
| requirement_exp | varchar(40) | NO |  | NULL |  |
| requirement_oec_file | date | YES |  | NULL |  |
| requirement_musaned_encoded | date | YES |  | NULL |  |
| requirement_musaned_approved | date | YES |  | NULL |  |
| requirement_musaned_sign | date | YES |  | NULL |  |
| transnum | varchar(50) | NO |  | NULL |  |
| ticket_plus | varchar(30) | NO |  | NULL |  |
| stamped_kuw | date | NO |  | NULL |  |
| covidme | varchar(25) | NO |  | NULL |  |
| covid_name | varchar(30) | NO |  | NULL |  |
| covid_date | date | NO |  | NULL |  |
| covid_date2 | date | NO |  | NULL |  |
| covid_loc | varchar(60) | NO |  | NULL |  |
| covid_yellow | varchar(3) | NO |  | NULL |  |
| covid_cert | varchar(3) | NO |  | NULL |  |
| covidb1 | varchar(20) | NO |  | NULL |  |
| covidb2 | varchar(30) | NO |  | NULL |  |
| covidb3 | varchar(20) | NO |  | NULL |  |
| insurance1 | varchar(30) | NO |  | NULL |  |
| insurance2 | varchar(30) | NO |  | NULL |  |
| insurance3 | varchar(60) | NO |  | NULL |  |
| vfs11 | varchar(15) | NO |  | NULL |  |
## Table: `applicant_salary`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| applicant_salary_id | int(11) | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| period_pay | varchar(100) | NO |  | NULL |  |
| basic_salary_pay | double | NO |  | NULL |  |
| status_pay | varchar(100) | NO |  | NULL |  |
| salary_date | varchar(10) | NO |  | NULL |  |
| sss_deductions | double | NO |  | NULL |  |
| phil_health_deductions | double | NO |  | NULL |  |
| pag_ibig_deductions | double | NO |  | NULL |  |
| hmo_miscellaneous | double | NO |  | NULL |  |
| over_time_miscellaneous | double | NO |  | NULL |  |
| night_differential_miscellaneous | double | NO |  | NULL |  |
| holiday_pay_miscellaneous | double | NO |  | NULL |  |
| absent_miscellaneous | double | NO |  | NULL |  |
| tardiness_miscellaneous | double | NO |  | NULL |  |
| undertime_miscellaneous | double | NO |  | NULL |  |
| meal_allowances | double | NO |  | NULL |  |
| transportation_allowances | double | NO |  | NULL |  |
| cola_allowances | double | NO |  | NULL |  |
| other_allowances | double | NO |  | NULL |  |
| tax_shielded_allowances | double | NO |  | NULL |  |
| total_deductions | double | NO |  | NULL |  |
| total_allowances | double | NO |  | NULL |  |
| total_misc | double | NO |  | NULL |  |
| taxable_income | double | NO |  | NULL |  |
| withholding_tax | double | NO |  | NULL |  |
| net_income | double | NO |  | NULL |  |
| salary_remarks | varchar(500) | NO |  | NULL |  |
| last_salary | datetime | NO |  | NULL |  |
| updated_at | datetime | NO |  | NULL |  |
| created_at | datetime | NO |  | NULL |  |
| deleted_at | datetime | NO |  | NULL |  |
## Table: `applicant_salary_record`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| applicant_salary_record_id | int(11) | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| period_pay | varchar(100) | NO |  | NULL |  |
| basic_salary_pay | double | NO |  | NULL |  |
| status_pay | varchar(100) | NO |  | NULL |  |
| salary_date | varchar(10) | NO |  | NULL |  |
| sss_deductions | double | NO |  | NULL |  |
| phil_health_deductions | double | NO |  | NULL |  |
| pag_ibig_deductions | double | NO |  | NULL |  |
| hmo_miscellaneous | double | NO |  | NULL |  |
| over_time_miscellaneous | double | NO |  | NULL |  |
| night_differential_miscellaneous | double | NO |  | NULL |  |
| holiday_pay_miscellaneous | double | NO |  | NULL |  |
| absent_miscellaneous | double | NO |  | NULL |  |
| tardiness_miscellaneous | double | NO |  | NULL |  |
| undertime_miscellaneous | double | NO |  | NULL |  |
| meal_allowances | double | NO |  | NULL |  |
| transportation_allowances | double | NO |  | NULL |  |
| cola_allowances | double | NO |  | NULL |  |
| other_allowances | double | NO |  | NULL |  |
| tax_shielded_allowances | double | NO |  | NULL |  |
| total_deductions | double | NO |  | NULL |  |
| total_allowances | double | NO |  | NULL |  |
| total_misc | double | NO |  | NULL |  |
| taxable_income | double | NO |  | NULL |  |
| withholding_tax | double | NO |  | NULL |  |
| net_income | double | NO |  | NULL |  |
| salary_remarks | varchar(500) | NO |  | NULL |  |
| updated_at | datetime | NO |  | NULL |  |
| created_at | datetime | NO |  | NULL |  |
| deleted_at | datetime | NO |  | NULL |  |
## Table: `applicant_skills`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| skill_id | bigint(20) unsigned | NO |  | NULL |  |
| skill_applicant | int(10) unsigned | NO |  | NULL |  |
| skill_name | varchar(255) | NO |  | NULL |  |
| skill_created | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `applicant_skills_cyds`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| ironing | tinyint(1) | NO |  | NULL |  |
| cooking | tinyint(1) | NO |  | NULL |  |
| sewing | tinyint(1) | NO |  | NULL |  |
| computer | tinyint(1) | NO |  | NULL |  |
| washing | tinyint(1) | NO |  | NULL |  |
| cleaning | tinyint(1) | NO |  | NULL |  |
| tutoring | tinyint(1) | NO |  | NULL |  |
| children_care | tinyint(1) | NO |  | NULL |  |
| baby_sitting | tinyint(1) | NO |  | NULL |  |
| arabic_cooking | tinyint(1) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| coloring | int(2) | NO |  | NULL |  |
| blower | int(2) | NO |  | NULL |  |
| massage | int(2) | NO |  | NULL |  |
| manicure | int(2) | NO |  | NULL |  |
| write_e | int(2) | NO |  | NULL |  |
| read_e | int(2) | NO |  | NULL |  |
| speak_e | int(2) | NO |  | NULL |  |
| write_a | int(2) | NO |  | NULL |  |
| read_a | int(2) | NO |  | NULL |  |
| speak_a | int(2) | NO |  | NULL |  |
## Table: `applicant_view`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| applicant_id | int(10) unsigned | NO |  | 0 |  |
| applicant_first | varchar(100) | NO |  | NULL |  |
| applicant_middle | varchar(100) | YES |  | NULL |  |
| applicant_last | varchar(100) | NO |  | NULL |  |
| applicant_name | varchar(302) | YES |  | NULL |  |
| applicant_suffix | varchar(10) | YES |  | NULL |  |
| applicant_birthdate | date | YES |  | NULL |  |
| applicant_age | int(10) unsigned | YES |  | NULL |  |
| applicant_gender | varchar(10) | YES |  | NULL |  |
| applicant_contacts | varchar(255) | YES |  | NULL |  |
| applicant_address | varchar(255) | YES |  | NULL |  |
| applicant_email | varchar(100) | YES |  | NULL |  |
| applicant_nationality | varchar(100) | YES |  | NULL |  |
| applicant_civil_status | varchar(100) | YES |  | NULL |  |
| applicant_religion | varchar(100) | YES |  | NULL |  |
| applicant_languages | varchar(255) | YES |  | NULL |  |
| applicant_height | varchar(10) | YES |  | NULL |  |
| applicant_weight | varchar(10) | YES |  | NULL |  |
| applicant_position_type | varchar(10) | YES |  | NULL |  |
| applicant_preferred_position | int(10) unsigned | YES |  | NULL |  |
| applicant_expected_salary | float unsigned | YES |  | NULL |  |
| applicant_preferred_country | int(5) | YES |  | NULL |  |
| applicant_other_skills | text | YES |  | NULL |  |
| applicant_cv | varchar(255) | YES |  | NULL |  |
| applicant_photo | varchar(255) | YES |  | NULL |  |
| applicant_status | int(10) unsigned | YES |  | 0 |  |
| applicant_source | int(11) | YES |  | 0 |  |
| applicant_remarks | text | YES |  | NULL |  |
| applicant_date_applied | date | YES |  | NULL |  |
| applicant_createdby | int(10) unsigned | YES |  | NULL |  |
| applicant_updatedby | int(10) unsigned | YES |  | NULL |  |
| applicant_created | timestamp | YES |  | NULL |  |
| applicant_updated | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| education_id | int(10) unsigned | YES |  | 0 |  |
| education_applicant | int(10) unsigned | YES |  | NULL |  |
| education_mba | varchar(255) | YES |  | NULL |  |
| education_mba_course | varchar(255) | YES |  | NULL |  |
| education_mba_year | varchar(10) | YES |  | NULL |  |
| education_college | varchar(255) | YES |  | NULL |  |
| education_college_skills | varchar(255) | YES |  | NULL |  |
| education_college_year | varchar(10) | YES |  | NULL |  |
| education_others | varchar(255) | YES |  | NULL |  |
| education_others_year | varchar(10) | YES |  | NULL |  |
| education_highschool | varchar(255) | YES |  | NULL |  |
| education_highschool_year | varchar(10) | YES |  | NULL |  |
| passport_id | int(10) unsigned | YES |  | 0 |  |
| passport_applicant | int(10) unsigned | YES |  | NULL |  |
| passport_number | varchar(100) | YES |  | NULL |  |
| passport_expiration | date | YES |  | NULL |  |
| passport_visible | tinyint(4) | YES |  | 0 |  |
| certificate_id | int(10) unsigned | YES |  | 0 |  |
| certificate_applicant | int(10) unsigned | YES |  | NULL |  |
| certificate_tesda | tinyint(3) unsigned | YES |  | NULL |  |
| certificate_info_sheet | int(3) unsigned | YES |  | NULL |  |
| certificate_authenticated | tinyint(3) unsigned | YES |  | NULL |  |
| certificate_authenticated_nbi | tinyint(3) unsigned | YES |  | NULL |  |
| certificate_insurance | varchar(255) | YES |  | NULL |  |
| certificate_medical_clinic | varchar(255) | YES |  | NULL |  |
| certificate_medical_exam_date | date | YES |  | 0000-00-00 |  |
| certificate_medical_result | varchar(255) | YES |  | NULL |  |
| certificate_medical_remarks | text | YES |  | NULL |  |
| certificate_medical_expiration | date | YES |  | 0000-00-00 |  |
| certificate_pdos | tinyint(3) unsigned | YES |  | NULL |  |
| certificate_pt_result | varchar(255) | YES |  | NULL |  |
| certificate_pt_result_date | date | YES |  | 0000-00-00 |  |
| certificate_philhealth | tinyint(3) unsigned | YES |  | NULL |  |
| certificate_m1b | tinyint(3) unsigned | YES |  | NULL |  |
| requirement_id | int(10) unsigned | YES |  | 0 |  |
| requirement_applicant | int(10) unsigned | YES |  | NULL |  |
| requirement_visa | tinyint(4) unsigned | YES |  | NULL |  |
| requirement_visa_date | date | YES |  | NULL |  |
| requirement_visa_release_date | date | YES |  | NULL |  |
| requirement_visa_expiration | date | YES |  | NULL |  |
| requirement_oec_number | varchar(100) | YES |  | NULL |  |
| requirement_oec_submission_date | date | YES |  | NULL |  |
| requirement_oec_release_date | date | YES |  | NULL |  |
| requirement_owwa_certificate | varchar(255) | YES |  | NULL |  |
| requirement_owwa_schedule | date | YES |  | NULL |  |
| requirement_contract | date | YES |  | NULL |  |
| requirement_mofa | varchar(255) | YES |  | NULL |  |
| requirement_job_offer | int(11) | YES |  | NULL |  |
| position_id | int(10) unsigned | YES |  | NULL |  |
| position_name | varchar(255) | YES |  | NULL |  |
| country_id | int(10) unsigned | YES |  | NULL |  |
| country_name | varchar(100) | YES |  | NULL |  |
| country_code | varchar(10) | YES |  | NULL |  |
| country_abbr | varchar(10) | YES |  | NULL |  |
| agent_id | int(10) unsigned | YES |  | NULL |  |
| agent_first | varchar(100) | YES |  | NULL |  |
| agent_last | varchar(100) | YES |  | NULL |  |
| requirement_remarks | text | YES |  | NULL |  |
| requirement_school_records | varchar(255) | YES |  | NULL |  |
| requirement_coe | tinyint(3) unsigned | YES |  | NULL |  |
| requirement_picture_status | varchar(100) | YES |  | NULL |  |
| requirement_trade_test | tinyint(3) unsigned | YES |  | NULL |  |
| job_id | int(10) unsigned | YES |  | NULL |  |
| job_employer | int(10) unsigned | YES |  | NULL |  |
| job_position | int(10) unsigned | YES |  | NULL |  |
| job_gender | varchar(10) | YES |  | NULL |  |
| job_salary | float | YES |  | NULL |  |
| job_total | int(10) unsigned | YES |  | NULL |  |
| job_occupied | int(10) unsigned | YES |  | NULL |  |
| job_name | varchar(255) | YES |  | NULL |  |
| job_content | text | YES |  | NULL |  |
| job_status | int(11) unsigned | YES |  | 0 |  |
| job_remarks | text | YES |  | NULL |  |
| user_id | int(10) unsigned | YES |  | NULL |  |
| user_name | varchar(100) | YES |  | NULL |  |
| user_password | varchar(255) | YES |  | NULL |  |
| user_fullname | varchar(100) | YES |  | NULL |  |
| user_email | varchar(100) | YES |  | NULL |  |
| user_type | int(10) unsigned | YES |  | 0 |  |
| user_status | int(10) unsigned | YES |  | NULL |  |
| user_lastlogin | timestamp | YES |  | NULL |  |
| applicant_slug | varchar(255) | YES |  | NULL |  |
| applicant_employer | int(10) unsigned | YES |  | 0 |  |
| applicant_job | int(10) unsigned | YES |  | 0 |  |
| job_revenue | float unsigned | YES |  | 0 |  |
| employer_id | int(10) unsigned | YES |  | NULL |  |
| employer_user | int(11) | YES |  | NULL |  |
| employer_no | varchar(10) | YES |  | NULL |  |
| employer_name | varchar(100) | YES |  | NULL |  |
| employer_remarks | text | YES |  | NULL |  |
| employer_contact_person | varchar(100) | YES |  | NULL |  |
| employer_contact | varchar(100) | YES |  | NULL |  |
| employer_email | varchar(100) | YES |  | NULL |  |
| employer_address | varchar(255) | YES |  | NULL |  |
| employer_country | int(11) | YES |  | NULL |  |
| employer_source_agency | int(11) unsigned | YES |  | 0 |  |
| employer_source_agent | int(11) unsigned | YES |  | 0 |  |
| employer_agency_commission | float unsigned | YES |  | 0 |  |
| employer_agent_commission | float unsigned | YES |  | 0 |  |
| requirement_offer_salary | float unsigned | YES |  | NULL |  |
| requirement_ticket | varchar(255) | YES |  | NULL |  |
| passport_issue_place | varchar(255) | YES |  | NULL |  |
| passport_issue | date | YES |  | NULL |  |
| applicant_paid | tinyint(4) unsigned | NO |  | NULL |  |
## Table: `applicants_logs_view`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| log_id | bigint(20) unsigned | NO |  | NULL |  |
| log_applicant | int(10) unsigned | NO |  | NULL |  |
| log_employer | int(10) unsigned | YES |  | NULL |  |
| log_status | int(10) unsigned | NO |  | NULL |  |
| log_country | int(10) unsigned | NO |  | NULL |  |
| log_date | date | YES |  | NULL |  |
| log_remarks | text | YES |  | NULL |  |
| log_createdby | int(10) unsigned | NO |  | NULL |  |
| log_created | timestamp | YES |  | NULL |  |
| country_id | int(10) unsigned | YES |  | NULL |  |
| country_name | varchar(100) | YES |  | NULL |  |
| country_code | varchar(10) | YES |  | NULL |  |
| country_abbr | varchar(10) | YES |  | NULL |  |
| employer_id | int(10) unsigned | YES |  | NULL |  |
| employer_user | int(11) | YES |  | NULL |  |
| employer_source_agency | int(11) unsigned | YES |  | 0 |  |
| employer_source_agent | int(11) unsigned | YES |  | 0 |  |
| employer_no | varchar(10) | YES |  | NULL |  |
| employer_name | varchar(100) | YES |  | NULL |  |
| employer_contact_person | varchar(100) | YES |  | NULL |  |
| employer_contact | varchar(100) | YES |  | NULL |  |
| employer_email | varchar(100) | YES |  | NULL |  |
| employer_address | varchar(255) | YES |  | NULL |  |
| employer_country | int(11) | YES |  | NULL |  |
| employer_remarks | text | YES |  | NULL |  |
| user_id | int(10) unsigned | YES |  | NULL |  |
| user_name | varchar(100) | YES |  | NULL |  |
| user_password | varchar(255) | YES |  | NULL |  |
| user_fullname | varchar(100) | YES |  | NULL |  |
| user_email | varchar(100) | YES |  | NULL |  |
| user_type | int(10) unsigned | YES |  | 0 |  |
| user_status | int(10) unsigned | YES |  | NULL |  |
| user_updated | timestamp | YES |  | NULL |  |
| applicant_id | int(10) unsigned | NO |  | 0 |  |
| applicant_first | varchar(100) | NO |  | NULL |  |
| applicant_middle | varchar(100) | YES |  | NULL |  |
| applicant_last | varchar(100) | NO |  | NULL |  |
| applicant_name | varchar(302) | YES |  | NULL |  |
| applicant_suffix | varchar(10) | YES |  | NULL |  |
| applicant_birthdate | date | YES |  | NULL |  |
| applicant_age | int(10) unsigned | YES |  | NULL |  |
| applicant_gender | varchar(10) | YES |  | NULL |  |
| applicant_contacts | varchar(255) | YES |  | NULL |  |
| applicant_address | varchar(255) | YES |  | NULL |  |
| applicant_email | varchar(100) | YES |  | NULL |  |
| applicant_nationality | varchar(100) | YES |  | NULL |  |
| applicant_civil_status | varchar(100) | YES |  | NULL |  |
| applicant_religion | varchar(100) | YES |  | NULL |  |
| applicant_languages | varchar(255) | YES |  | NULL |  |
| applicant_height | varchar(10) | YES |  | NULL |  |
| applicant_weight | varchar(10) | YES |  | NULL |  |
| applicant_position_type | varchar(10) | YES |  | NULL |  |
| applicant_preferred_position | int(10) unsigned | YES |  | NULL |  |
| applicant_expected_salary | float unsigned | YES |  | NULL |  |
| applicant_preferred_country | int(5) | YES |  | NULL |  |
| applicant_other_skills | text | YES |  | NULL |  |
| applicant_cv | varchar(255) | YES |  | NULL |  |
| applicant_photo | varchar(255) | YES |  | NULL |  |
| applicant_status | int(10) unsigned | YES |  | 0 |  |
| applicant_source | int(11) | YES |  | 0 |  |
| applicant_remarks | text | YES |  | NULL |  |
| applicant_date_applied | date | YES |  | NULL |  |
| applicant_employer | int(10) unsigned | YES |  | 0 |  |
| applicant_job | int(10) unsigned | YES |  | 0 |  |
| applicant_slug | varchar(255) | YES |  | NULL |  |
| employer_slug | varchar(255) | YES |  | NULL |  |
## Table: `appliocant_train`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| t_id | int(11) | NO |  | NULL |  |
| t_provider | varchar(60) | NO |  | NULL |  |
| t_place | varchar(100) | NO |  | NULL |  |
| t_issue | date | NO |  | NULL |  |
| t_expired | date | NO |  | NULL |  |
| t_name | varchar(60) | NO |  | NULL |  |
| m_app | int(3) | NO |  | NULL |  |
| tissue | date | NO |  | NULL |  |
| tno | varchar(20) | NO |  | NULL |  |
## Table: `applicants_others`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| applicant_id | int(11) | NO |  | NULL |  |
| pos_in_fam | varchar(255) | YES |  | NULL |  |
| no_of_bro | varchar(255) | YES |  | NULL |  |
| no_of_sis | varchar(255) | YES |  | NULL |  |
| nam_of_fat | varchar(255) | YES |  | NULL |  |
| occ_of_fat | varchar(255) | YES |  | NULL |  |
| occ_of_mom | varchar(255) | YES |  | NULL |  |
| relative_name | varchar(255) | YES |  | NULL |  |
| relative_mobile | varchar(255) | YES |  | NULL |  |
| partner_husband | varchar(255) | YES |  | NULL |  |
| partner_occupation | varchar(255) | YES |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
## Table: `archive_applicant`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| applicant_id | int(10) unsigned | NO |  | NULL |  |
| applicant_first | varchar(100) | NO |  | NULL |  |
| applicant_middle | varchar(100) | YES |  | NULL |  |
| applicant_last | varchar(100) | NO |  | NULL |  |
| applicant_suffix | varchar(10) | YES |  | NULL |  |
| applicant_birthdate | date | YES |  | NULL |  |
| applicant_age | int(10) unsigned | YES |  | NULL |  |
| applicant_gender | varchar(10) | YES |  | NULL |  |
| applicant_contacts | varchar(255) | YES |  | NULL |  |
| applicant_address | varchar(255) | YES |  | NULL |  |
| applicant_email | varchar(100) | YES |  | NULL |  |
| applicant_nationality | varchar(100) | YES |  | NULL |  |
| applicant_civil_status | varchar(100) | YES |  | NULL |  |
| applicant_religion | varchar(100) | YES |  | NULL |  |
| applicant_languages | varchar(255) | YES |  | NULL |  |
| applicant_height | varchar(10) | YES |  | NULL |  |
| applicant_weight | varchar(10) | YES |  | NULL |  |
| applicant_position_type | varchar(10) | YES |  | NULL |  |
| applicant_preferred_position | int(10) unsigned | YES |  | NULL |  |
| applicant_expected_salary | float unsigned | YES |  | NULL |  |
| applicant_preferred_country | varchar(100) | YES |  | NULL |  |
| applicant_other_skills | text | YES |  | NULL |  |
| applicant_cv | varchar(255) | YES |  | NULL |  |
| applicant_photo | varchar(255) | YES |  | NULL |  |
| applicant_status | int(10) unsigned | YES |  | NULL |  |
| applicant_employer | int(10) unsigned | YES |  | 0 |  |
| applicant_job | int(10) unsigned | YES |  | 0 |  |
| applicant_source | int(11) | YES |  | 0 |  |
| applicant_slug | varchar(255) | YES |  | NULL |  |
| applicant_remarks | text | YES |  | NULL |  |
| applicant_date_applied | date | YES |  | NULL |  |
| applicant_createdby | int(10) unsigned | YES |  | NULL |  |
| applicant_updatedby | int(10) unsigned | YES |  | NULL |  |
| applicant_created | timestamp | YES |  | NULL |  |
| applicant_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
| applicant_archivedby | int(10) unsigned | YES |  | 0 |  |
## Table: `assign`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| a_id | int(11) | NO |  | NULL |  |
| a_employer | int(5) | NO |  | NULL |  |
| a_user | int(5) | NO |  | NULL |  |
## Table: `assign1`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| a_id | int(11) | NO |  | NULL |  |
| a_employer | int(5) | NO |  | NULL |  |
| a_user | int(5) | NO |  | NULL |  |
## Table: `bank_accounts`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| bank_id | int(11) | NO |  | NULL |  |
| bank_date | date | NO |  | NULL |  |
| bank_name | varchar(30) | NO |  | NULL |  |
| bank_num | varchar(50) | NO |  | NULL |  |
| bank_currency | varchar(50) | NO |  | NULL |  |
| bank_remarks | varchar(100) | NO |  | NULL |  |
| bank_user_type | int(3) | NO |  | NULL |  |
## Table: `bill`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| bill_id | int(10) unsigned | NO |  | NULL |  |
| bill_employer | int(10) unsigned | YES |  | NULL |  |
| bill_applicant | int(10) unsigned | YES |  | NULL |  |
| bill_amount | float unsigned | YES |  | 0 |  |
| bill_employer_cost | float unsigned | YES |  | 0 |  |
| bill_applicant_cost | float unsigned | YES |  | 0 |  |
| bill_employer_deposit | float unsigned | YES |  | NULL |  |
| bill_applicant_deposit | float unsigned | YES |  | NULL |  |
| bill_status | int(10) unsigned | YES |  | 0 |  |
| bill_remarks | text | YES |  | NULL |  |
| bill_createdby | int(10) unsigned | YES |  | NULL |  |
| bill_updatedby | int(10) unsigned | YES |  | NULL |  |
| bill_created | timestamp | YES |  | NULL |  |
| bill_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `bill_detail`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| detail_id | int(10) unsigned | NO |  | NULL |  |
| detail_bill | int(10) unsigned | YES |  | NULL |  |
| detail_fee | int(10) unsigned | YES |  | NULL |  |
| detail_amount | float unsigned | YES |  | NULL |  |
| detail_employer | int(10) unsigned | YES |  | NULL |  |
| detail_applicant | int(10) unsigned | YES |  | NULL |  |
| detail_employer_cost | float unsigned | YES |  | NULL |  |
| detail_applicant_cost | float unsigned | YES |  | NULL |  |
| detail_employer_deposit | float unsigned | YES |  | NULL |  |
| detail_applicant_deposit | float unsigned | YES |  | NULL |  |
| detail_employer_balance | float unsigned | YES |  | NULL |  |
| detail_applicant_balance | float unsigned | YES |  | NULL |  |
| detail_remarks | text | YES |  | NULL |  |
| detail_createdby | int(10) unsigned | YES |  | NULL |  |
| detail_updatedby | int(10) unsigned | YES |  | NULL |  |
| detail_created | timestamp | YES |  | NULL |  |
| detail_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `bill_payment_applicant`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| payment_id | int(10) unsigned | NO |  | NULL |  |
| payment_or | varchar(255) | YES |  | NULL |  |
| payment_bill | int(10) unsigned | YES |  | NULL |  |
| payment_applicant | int(10) unsigned | YES |  | NULL |  |
| payment_amount | float unsigned | YES |  | NULL |  |
| payment_remarks | text | YES |  | NULL |  |
| payment_createdby | int(10) unsigned | YES |  | NULL |  |
| payment_updatedby | int(10) unsigned | YES |  | NULL |  |
| payment_created | timestamp | YES |  | NULL |  |
| payment_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `bill_payment_employer`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| payment_id | int(10) unsigned | NO |  | NULL |  |
| payment_or | varchar(255) | YES |  | NULL |  |
| payment_bill | int(10) unsigned | YES |  | NULL |  |
| payment_employer | int(10) unsigned | YES |  | NULL |  |
| payment_amount | float unsigned | YES |  | NULL |  |
| payment_remarks | text | YES |  | NULL |  |
| payment_createdby | int(10) unsigned | YES |  | NULL |  |
| payment_updatedby | int(10) unsigned | YES |  | NULL |  |
| payment_created | timestamp | YES |  | NULL |  |
| payment_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `billing_employer_view`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| bill_id | int(10) unsigned | YES |  | NULL |  |
| bill_employer | int(10) unsigned | YES |  | NULL |  |
| bill_applicant | int(10) unsigned | YES |  | NULL |  |
| bill_amount | float unsigned | YES |  | NULL |  |
| bill_employer_cost | float unsigned | YES |  | NULL |  |
| bill_applicant_cost | float unsigned | YES |  | NULL |  |
| bill_employer_deposit | float unsigned | YES |  | NULL |  |
| bill_applicant_deposit | float unsigned | YES |  | NULL |  |
| bill_status | int(10) unsigned | YES |  | NULL |  |
| bill_remarks | text | YES |  | NULL |  |
| bill_createdby | int(10) unsigned | YES |  | NULL |  |
| bill_updatedby | int(10) unsigned | YES |  | NULL |  |
| bill_created | timestamp | NO |  | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
| bill_updated | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| employer_id | int(10) unsigned | YES |  | NULL |  |
| employer_user | int(11) | YES |  | NULL |  |
| employer_no | varchar(10) | YES |  | NULL |  |
| employer_name | varchar(100) | YES |  | NULL |  |
| employer_remarks | text | YES |  | NULL |  |
| employer_contact_person | varchar(100) | YES |  | NULL |  |
| employer_contact | varchar(100) | YES |  | NULL |  |
| employer_email | varchar(100) | YES |  | NULL |  |
| employer_address | varchar(255) | YES |  | NULL |  |
| employer_country | int(11) | YES |  | NULL |  |
| employer_source_agency | int(11) unsigned | YES |  | NULL |  |
| employer_source_agent | int(11) unsigned | YES |  | NULL |  |
| employer_agency_commission | float unsigned | YES |  | NULL |  |
| employer_agent_commission | float unsigned | YES |  | NULL |  |
| employer_agency_commission_from | varchar(255) | YES |  | NULL |  |
| employer_agent_commission_from | varchar(255) | YES |  | NULL |  |
| employer_slug | varchar(255) | YES |  | NULL |  |
| employer_createdby | int(10) unsigned | YES |  | NULL |  |
| employer_updatedby | int(10) unsigned | YES |  | NULL |  |
| employer_created | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| employer_updated | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| amount | double | YES |  | NULL |  |
| debit | double | YES |  | NULL |  |
| credit | double | YES |  | NULL |  |
| applicants | bigint(21) | YES |  | NULL |  |
## Table: `cache`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| key | varchar(255) | NO |  | NULL |  |
| value | text | NO |  | NULL |  |
| expiration | int(11) | NO |  | NULL |  |
## Table: `cash_advance_logs`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| recruitment_agent_id | int(11) | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| current_status | varchar(255) | NO |  | NULL |  |
| remaining_commission | int(11) | NO |  | NULL |  |
| cash_advance | int(11) | NO |  | NULL |  |
| current_balance | int(11) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| deleted_at | timestamp | YES |  | NULL |  |
## Table: `cash_transaction`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| cash_id | int(11) | NO |  | NULL |  |
| cash_bank_in | int(11) | NO |  | NULL |  |
| cash_date | datetime | YES |  | CURRENT_TIMESTAMP |  |
| cash_amount | double | NO |  | NULL |  |
| cash_convertion | double | NO |  | NULL |  |
| cash_type | int(11) | NO |  | NULL |  |
| cash_bank_out | int(11) | NO |  | NULL |  |
| bank_remarks | varchar(100) | NO |  | NULL |  |
| cashadmad | int(2) | NO |  | NULL |  |
| refids | int(20) | NO |  | NULL |  |
| refid | int(20) | YES |  | NULL |  |
## Table: `category`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| category_id | int(10) unsigned | NO |  | NULL |  |
| category_name | varchar(255) | YES |  | NULL |  |
| category_photo | varchar(255) | YES |  | NULL |  |
| category_createdby | int(10) unsigned | YES |  | NULL |  |
| category_created | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `cash_advance_transactions`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| agent_id | int(11) | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| current_commission | int(11) | NO |  | NULL |  |
| cash_advance | int(11) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| deleted_at | timestamp | YES |  | NULL |  |
## Table: `category_positions`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| rel_id | int(10) unsigned | NO |  | NULL |  |
| rel_category | int(10) unsigned | YES |  | NULL |  |
| rel_position | int(10) unsigned | YES |  | NULL |  |
| rel_position_type | varchar(255) | YES |  | NULL |  |
| rel_createdby | int(10) unsigned | YES |  | NULL |  |
| rel_created | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `client`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| clinet_id | int(11) | NO |  | NULL |  |
| client_name | varchar(100) | NO |  | NULL |  |
| client_contact | varchar(100) | NO |  | NULL |  |
| client_position | varchar(100) | NO |  | NULL |  |
| client_address | varchar(100) | NO |  | NULL |  |
| client_pos | varchar(50) | NO |  | NULL |  |
## Table: `client_pos`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| pos_id | int(11) | NO |  | NULL |  |
| pos_name | varchar(100) | NO |  | NULL |  |
## Table: `commission_marketing_agency`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| commission_id | int(10) unsigned | NO |  | NULL |  |
| commission_agency | int(10) unsigned | YES |  | NULL |  |
| commission_applicant | int(10) unsigned | YES |  | NULL |  |
| commission_employer | int(10) unsigned | YES |  | NULL |  |
| commission_bill | int(10) unsigned | YES |  | NULL |  |
| commission_placement_fee | tinyint(3) unsigned | YES |  | NULL |  |
| commission_service_fee | tinyint(3) unsigned | YES |  | NULL |  |
| commission_percentage | float unsigned | YES |  | NULL |  |
| commission_base_amount | float unsigned | YES |  | NULL |  |
| commission_amount | float unsigned | YES |  | NULL |  |
| commission_remarks | text | YES |  | NULL |  |
| commission_status | int(10) unsigned | YES |  | 0 |  |
| commission_voucher | varchar(255) | YES |  | NULL |  |
| commission_createdby | int(10) unsigned | YES |  | NULL |  |
| commission_updatedby | int(10) unsigned | YES |  | NULL |  |
| commission_created | timestamp | YES |  | NULL |  |
| commission_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `commission_marketing_agent`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| commission_id | int(10) unsigned | NO |  | NULL |  |
| commission_agent | int(10) unsigned | YES |  | NULL |  |
| commission_applicant | int(10) unsigned | YES |  | NULL |  |
| commission_employer | int(10) unsigned | YES |  | NULL |  |
| commission_bill | int(10) unsigned | YES |  | NULL |  |
| commission_placement_fee | tinyint(3) unsigned | YES |  | NULL |  |
| commission_service_fee | tinyint(3) unsigned | YES |  | NULL |  |
| commission_percentage | float unsigned | YES |  | NULL |  |
| commission_base_amount | float unsigned | YES |  | NULL |  |
| commission_amount | float unsigned | YES |  | NULL |  |
| commission_remarks | text | YES |  | NULL |  |
| commission_status | int(10) unsigned | YES |  | 0 |  |
| commission_voucher | varchar(255) | YES |  | NULL |  |
| commission_createdby | int(10) unsigned | YES |  | NULL |  |
| commission_updatedby | int(10) unsigned | YES |  | NULL |  |
| commission_created | timestamp | YES |  | NULL |  |
| commission_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `commission_recruitment_agent`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| commission_id | int(10) unsigned | NO |  | NULL |  |
| commission_agent | int(10) unsigned | YES |  | NULL |  |
| commission_applicant | int(10) unsigned | YES |  | NULL |  |
| commission_employer | int(10) unsigned | YES |  | 0 |  |
| commission_bill | int(10) unsigned | YES |  | NULL |  |
| commission_amount | float unsigned | YES |  | 0 |  |
| commission_remarks | text | YES |  | NULL |  |
| commission_status | int(10) unsigned | YES |  | 0 |  |
| commission_voucher | varchar(255) | YES |  | NULL |  |
| commission_createdby | int(10) unsigned | YES |  | NULL |  |
| commission_updatedby | int(10) unsigned | YES |  | NULL |  |
| commission_created | timestamp | YES |  | NULL |  |
| commission_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `contract_marketing_agency`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| contract_id | int(10) unsigned | NO |  | NULL |  |
| contract_agency | int(10) unsigned | YES |  | NULL |  |
| contract_employer | int(10) unsigned | YES |  | NULL |  |
| contract_percentage | float unsigned | YES |  | NULL |  |
| contract_placement_fee | tinyint(3) unsigned | YES |  | NULL |  |
| contract_service_fee | tinyint(3) unsigned | YES |  | NULL |  |
| contract_base_amount | float unsigned | YES |  | NULL |  |
| contract_amount | float unsigned | YES |  | NULL |  |
| contract_remarks | text | YES |  | NULL |  |
| contract_status | int(10) unsigned | YES |  | 0 |  |
| contract_createdby | int(10) unsigned | YES |  | NULL |  |
| contract_updatedby | int(10) unsigned | YES |  | NULL |  |
| contract_created | timestamp | YES |  | NULL |  |
| contract_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `contract_marketing_agent`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| contract_id | int(10) unsigned | NO |  | NULL |  |
| contract_agent | int(10) unsigned | YES |  | NULL |  |
| contract_employer | int(10) unsigned | YES |  | NULL |  |
| contract_percentage | float unsigned | YES |  | NULL |  |
| contract_placement_fee | tinyint(3) unsigned | YES |  | NULL |  |
| contract_service_fee | tinyint(3) unsigned | YES |  | NULL |  |
| contract_amount | float unsigned | YES |  | NULL |  |
| contract_remarks | text | YES |  | NULL |  |
| contract_status | int(10) unsigned | YES |  | 0 |  |
| contract_createdby | int(10) unsigned | YES |  | NULL |  |
| contract_updatedby | int(10) unsigned | YES |  | NULL |  |
| contract_created | timestamp | YES |  | NULL |  |
| contract_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `country`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| country_id | int(10) unsigned | NO |  | NULL |  |
| country_name | varchar(100) | NO |  | NULL |  |
| country_code | varchar(10) | YES |  | NULL |  |
| country_abbr | varchar(10) | YES |  | NULL |  |
| country_createdby | int(10) unsigned | YES |  | NULL |  |
| country_created | timestamp | YES |  | NULL |  |
## Table: `currencies`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| currency | varchar(255) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
## Table: `custom_fields`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| name | varchar(255) | NO |  | NULL |  |
| location | varchar(255) | NO |  | NULL |  |
| description | varchar(255) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
## Table: `custom_field_values`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| customFieldId | int(11) | NO |  | NULL |  |
| applicantID | int(11) | NO |  | NULL |  |
| value | varchar(255) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
## Table: `deduction`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| deduction_id | int(11) | NO |  | NULL |  |
| agent_id | int(11) | NO |  | NULL |  |
| deduction_date | date | NO |  | NULL |  |
| deduction_amount | double | NO |  | NULL |  |
| deduction_remarks | tinytext | NO |  | NULL |  |
| app_id | int(11) | NO |  | NULL |  |
| deductme | int(3) | NO |  | NULL |  |
## Table: `deployed`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| deployed_id | bigint(20) unsigned | NO |  | NULL |  |
| deployed_applicant | int(10) unsigned | NO |  | NULL |  |
| deployed_employer | int(10) unsigned | NO |  | NULL |  |
| deployed_job | int(10) unsigned | NO |  | NULL |  |
| deployed_country | int(10) unsigned | NO |  | NULL |  |
| deployed_position | int(10) unsigned | NO |  | NULL |  |
| deployed_salary | float unsigned | NO |  | NULL |  |
| deployed_remarks | text | NO |  | NULL |  |
| deployed_date | date | NO |  | NULL |  |
| deployed_createdby | int(10) unsigned | NO |  | NULL |  |
| deployed_updatedby | int(10) unsigned | NO |  | NULL |  |
| deployed_created | timestamp | NO |  | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
| deployed_updated | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| deployed_status | int(3) | NO |  | NULL |  |
## Table: `deployedmonthly`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| d_id | int(11) | NO |  | NULL |  |
| year | int(10) | NO |  | NULL |  |
| month | varchar(30) | NO |  | NULL |  |
| d_from | date | NO |  | NULL |  |
| d_to | date | NO |  | NULL |  |
## Table: `email_address`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| e_id | int(11) | NO |  | NULL |  |
| ref | varchar(100) | NO |  | NULL |  |
| agent_id | int(20) | NO |  | NULL |  |
| emp_id | int(11) | NO |  | NULL |  |
| method | varchar(50) | NO |  | NULL |  |
| method_date | date | NO |  | NULL |  |
| method_num | varchar(300) | NO |  | NULL |  |
| method_remarks | tinytext | NO |  | NULL |  |
| account | varchar(1000) | NO |  | NULL |  |
| amount | double | NO |  | NULL |  |
| description | varchar(2000) | NO |  | NULL |  |
| currency | varchar(50) | NO |  | NULL |  |
| date_ad | text | NO |  | NULL |  |
| email_country | varchar(50) | NO |  | NULL |  |
| email_status | int(10) unsigned | NO |  | NULL |  |
| email_created | datetime | NO |  | NULL |  |
| email_updated | datetime | NO |  | NULL |  |
| app_id | int(100) | NO |  | NULL |  |
| staff_id | int(11) | NO |  | NULL |  |
| sources | varchar(30) | NO |  | NULL |  |
| request_payment | int(11) | NO |  | NULL |  |
| requestby | varchar(20) | NO |  | NULL |  |
| e_remarks | varchar(50) | NO |  | NULL |  |
| type_payment | varchar(20) | NO |  | NULL |  |
| sup_id | int(11) | NO |  | NULL |  |
| createdby | varchar(20) | NO |  | NULL |  |
| duedate | date | NO |  | NULL |  |
| fund_source_id | int(11) | NO |  | NULL |  |
| chargeto | varchar(20) | NO |  | NULL |  |
| branch_type | int(3) | NO |  | NULL |  |
| country | int(3) | NO |  | NULL |  |
| date_ads | datetime | NO |  | NULL |  |
| cancel_date | date | NO |  | NULL |  |
| account_expense | int(3) | NO |  | NULL |  |
| date_up | date | NO |  | NULL |  |
| user_released | varchar(30) | NO |  | NULL |  |
| approved | varchar(30) | NO |  | NULL |  |
| chargeback | varchar(20) | NO |  | NULL |  |
| date_create | datetime | NO |  | NULL |  |
| regulars | tinytext | NO |  | NULL |  |
| hides | int(5) | NO |  | NULL |  |
| checkings | varchar(30) | NO |  | NULL |  |
| e_liq | varchar(15) | NO |  | NULL |  |
| e_liq1 | varchar(5) | NO |  | NULL |  |
## Table: `email_address6`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| tr_id | int(11) | NO |  | NULL |  |
| refid | int(20) | NO |  | NULL |  |
| payee | varchar(150) | NO |  | NULL |  |
| email_address | tinytext | NO |  | NULL |  |
| date1 | date | NO |  | NULL |  |
| or_num | date | NO |  | NULL |  |
| agent_id | varchar(500) | NO |  | NULL |  |
| emp_id | int(11) | NO |  | NULL |  |
| particular | text | NO |  | NULL |  |
| currency | varchar(100) | NO |  | NULL |  |
| amount | double | NO |  | NULL |  |
| user | varchar(100) | NO |  | NULL |  |
| received | varchar(500) | NO |  | NULL |  |
| email_updated | datetime | NO |  | NULL |  |
| email_created | datetime | NO |  | NULL |  |
| account | varchar(100) | NO |  | NULL |  |
| app_id | int(11) | NO |  | NULL |  |
| source_m | varchar(50) | NO |  | NULL |  |
| sources | varchar(30) | NO |  | NULL |  |
| collect_type | int(11) | NO |  | NULL |  |
| collect_status | int(11) | NO |  | NULL |  |
| method | varchar(20) | NO |  | NULL |  |
| type_payment | varchar(20) | NO |  | NULL |  |
| sendername | varchar(50) | NO |  | NULL |  |
| companyaccount | varchar(60) | NO |  | NULL |  |
| collect_bank_id | int(11) | NO |  | NULL |  |
| requestby | varchar(30) | NO |  | NULL |  |
| bankcharge | double | NO |  | NULL |  |
| categorys | int(11) | NO |  | NULL |  |
| qty | int(11) | NO |  | NULL |  |
| clinet_id | int(11) | NO |  | NULL |  |
| approvedby | int(3) | NO |  | NULL |  |
| liqu | varchar(15) | NO |  | NULL |  |
| discount | double | NO |  | NULL |  |
| invoice | varchar(50) | NO |  | NULL |  |
| remarks2 | tinytext | NO |  | NULL |  |
| amount1 | double | NO |  | NULL |  |
## Table: `email_address4`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| exp_id | int(11) | NO |  | NULL |  |
| nickname | varchar(100) | NO |  | NULL |  |
| email_address | varchar(300) | NO |  | NULL |  |
| agent_id | int(11) | NO |  | NULL |  |
| emp_id | int(11) | NO |  | NULL |  |
| app_id | int(11) | NO |  | NULL |  |
| date_med | varchar(2000) | NO |  | NULL |  |
| findings | text | NO |  | NULL |  |
| country | varchar(100) | NO |  | NULL |  |
| receivable | double | NO |  | NULL |  |
| amount_paid | double | NO |  | NULL |  |
| date_paid | varchar(2000) | NO |  | NULL |  |
| date_release | varchar(2000) | NO |  | NULL |  |
| exp_remarks | tinytext | NO |  | NULL |  |
| exp_status | int(11) | NO |  | NULL |  |
| currency | varchar(50) | NO |  | NULL |  |
| exp_type | varchar(150) | NO |  | NULL |  |
| email_created | datetime | NO |  | NULL |  |
| email_updated | datetime | NO |  | NULL |  |
| date_transaction | date | NO |  | NULL |  |
| user | varchar(100) | NO |  | NULL |  |
| type_status | varchar(50) | NO |  | NULL |  |
| othercharge | double | NO |  | NULL |  |
| status_paid | int(5) | NO |  | NULL |  |
| collect | int(5) | NO |  | NULL |  |
| sources | varchar(30) | NO |  | NULL |  |
| status_me | int(3) | NO |  | NULL |  |
## Table: `email_address_test`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| account_expense | int(11) | NO |  | NULL |  |
| status_collect | int(3) | NO |  | NULL |  |
| e_id | int(11) | NO |  | NULL |  |
| email_address | varchar(100) | NO |  | NULL |  |
| ref | varchar(100) | NO |  | NULL |  |
| agent_id | int(20) | NO |  | NULL |  |
| emp_id | int(11) | NO |  | NULL |  |
| method | varchar(50) | NO |  | NULL |  |
| account | varchar(1000) | NO |  | NULL |  |
| amount | double | NO |  | NULL |  |
| description | varchar(2000) | NO |  | NULL |  |
| currency | varchar(50) | NO |  | NULL |  |
| date_ad | text | NO |  | NULL |  |
| email_country | varchar(50) | YES |  | NULL |  |
| email_status | int(10) unsigned | YES |  | NULL |  |
| email_created | datetime | YES |  | NULL |  |
| email_updated | datetime | YES |  | NULL |  |
| app_id | int(11) | NO |  | NULL |  |
| staff_id | int(11) | NO |  | NULL |  |
| sources | varchar(30) | NO |  | NULL |  |
| request_payment | int(11) | NO |  | NULL |  |
| requestby | varchar(20) | NO |  | NULL |  |
| payment_to | varchar(30) | NO |  | NULL |  |
| e_remarks | varchar(50) | NO |  | NULL |  |
| e_dateupdate | date | YES |  | NULL |  |
| type_payment | varchar(20) | NO |  | NULL |  |
| sup_id | int(11) | NO |  | NULL |  |
| createdby | varchar(20) | NO |  | NULL |  |
| duedate | date | YES |  | NULL |  |
| fund_source_id | int(11) | NO |  | NULL |  |
| chargeto_bank | int(11) | NO |  | NULL |  |
| mineme | int(2) | NO |  | NULL |  |
| accountmanager | int(3) | NO |  | NULL |  |
| chargeto | varchar(20) | NO |  | NULL |  |
| branch_type | int(3) | NO |  | NULL |  |
| country | int(3) | NO |  | NULL |  |
| tm1 | int(3) | NO |  | NULL |  |
| tm2 | int(3) | NO |  | NULL |  |
| appt | int(2) | NO |  | NULL |  |
## Table: `eminvoice`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| em_id | int(11) | NO |  | NULL |  |
| em_ref | varchar(100) | NO |  | NULL |  |
| em_branch | int(3) | NO |  | NULL |  |
## Table: `employer_reservation`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| reservation_id | bigint(20) unsigned | NO |  | NULL |  |
| reservation_employer | int(10) unsigned | YES |  | NULL |  |
| reservation_applicant | int(10) unsigned | YES |  | NULL |  |
| reservation_expiration | date | YES |  | NULL |  |
| reservation_status | tinyint(3) unsigned | YES |  | 0 |  |
| reservation_remarks | text | YES |  | NULL |  |
| reservation_date | date | YES |  | NULL |  |
| reservation_createdby | int(10) unsigned | YES |  | NULL |  |
| reservation_updatedby | int(10) unsigned | YES |  | NULL |  |
| reservation_created | timestamp | YES |  | NULL |  |
| reservation_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `employer`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| employer_id | int(10) unsigned | NO |  | NULL |  |
| rs_id | int(11) | NO |  | NULL |  |
| employer_user | int(11) | YES |  | NULL |  |
| employer_no | varchar(10) | YES |  | NULL |  |
| employer_name | varchar(100) | NO |  | NULL |  |
| employer_remarks | text | YES |  | NULL |  |
| employer_contact_person | varchar(100) | YES |  | NULL |  |
| employer_contact | varchar(100) | YES |  | NULL |  |
| employer_email | varchar(100) | YES |  | NULL |  |
| employer_address | varchar(255) | YES |  | NULL |  |
| employer_country | int(11) | YES |  | NULL |  |
| employer_source_agency | int(11) unsigned | YES |  | 0 |  |
| employer_source_agent | int(11) unsigned | YES |  | 0 |  |
| employer_agency_commission | float unsigned | YES |  | 0 |  |
| employer_agent_commission | float unsigned | YES |  | 0 |  |
| employer_agency_commission_from | varchar(255) | YES |  | NULL |  |
| employer_agent_commission_from | varchar(255) | YES |  | NULL |  |
| employer_slug | varchar(255) | YES |  | NULL |  |
| employer_createdby | int(10) unsigned | YES |  | NULL |  |
| employer_updatedby | int(10) unsigned | YES |  | NULL |  |
| employer_created | timestamp | YES |  | NULL |  |
| employer_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
| agree_ftw | double | NO |  | NULL |  |
| agree_visa | double | NO |  | NULL |  |
| agree_deployed | double | NO |  | NULL |  |
| agree_driver1 | double | NO |  | NULL |  |
| agree_driver2 | double | NO |  | NULL |  |
| agree_driver3 | double | NO |  | NULL |  |
| agree_direct1 | double | NO |  | NULL |  |
| agree_direct2 | double | NO |  | NULL |  |
| agree_direct3 | double | NO |  | NULL |  |
| numberdays | double | NO |  | NULL |  |
| agree_sent | double | NO |  | NULL |  |
| agree_before | double | NO |  | NULL |  |
| employer_selections | int(3) | NO |  | NULL |  |
| remarks | varchar(100) | YES |  | NULL |  |
## Table: `employer_selected`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| selected_id | bigint(20) unsigned | NO |  | NULL |  |
| selected_employer | int(10) unsigned | YES |  | NULL |  |
| selected_applicant | int(10) unsigned | YES |  | NULL |  |
| selected_remarks | text | YES |  | NULL |  |
| selected_date | date | YES |  | NULL |  |
| selected_createdby | int(10) unsigned | YES |  | NULL |  |
| selected_updatedby | int(10) unsigned | YES |  | NULL |  |
| selected_created | timestamp | YES |  | NULL |  |
| selected_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `fee`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| fee_id | int(10) unsigned | NO |  | NULL |  |
| fee_name | varchar(150) | YES |  | NULL |  |
| fee_group | varchar(100) | YES |  | NULL |  |
| fee_shareable | tinyint(4) | YES |  | 0 |  |
| fee_createdby | int(10) unsigned | YES |  | NULL |  |
| fee_updatedby | int(10) unsigned | YES |  | NULL |  |
| fee_created | timestamp | YES |  | NULL |  |
| fee_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `funded`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| funded_id | int(11) | NO |  | NULL |  |
| funded_date | date | NO |  | NULL |  |
| funded_amount | double | NO |  | NULL |  |
| funded_currency | varchar(10) | NO |  | NULL |  |
| fundeddate | datetime | NO |  | NULL | on update CURRENT_TIMESTAMP |
| funded_remarks | varchar(100) | NO |  | NULL |  |
## Table: `hearings`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| htype | varchar(25) | NO |  | NULL |  |
| hdate | date | NO |  | NULL |  |
| href | varchar(25) | NO |  | NULL |  |
| hloc | varchar(60) | NO |  | NULL |  |
| hremarks | text | NO |  | NULL |  |
| hid | int(11) | NO |  | NULL |  |
| happ | int(11) | NO |  | NULL |  |
| htime | varchar(25) | NO |  | NULL |  |
| hattend | varchar(30) | NO |  | NULL |  |
## Table: `job`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| job_id | int(10) unsigned | NO |  | NULL |  |
| job_employer | int(10) unsigned | YES |  | NULL |  |
| job_position | int(10) unsigned | YES |  | NULL |  |
| job_gender | varchar(10) | YES |  | NULL |  |
| job_salary | float | YES |  | NULL |  |
| job_salary_from | float unsigned | YES |  | NULL |  |
| job_salary_to | float unsigned | YES |  | NULL |  |
| job_total | int(10) unsigned | YES |  | NULL |  |
| job_occupied | int(10) unsigned | YES |  | NULL |  |
| job_name | varchar(255) | YES |  | NULL |  |
| job_content | text | YES |  | NULL |  |
| job_dollar_exchange | float unsigned | YES |  | NULL |  |
| job_revenue | float unsigned | YES |  | 0 |  |
| job_status | int(11) unsigned | YES |  | 0 |  |
| job_sstatus | varchar(255) | NO |  | NULL |  |
| job_remarks | text | YES |  | NULL |  |
| job_createdby | int(10) unsigned | YES |  | NULL |  |
| job_updatedby | int(10) unsigned | YES |  | NULL |  |
| job_created | timestamp | YES |  | NULL |  |
| job_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
| rid | int(11) | NO |  | NULL |  |
| job_accre | varchar(50) | NO |  | NULL |  |
| job_end | date | NO |  | NULL |  |
| job_requirements | tinytext | NO |  | NULL |  |
## Table: `job_fee`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| fee_id | bigint(20) unsigned | NO |  | NULL |  |
| fee_job | int(10) unsigned | YES |  | NULL |  |
| fee_fee | int(10) unsigned | YES |  | NULL |  |
| fee_amount | float unsigned | YES |  | NULL |  |
| fee_employer | tinyint(3) unsigned | YES |  | NULL |  |
| fee_applicant | tinyint(10) unsigned | YES |  | NULL |  |
| fee_employer_cost | float unsigned | YES |  | NULL |  |
| fee_applicant_cost | float unsigned | YES |  | NULL |  |
| fee_createdby | int(10) unsigned | YES |  | NULL |  |
| fee_updatedby | int(10) unsigned | YES |  | NULL |  |
| fee_created | timestamp | YES |  | NULL |  |
| fee_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `liq_fra`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| fra_id | int(11) | NO |  | NULL |  |
| app_id | int(11) | NO |  | NULL |  |
| emp_id | int(11) | NO |  | NULL |  |
| fra_amount | double | NO |  | NULL |  |
| fra_remarks | text | NO |  | NULL |  |
| fra_tr_id | int(11) | NO |  | NULL |  |
| fra_user | varchar(20) | NO |  | NULL |  |
| l_date | date | NO |  | NULL |  |
| liq_replacement | varchar(100) | NO |  | NULL |  |
| liq_show | varchar(40) | NO |  | NULL |  |
| account | varchar(40) | NO |  | NULL |  |
## Table: `marketing_agency`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| agency_id | int(10) unsigned | NO |  | NULL |  |
| agency_name | varchar(100) | YES |  | NULL |  |
| agency_contact_person | varchar(255) | YES |  | NULL |  |
| agency_contacts | varchar(255) | YES |  | NULL |  |
| agency_address | varchar(255) | YES |  | NULL |  |
| agency_email | varchar(100) | YES |  | NULL |  |
| agency_remarks | text | YES |  | NULL |  |
| agency_createdby | int(10) unsigned | YES |  | NULL |  |
| agency_updatedby | int(10) unsigned | YES |  | NULL |  |
| agency_created | timestamp | YES |  | NULL |  |
| agency_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `marketing_agent`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| agent_id | int(10) unsigned | NO |  | NULL |  |
| agent_agency | int(11) | YES |  | NULL |  |
| agent_first | varchar(100) | YES |  | NULL |  |
| agent_last | varchar(100) | YES |  | NULL |  |
| agent_contacts | varchar(255) | YES |  | NULL |  |
| agent_email | varchar(100) | YES |  | NULL |  |
| agent_remarks | text | YES |  | NULL |  |
| agent_createdby | int(10) unsigned | YES |  | NULL |  |
| agent_updatedby | int(10) unsigned | YES |  | NULL |  |
| agent_created | timestamp | YES |  | NULL |  |
| agent_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `medicalhistory`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| m_id | int(11) | NO |  | NULL |  |
| certificate_medical_clinic | varchar(100) | NO |  | NULL |  |
| medical_fit | date | NO |  | NULL |  |
| certificate_medical_exam_date | date | NO |  | NULL |  |
| certificate_medical_result | varchar(50) | NO |  | NULL |  |
| certificate_medical_remarks | tinytext | NO |  | NULL |  |
| m_app | int(11) | NO |  | NULL |  |
| m_date | datetime | NO |  | CURRENT_TIMESTAMP |  |
| m_user | varchar(40) | NO |  | NULL |  |
| medical_certdate | varchar(20) | NO |  | NULL |  |
| medical_cert | varchar(20) | NO |  | NULL |  |
| medicaltype | varchar(25) | NO |  | NULL |  |
## Table: `meta`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| meta_id | bigint(20) unsigned | NO |  | NULL |  |
| meta_parent | int(10) unsigned | YES |  | NULL |  |
| meta_type | varchar(100) | YES |  | NULL |  |
| meta_key | varchar(255) | YES |  | NULL |  |
| meta_value | text | YES |  | NULL |  |
## Table: `migrations`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| migration | varchar(255) | NO |  | NULL |  |
| batch | int(11) | NO |  | NULL |  |
## Table: `multiple_lineups`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| applicant_employer | int(11) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| deleted_at | timestamp | YES |  | NULL |  |
## Table: `multiple_lineups1`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| applicant_employer | int(11) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| deleted_at | timestamp | YES |  | NULL |  |
## Table: `optional_statuses`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| optional_status | varchar(255) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
## Table: `or`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| or_id | bigint(20) unsigned | NO |  | NULL |  |
| or_number | varchar(255) | YES |  | NULL |  |
| or_amount | float unsigned | YES |  | NULL |  |
| or_employer | int(10) unsigned | YES |  | NULL |  |
| or_applicant | int(10) unsigned | YES |  | NULL |  |
| or_status | int(10) unsigned | YES |  | 0 |  |
| or_approvedby | int(10) unsigned | YES |  | 0 |  |
| or_remarks | text | YES |  | NULL |  |
| or_date | date | YES |  | NULL |  |
| or_createdby | int(10) unsigned | YES |  | NULL |  |
| or_created | timestamp | YES |  | NULL |  |
## Table: `options`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| option_id | int(11) | NO |  | NULL |  |
| option_name | varchar(160) | NO |  | NULL |  |
| option_value | varchar(160) | NO |  | NULL |  |
| option_remarks | varchar(500) | NO |  | NULL |  |
| updated_at | datetime | NO |  | NULL |  |
| created_at | datetime | NO |  | NULL |  |
| deleted_at | datetime | NO |  | NULL |  |
## Table: `payment_employer_detail`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| detail_id | bigint(20) unsigned | NO |  | NULL |  |
| detail_payment | int(10) unsigned | YES |  | 0 |  |
| detail_bill | int(10) unsigned | YES |  | 0 |  |
| detail_or | varchar(255) | YES |  | NULL |  |
| detail_employer | int(10) unsigned | YES |  | 0 |  |
| detail_applicant | int(10) unsigned | YES |  | 0 |  |
| detail_fee | int(10) unsigned | YES |  | 0 |  |
| detail_amount | float unsigned | YES |  | 0 |  |
| detail_balance | float unsigned | YES |  | 0 |  |
| detail_createdby | int(10) unsigned | YES |  | 0 |  |
| detail_created | timestamp | YES |  | NULL |  |
## Table: `paidall_employer_applicants`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| paidall_id | int(10) unsigned | NO |  | NULL |  |
| paidall_or | varchar(255) | YES |  | NULL |  |
| paidall_bill | int(10) unsigned | YES |  | NULL |  |
| paidall_employer | int(10) unsigned | YES |  | NULL |  |
| paidall_applicant | int(10) unsigned | YES |  | NULL |  |
| paidall_amount | float unsigned | YES |  | NULL |  |
| paidall_paid | timestamp | YES |  | NULL |  |
## Table: `payment_fra`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| fra_ids | int(11) | NO |  | NULL |  |
| app_id | int(11) | NO |  | NULL |  |
| emp_id | int(11) | NO |  | NULL |  |
| fra_amount | double | NO |  | NULL |  |
| fra_remarks | text | NO |  | NULL |  |
| fra_tr_id | int(11) | NO |  | NULL |  |
| fra_user | varchar(20) | NO |  | NULL |  |
| l_date | date | NO |  | NULL |  |
| fra_bank | varchar(100) | NO |  | NULL |  |
| fra_payments | varchar(50) | NO |  | NULL |  |
| fra_method | varchar(20) | NO |  | NULL |  |
| fra_account | varchar(50) | NO |  | NULL |  |
## Table: `payment_recruitment`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| payment_id | bigint(20) unsigned | NO |  | NULL |  |
| payment_or | varchar(255) | YES |  | NULL |  |
| payment_applicant | int(10) unsigned | YES |  | 0 |  |
| payment_agent | int(10) unsigned | YES |  | 0 |  |
| payment_amount | float unsigned | YES |  | 0 |  |
| payment_bill | int(10) unsigned | YES |  | 0 |  |
| payment_createdby | int(10) unsigned | YES |  | 0 |  |
| payment_created | timestamp | YES |  | NULL |  |
## Table: `payment_worker_detail`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| detail_id | bigint(20) unsigned | NO |  | NULL |  |
| detail_payment | int(10) unsigned | YES |  | NULL |  |
| detail_bill | int(10) unsigned | YES |  | 0 |  |
| detail_or | varchar(255) | YES |  | NULL |  |
| detail_applicant | int(10) unsigned | YES |  | 0 |  |
| detail_fee | int(10) unsigned | YES |  | 0 |  |
| detail_amount | float unsigned | YES |  | 0 |  |
| detail_balance | float unsigned | YES |  | 0 |  |
| detail_createdby | int(10) unsigned | YES |  | 0 |  |
| detail_created | timestamp | YES |  | NULL |  |
## Table: `position`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| position_id | int(10) unsigned | NO |  | NULL |  |
| position_name | varchar(255) | NO |  | NULL |  |
| position_type | varchar(10) | YES |  | NULL |  |
| position_status | int(10) unsigned | YES |  | 0 |  |
| position_createdby | int(10) unsigned | YES |  | NULL |  |
| position_updatedby | int(10) unsigned | YES |  | NULL |  |
| position_created | timestamp | YES |  | NULL |  |
| position_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
## Table: `resibo`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| resibo_id | int(3) | NO |  | NULL |  |
| resibo_connect | int(11) | NO |  | NULL |  |
| resibo_path | varchar(100) | NO |  | NULL |  |
| file_type | varchar(100) | NO |  | NULL |  |
| file_type1 | varchar(100) | NO |  | NULL |  |
| file_date | date | NO |  | NULL |  |
| file_user | varchar(30) | NO |  | NULL |  |
| file_remarks | tinytext | NO |  | NULL |  |
| file_datecreate | datetime | NO |  | CURRENT_TIMESTAMP |  |
## Table: `resibo12`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| resibo_id | int(3) | NO |  | NULL |  |
| resibo_connect | int(11) | NO |  | NULL |  |
| resibo_path | varchar(100) | NO |  | NULL |  |
| file_type | int(30) | NO |  | NULL |  |
| file_remarks | tinytext | NO |  | NULL |  |
| file_date | varchar(30) | NO |  | NULL |  |
| file_user | varchar(30) | NO |  | NULL |  |
## Table: `recruitment_agent`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| agent_id | int(10) unsigned | NO |  | NULL |  |
| agent_string_id | varchar(255) | NO |  | NULL |  |
| agent_first | varchar(100) | YES |  | NULL |  |
| agent_last | varchar(100) | YES |  | NULL |  |
| agent_contacts | varchar(255) | YES |  | NULL |  |
| agent_email | varchar(100) | YES |  | NULL |  |
| agent_commission | int(11) | NO |  | NULL |  |
| cash_advance | int(11) | NO |  | NULL |  |
| balance | int(11) | NO |  | NULL |  |
| agent_remarks | text | YES |  | NULL |  |
| agent_createdby | int(10) unsigned | YES |  | NULL |  |
| agent_updatedby | int(10) unsigned | YES |  | NULL |  |
| agent_created | timestamp | YES |  | NULL |  |
| agent_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
| agent_type | varchar(20) | NO |  | NULL |  |
| branch_type | varchar(20) | NO |  | NULL |  |
| branchme | int(3) | NO |  | NULL |  |
| branc_id | int(3) | NO |  | NULL |  |
## Table: `setcat`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| setcat_id | int(11) | NO |  | NULL |  |
| setcat_name | varchar(30) | NO |  | NULL |  |
## Table: `salary_transactions`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| salary_transaction_id | int(11) | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| payment_type | varchar(20) | NO |  | NULL |  |
| applicant_fullname | varchar(500) | NO |  | NULL |  |
| basic_pay | varchar(50) | NO |  | NULL |  |
| total_deduction | varchar(500) | NO |  | NULL |  |
| total_allowance | varchar(500) | NO |  | NULL |  |
| total_miscellaneous | varchar(500) | NO |  | NULL |  |
| tax | varchar(500) | NO |  | NULL |  |
| net_income | varchar(500) | NO |  | NULL |  |
| salary_date | datetime | NO |  | NULL |  |
| updated_at | datetime | NO |  | NULL |  |
| created_at | datetime | NO |  | NULL |  |
| deleted_at | datetime | NO |  | NULL |  |
## Table: `set_`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| set_id | int(11) | NO |  | NULL |  |
| account | varchar(400) | NO |  | NULL |  |
| set_type | varchar(50) | NO |  | NULL |  |
| set_cat | varchar(50) | NO |  | NULL |  |
| a_amount | double | NO |  | NULL |  |
| fixme | varchar(25) | NO |  | NULL |  |
## Table: `set_1`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| set_id | int(11) | NO |  | NULL |  |
| account | varchar(400) | NO |  | NULL |  |
| set_type | varchar(50) | NO |  | NULL |  |
| set_cat | varchar(50) | NO |  | NULL |  |
| a_amount | double | NO |  | NULL |  |
| fixme | varchar(25) | NO |  | NULL |  |
## Table: `settings`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| key | varchar(255) | NO |  | NULL |  |
| value | text | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
## Table: `statuses`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO | PRI | NULL | auto_increment |
| number | int(11) | NO |  | NULL |  |
| status | varchar(255) | NO |  | NULL |  |
| statusText | varchar(255) | NO |  | NULL |  |
| statusColors | varchar(255) | NO |  | NULL |  |
| orderby | int(11) | NO |  | NULL |  |
## Table: `statuses1`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| number | int(11) | NO |  | NULL |  |
| status | varchar(255) | NO |  | NULL |  |
| statusText | varchar(255) | NO |  | NULL |  |
| statusColors | varchar(255) | NO |  | NULL |  |
## Table: `subject`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| app_name | varchar(60) | NO |  | NULL |  |
| app_name1 | varchar(30) | NO |  | NULL |  |
| app_name2 | varchar(30) | NO |  | NULL |  |
| app_position | varchar(40) | NO |  | NULL |  |
| app_applied | varchar(20) | NO |  | NULL |  |
| app_fb | varchar(60) | NO |  | NULL |  |
| app_contact | varchar(30) | NO |  | NULL |  |
| app_status | tinytext | NO |  | NULL |  |
| app_status1 | varchar(30) | NO |  | NULL |  |
| app_remarks | text | NO |  | NULL |  |
| fra_name | varchar(60) | NO |  | NULL |  |
| fra_contact | varchar(30) | NO |  | NULL |  |
| app_update | datetime | NO |  | CURRENT_TIMESTAMP |  |
| app_last | date | NO |  | NULL |  |
| app_country | varchar(25) | NO |  | NULL |  |
| app_deployed | varchar(20) | NO |  | NULL |  |
| agent_id | varchar(30) | NO |  | NULL |  |
| w1 | int(3) | NO |  | NULL |  |
| w2 | int(3) | NO |  | NULL |  |
| app_remarks1 | text | NO |  | NULL |  |
| app_source | varchar(15) | NO |  | NULL |  |
| app_action | date | NO |  | NULL |  |
| app_arrival | date | NO |  | NULL |  |
| app_arrival1 | varchar(100) | NO |  | NULL |  |
| sponsor | varchar(60) | NO |  | NULL |  |
| reqname | varchar(50) | NO |  | NULL |  |
| reqcontact | varchar(50) | NO |  | NULL |  |
| reqrel | varchar(25) | NO |  | NULL |  |
| reqadd | varchar(60) | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| SUBJ_ID | int(11) | NO |  | NULL |  |
| app_fb1 | varchar(100) | NO |  | NULL |  |
| app_fb2 | varchar(100) | NO |  | NULL |  |
| app_fb3 | varchar(100) | NO |  | NULL |  |
| category1 | varchar(100) | NO |  | NULL |  |
| passport | varchar(100) | NO |  | NULL |  |
| oec | varchar(100) | NO |  | NULL |  |
| arrival | varchar(100) | NO |  | NULL |  |
| other2 | varchar(100) | NO |  | NULL |  |
| agent | varchar(100) | NO |  | NULL |  |
| sub | varchar(100) | NO |  | NULL |  |
| other1 | varchar(100) | NO |  | NULL |  |
## Table: `subject1`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| app_status | tinytext | NO |  | NULL |  |
| app_remarks | text | NO |  | NULL |  |
| app_update | datetime | NO |  | CURRENT_TIMESTAMP |  |
| app_last | date | NO |  | NULL |  |
| app_country | varchar(25) | NO |  | NULL |  |
| app_deployed | varchar(20) | NO |  | NULL |  |
| ids | int(11) | NO |  | NULL |  |
| agent_id | varchar(30) | NO |  | NULL |  |
| w1 | int(11) | NO |  | NULL |  |
| w2 | int(11) | NO |  | NULL |  |
| app_remarks1 | text | NO |  | NULL |  |
| app_source | varchar(25) | NO |  | NULL |  |
| app_action | date | NO |  | NULL |  |
| SUBJ_ID | int(11) | NO |  | NULL |  |
## Table: `subject11111`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| SUBJ_ID | int(11) | NO |  | NULL |  |
| app_name | varchar(60) | NO |  | NULL |  |
| app_name1 | varchar(30) | NO |  | NULL |  |
| app_name2 | varchar(30) | NO |  | NULL |  |
| app_position | varchar(40) | NO |  | NULL |  |
| app_applied | varchar(20) | NO |  | NULL |  |
| app_fb | varchar(60) | NO |  | NULL |  |
| app_contact | varchar(30) | NO |  | NULL |  |
| app_status | tinytext | NO |  | NULL |  |
| app_status1 | varchar(30) | NO |  | NULL |  |
| app_remarks | text | NO |  | NULL |  |
| fra_name | varchar(60) | NO |  | NULL |  |
| fra_contact | varchar(30) | NO |  | NULL |  |
| app_update | datetime | NO |  | CURRENT_TIMESTAMP |  |
| app_last | date | NO |  | NULL |  |
| app_country | varchar(25) | NO |  | NULL |  |
| app_deployed | varchar(20) | NO |  | NULL |  |
| agent_id | varchar(30) | NO |  | NULL |  |
| w1 | int(3) | NO |  | NULL |  |
| w2 | int(3) | NO |  | NULL |  |
| app_remarks1 | tinytext | NO |  | NULL |  |
| app_source | varchar(15) | NO |  | NULL |  |
| app_action | date | NO |  | NULL |  |
| app_arrival | date | NO |  | NULL |  |
| app_arrival1 | varchar(100) | NO |  | NULL |  |
| sponsor | varchar(60) | NO |  | NULL |  |
| reqname | varchar(50) | NO |  | NULL |  |
| reqcontact | varchar(50) | NO |  | NULL |  |
| reqrel | varchar(25) | NO |  | NULL |  |
| reqadd | varchar(60) | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
## Table: `subject1222`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| SUBJ_ID | int(11) | NO |  | NULL |  |
| app_status | tinytext | NO |  | NULL |  |
| app_remarks | text | NO |  | NULL |  |
| app_update | datetime | NO |  | CURRENT_TIMESTAMP |  |
| app_last | date | NO |  | NULL |  |
| app_country | varchar(25) | NO |  | NULL |  |
| app_deployed | varchar(20) | NO |  | NULL |  |
| ids | int(11) | NO |  | NULL |  |
| agent_id | varchar(30) | NO |  | NULL |  |
| w1 | int(11) | NO |  | NULL |  |
| w2 | int(11) | NO |  | NULL |  |
| app_remarks1 | tinytext | NO |  | NULL |  |
| app_source | varchar(25) | NO |  | NULL |  |
| app_action | date | NO |  | NULL |  |
## Table: `subject_name`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| well_id | int(11) | NO |  | NULL |  |
| wel_id | int(11) | NO |  | NULL |  |
| well_name | varchar(50) | NO |  | NULL |  |
## Table: `suppliers`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| name | varchar(50) | NO |  | NULL |  |
| owner | varchar(30) | NO |  | NULL |  |
| address | varchar(60) | NO |  | NULL |  |
| contact1 | int(20) | NO |  | NULL |  |
| contact2 | varchar(20) | NO |  | NULL |  |
| email_address | varchar(25) | NO |  | NULL |  |
| sec_number | varchar(25) | NO |  | NULL |  |
| account_tite | varchar(25) | NO |  | NULL |  |
| terms_payment | varchar(25) | NO |  | NULL |  |
| mode_payment | varchar(25) | NO |  | NULL |  |
| bank_name | varchar(50) | NO |  | NULL |  |
| bank_number | varchar(30) | NO |  | NULL |  |
| bank_remarks | varchar(80) | NO |  | NULL |  |
| sup_status | int(3) | NO |  | NULL |  |
| sup_id | int(11) | NO |  | NULL |  |
| sup_type | varchar(15) | NO |  | NULL |  |
## Table: `subject_type`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| wel_id | int(11) | NO |  | NULL |  |
| wel_type | varchar(60) | NO |  | NULL |  |
## Table: `suppliers_docs`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(11) | NO |  | NULL |  |
| docs_fie | varchar(60) | NO |  | NULL |  |
| docs_name | varchar(40) | NO |  | NULL |  |
| sup_id | int(11) | NO |  | NULL |  |
| file_status | varchar(30) | NO |  | NULL |  |
| trid | int(5) | NO |  | NULL |  |
| agent_id | int(5) | NO |  | NULL |  |
| jobid | int(5) | NO |  | NULL |  |
## Table: `survey_alphatomo`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| applicant_id | int(11) | NO |  | NULL |  |
| do_you_have_any_experience_of_taking_care_of_pets | tinyint(1) | NO |  | NULL |  |
| are_you_willing_to_work_in_a_house_where_there_are_pets | tinyint(1) | NO |  | NULL |  |
| work_without_any_day_off | tinyint(1) | NO |  | NULL |  |
| are_you_willing_to_work_with_muslim_family | tinyint(1) | NO |  | NULL |  |
| are_you_willing_to_work_with_non_muslim_family | tinyint(1) | NO |  | NULL |  |
| are_you_willing_not_to_use_mobile_during_working_hours | tinyint(1) | NO |  | NULL |  |
| do_you_drink_alcohol_or_smoke | tinyint(1) | NO |  | NULL |  |
| have_you_ever_suffered_from_any_serious_illness | tinyint(1) | NO |  | NULL |  |
| will_you_be_honest_and_loyal_to_your_employer | tinyint(1) | NO |  | NULL |  |
| do_you_promise_not_to_use_mobile_without_permission | tinyint(1) | NO |  | NULL |  |
| are_you_aware_of_2_years_employment_contract_with_your_employer | tinyint(1) | NO |  | NULL |  |
| promise_not_to_answer_back_to_employer | tinyint(1) | NO |  | NULL |  |
| promise_not_invite_friends | tinyint(1) | NO |  | NULL |  |
| are_your_fam_allowerd_you_to_work_as_housemaid_in_malaysia | tinyint(1) | NO |  | NULL |  |
| ready_to_be_separated_from_family_in_malaysia | tinyint(1) | NO |  | NULL |  |
| physically_and_mentally_as_housemaid_in_malaysia | tinyint(1) | NO |  | NULL |  |
| newborn_baby_exp | tinyint(1) | NO |  | NULL |  |
| newborn_baby_will | tinyint(1) | NO |  | NULL |  |
| toddles_exp | tinyint(1) | NO |  | NULL |  |
| toddles_will | tinyint(1) | NO |  | NULL |  |
| baby_care_exp | tinyint(1) | NO |  | NULL |  |
| baby_care_will | tinyint(1) | NO |  | NULL |  |
| child_care_exp | tinyint(1) | NO |  | NULL |  |
| child_care_will | tinyint(1) | NO |  | NULL |  |
| special_child_exp | tinyint(1) | NO |  | NULL |  |
| special_child_will | tinyint(1) | NO |  | NULL |  |
| care_of_disable_exp | tinyint(1) | NO |  | NULL |  |
| care_of_disable_will | tinyint(1) | NO |  | NULL |  |
| care_of_bedridden_exp | tinyint(1) | NO |  | NULL |  |
| care_of_bedridden_will | tinyint(1) | NO |  | NULL |  |
| elderly_care_exp | tinyint(1) | NO |  | NULL |  |
| elderly_care_will | tinyint(1) | NO |  | NULL |  |
| cooking_exp | tinyint(1) | NO |  | NULL |  |
| cooking_will | tinyint(1) | NO |  | NULL |  |
| laundry_exp | tinyint(1) | NO |  | NULL |  |
| laundry_will | tinyint(1) | NO |  | NULL |  |
| ironing_exp | tinyint(1) | NO |  | NULL |  |
| ironing_will | tinyint(1) | NO |  | NULL |  |
| care_of_pets_exp | tinyint(1) | NO |  | NULL |  |
| care_of_pets_will | tinyint(1) | NO |  | NULL |  |
| car_wash_exp | tinyint(1) | NO |  | NULL |  |
| car_wash_will | tinyint(1) | NO |  | NULL |  |
| gardening_exp | tinyint(1) | NO |  | NULL |  |
| gardening_will | tinyint(1) | NO |  | NULL |  |
| experience_in_taking_care_of_baby | text | NO |  | NULL |  |
| experience_in_taking_care_of_children | text | NO |  | NULL |  |
| experience_in_taking_care_of_old | text | NO |  | NULL |  |
| future_plans | text | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
## Table: `training_branches`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| id | int(10) unsigned | NO |  | NULL |  |
| branch_name | varchar(255) | NO |  | NULL |  |
| created_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| updated_at | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| deleted_at | timestamp | YES |  | NULL |  |
## Table: `user`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| user_id | int(10) unsigned | NO |  | NULL |  |
| user_name | varchar(100) | YES |  | NULL |  |
| user_password | varchar(255) | YES |  | NULL |  |
| user_fullname | varchar(100) | YES |  | NULL |  |
| user_email | varchar(100) | YES |  | NULL |  |
| user_type | int(10) unsigned | YES |  | 0 |  |
| branch_id | int(11) | YES |  | NULL |  |
| team_lead_id | int(11) | NO |  | NULL |  |
| user_status | int(10) unsigned | YES |  | NULL |  |
| user_remarks | text | YES |  | NULL |  |
| user_lastlogin | timestamp | YES |  | NULL |  |
| notif_logs | datetime | NO |  | NULL |  |
| notif_media | datetime | NO |  | NULL |  |
| user_createdby | int(10) unsigned | YES |  | NULL |  |
| user_updatedby | int(10) unsigned | YES |  | NULL |  |
| user_created | timestamp | YES |  | NULL |  |
| user_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
| password | varchar(33) | NO |  | NULL |  |
| acct_pass | varchar(30) | NO |  | NULL |  |
| acct_code | varchar(10) | NO |  | NULL |  |
| numberme | varchar(15) | NO |  | NULL |  |
| payments | varchar(8) | NO |  | NULL |  |
| userid | varchar(30) | NO |  | NULL |  |
| userpos | varchar(30) | NO |  | NULL |  |
| poea | varchar(30) | NO |  | NULL |  |
| poeastart | date | NO |  | NULL |  |
| poeaend | date | NO |  | NULL |  |
| userhired | int(11) | NO |  | NULL |  |
| userassign | int(3) | NO |  | NULL |  |
## Table: `user_types`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| type_id | int(10) unsigned | NO |  | NULL |  |
| type_name | varchar(100) | YES |  | NULL |  |
| type_status | int(10) unsigned | YES |  | 1 |  |
| type_color | varchar(10) | YES |  | NULL |  |
| type_createdby | int(10) unsigned | YES |  | NULL |  |
| type_updatedby | int(10) unsigned | YES |  | NULL |  |
| type_created | timestamp | YES |  | NULL |  |
| type_updated | timestamp | YES |  | NULL | on update CURRENT_TIMESTAMP |
| password | varchar(30) | NO |  | NULL |  |
| acct_pass | varchar(30) | NO |  | NULL |  |
## Table: `vaccine_history`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| vid | int(11) | NO |  | NULL |  |
| covidme | varchar(25) | NO |  | NULL |  |
| covid_name | varchar(30) | NO |  | NULL |  |
| covidp | date | NO |  | NULL |  |
| covidloc | varchar(50) | NO |  | NULL |  |
| coviduser | varchar(20) | NO |  | NULL |  |
| coviddate | timestamp | NO |  | CURRENT_TIMESTAMP |  |
| m_app | int(11) | NO |  | NULL |  |
## Table: `voucher`
| Field | Type | Null | Key | Default | Extra |
|---|---|---|---|---|---|
| voucher_id | bigint(20) unsigned | NO |  | NULL |  |
| voucher_number | varchar(255) | YES |  | NULL |  |
| voucher_check | varchar(255) | YES |  | NULL |  |
| voucher_amount | float unsigned | YES |  | NULL |  |
| voucher_marketing_agency | int(10) unsigned | YES |  | NULL |  |
| voucher_marketing_agent | int(10) unsigned | YES |  | NULL |  |
| voucher_recruitment_agent | int(10) unsigned | YES |  | NULL |  |
| voucher_employer | int(10) unsigned | YES |  | NULL |  |
| voucher_applicant | int(10) unsigned | YES |  | NULL |  |
| voucher_status | int(10) unsigned | YES |  | 0 |  |
| voucher_approvedby | int(10) unsigned | YES |  | NULL |  |
| voucher_remarks | text | YES |  | NULL |  |
| voucher_date | date | YES |  | NULL |  |
| voucher_createdby | int(10) unsigned | YES |  | NULL |  |
| voucher_created | timestamp | YES |  | NULL |  |
