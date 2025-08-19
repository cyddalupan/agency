<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone to match the main application
date_default_timezone_set('Asia/Manila');

// 1. Database Connection
require_once('../config.test.php'); // Use the test config

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// 2. Minimal Insert Query
try {
    $sql = "INSERT INTO applicant (
        fra_ftw, agent_ppt, fra_visa, fra_deployed, fra_before, fra_sent, agent_ftw, agent_contract, agent_deployed, fra_remarks, applicantNumber, sub_employer, applicant_first, applicant_middle, applicant_last, password, applicant_suffix, applicant_birthdate, applicant_age, applicant_gender, applicant_contacts, applicant_contacts_2, applicant_contacts_3, applicant_address, applicant_email, applicant_nationality, applicant_civil_status, applicant_religion, applicant_languages, applicant_height, applicant_weight, applicant_position_type, applicant_preferred_position, currency, applicant_mothers, applicant_children, applicant_expected_salary, applicant_preferred_country, applicant_other_skills, personalAbilities, applicant_cv, applicant_photo, applicant_status, sub_status, applicant_paid, applicant_employer, applicant_employer_number, applicant_job, applicant_source, applicant_incase_name, applicant_incase_relation, applicant_incase_contact, applicant_incase_address, is_repat, repat_date, other_source, applicant_slug, training_remarks, end_training_at, start_training_at, training_branches_id, optional_statuses_id, applicant_remarks, hit_id, hit_hearing_date, hit_status, hit_date, applicant_date_applied, applicant_createdby, applicant_updatedby, applicant_created, applicant_updated, applicant_fb, incc, singil, applicant_employer_address, applicant_date_interview, applicant_by_interview, agentcom, applicant_paid1, applicant_ex, request1, request2, request3, applicant_remarks_3, applicant_employer_idno, applicant_remarks1, numberone, applicant_jobs, timesched, passsched, releases, remarkspas, locsched, applicant_ppt_pay, applicant_ppt_stat, applicant_remarks5, applicant_remarks6, typess, highest1, applicant_children1, applicant_arabic, applicant_engslish, applicant_con, applicant_data1, applicant_data2, applicant_data3, mystatus, hideme, selection_date, repat_date11, accomodation1, accomodation2, accomodation3, accomodation4, accomodation5, checkmet, pass_type, pass_com, locsched1, userassign, typess1, t1, t2, t3, t4, t5, t6, t7, t8, localflight2, fb_link, applicant_remarks2, applicant_remarks3, singil1, applicant_contacts_4
    ) VALUES (
        0, 0, 0, 0, 0, 0, 0, 0, 0, 'Test Remarks', 'APP001', 'Sub Employer', 'Test', 'Middle', 'User', 'password', '', '1990-01-01', 30, 'Male', '1234567890', '', 'Contact3', 'Test Address', 'test@example.com', 'Filipino', 'Single', 'Christian', 'English', '170cm', '60kg', 'Type', 0, 'PHP', '', '', 0, 0, '', '', '', '', 0, '', 0, 0, '', 0, 0, '', '', '', '', 0, '1970-01-01', '', '', '', '1970-01-01', '1970-01-01', 0, 0, '', 0, '1970-01-01', '', '1970-01-01 00:00:00', '1970-01-01', NULL, NULL, NOW(), NOW(), '', 0, 0, '', '1970-01-01', '', 0, 0, '', '', '', '', '', '', '', 0, '', '', '1970-01-01', '1970-01-01', '', '1970-01-01', '1970-01-01', '', '', 0, '', '', '', 0, 0, '', '', '', '', '', '', '', '', '', '', '', '', 0, '')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    echo "Direct insert test: SUCCESS\n";
} catch (PDOException $e) {
    echo "Direct insert test: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}

?>
