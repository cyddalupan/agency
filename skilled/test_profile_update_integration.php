<?php

require_once(dirname(__DIR__) . '/config.php');

// Start a session for the test
session_start();

echo "Running Profile Update Integration Test...\n";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // 1. Insert a temporary test user
    $test_email = 'test.profile.update@example.com';

    // --- Pre-test Cleanup: Delete any existing test users with this email ---
    $delete_existing_stmt = $pdo->prepare("DELETE FROM applicant WHERE applicant_email = ?");
    $delete_existing_stmt->execute([$test_email]);

    $sql = <<<SQL
INSERT INTO applicant (
    fra_ftw, agent_ppt, fra_visa, fra_deployed, fra_before, fra_sent, agent_ftw, agent_contract, agent_deployed,
    fra_remarks, applicantNumber, sub_employer, applicant_first, applicant_middle, applicant_last, password,
    applicant_suffix, applicant_birthdate, applicant_age, applicant_gender, applicant_contacts, applicant_contacts_2,
    applicant_contacts_3, applicant_address, applicant_email, applicant_nationality, applicant_civil_status,
    applicant_religion, applicant_languages, applicant_height, applicant_weight, applicant_position_type,
    applicant_preferred_position, currency, applicant_mothers, applicant_children, applicant_expected_salary,
    applicant_preferred_country, applicant_other_skills, personalAbilities, applicant_cv, applicant_photo,
    applicant_status, sub_status, applicant_paid, applicant_employer, applicant_employer_number, applicant_job,
    applicant_source, applicant_incase_name, applicant_incase_relation, applicant_incase_contact, applicant_incase_address,
    is_repat, repat_date, other_source, applicant_slug, training_remarks, end_training_at, start_training_at,
    training_branches_id, optional_statuses_id, applicant_remarks, hit_id, hit_hearing_date, hit_status, hit_date,
    applicant_date_applied, applicant_createdby, applicant_updatedby, applicant_created, applicant_updated,
    applicant_fb, incc, singil, applicant_employer_address, applicant_date_interview, applicant_by_interview,
    agentcom, applicant_paid1, applicant_ex, request1, request2, request3, applicant_remarks_3, applicant_employer_idno,
    applicant_remarks1, numberone, applicant_jobs, timesched, passsched, releases, remarkspas, locsched,
    applicant_ppt_pay, applicant_ppt_stat, applicant_remarks5, applicant_remarks6, typess, highest1, applicant_children1,
    applicant_arabic, applicant_engslish, applicant_con, applicant_data1, applicant_data2, applicant_data3, mystatus,
    hideme, selection_date, repat_date11, accomodation1, accomodation2, accomodation3, accomodation4, accomodation5,
    checkmet, pass_type, pass_com, locsched1, userassign, typess1, t1, t2, t3, t4, t5, t6, t7, t8, localflight2,
    fb_link, applicant_remarks2, applicant_remarks3, singil1, applicant_contacts_4
) VALUES (
    0, 0, 0, 0, 0, 0, 0, 0, 0,
    'Test Remarks', 'applicantNumber_val', 'sub_employer_val', 'Test', 'Profile', 'User', 'password123',
    '', '1990-01-01', 30, 'Male', '1234567890', 'contact2',
    'contact3', 'address_val', ?, 'Filipino', 'Single',
    'Christian', 'English', '170cm', '60kg', 'TypeA',
    0, 'PHP', 'mothers_val', 'children_val', 0.0,
    0, 'other_skills_val', 'personalAbilities_val', '', 'photo_val',
    73, 'sub_status_val', 0, 0, 'employer_number_val', 0,
    0, 'incase_name_val', 'incase_relation_val', 'incase_contact_val', 'incase_address_val',
    0, '1970-01-01', 'other_source_val', 'applicant_slug_val', 'training_remarks_val', '1970-01-01', '1970-01-01',
    0, 0, 'applicant_remarks_val', 0, '1970-01-01', 'hit_status_val', '1970-01-01 00:00:00',
    '1970-01-01', 0, 0, NOW(), NOW(),
    'applicant_fb_val', 0.0, 0.0, 'employer_address_val', '1970-01-01', 'by_interview_val',
    0.0, 0, 'applicant_ex_val', 'request1_val', 'request2_val', 'request3_val', 'remarks3_val', 'employer_idno_val',
    'remarks1_val', 0, 'applicant_jobs_val', 'timesched_val', '1970-01-01', '1970-01-01', 'remarkspas_val', 'locsched_val',
    'ppt_pay_val', 'ppt_stat_val', 'remarks5_val', 'remarks6_val', 0, 'highest1_val', 'children1_val',
    'arabic_val', 'english_val', 'con_val', 'data1_val', 'data2_val', 'data3_val', 0,
    0, '1970-01-01', '1970-01-01', 'accom1_val', '1970-01-01', '1970-01-01', 'accom4_val', 'accom5_val',
    0, 'pass_type_val', 'pass_com_val', 'locsched1_val', 0, 0, 't1_val', 't2_val', 't3_val', 't4_val', 't5_val',
    't6_val', 't7_val', 't8_val', 'localflight2_val',
    'fb_link_val', 'remarks2_val', 'remarks3_val', 0, 'contacts4_val'
)
SQL;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$test_email]);
    $test_user_id = $pdo->lastInsertId();

    echo "Test user created with ID: " . $test_user_id . "\n";

    // 2. Define new values for the update
    $newFirstName = 'UpdatedFirst';
    $newMiddleName = 'UpdatedMiddle';
    $newLastName = 'UpdatedLast';

    // 3. Build and execute the UPDATE query
    $sql_update = "UPDATE applicant SET 
                    applicant_first = ?, applicant_middle = ?, applicant_last = ?
                WHERE applicant_id = ?";
    
    $params = [
        $newFirstName, $newMiddleName, $newLastName, $test_user_id
    ];

    $stmt = $pdo->prepare($sql_update);
    $stmt->execute($params);
    echo "UPDATE query executed. Affected rows: " . $stmt->rowCount() . "\n";


    // 4. Verify the database reflects the changes
    $stmt = $pdo->prepare("SELECT applicant_first, applicant_middle, applicant_last FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$test_user_id]);
    $updated_user_data = $stmt->fetch();

    if (
        $updated_user_data['applicant_first'] === 'UpdatedFirst' &&
        $updated_user_data['applicant_middle'] === 'UpdatedMiddle' &&
        $updated_user_data['applicant_last'] === 'UpdatedLast'
    ) {
        echo "Test Passed: First, Middle, and Last Name updated successfully.\n";
    } else {
        echo "Test Failed: Profile update did not reflect in the database.\n";
        print_r($updated_user_data);
    }

} catch (PDOException $e) {
    echo "Test Error: " . $e->getMessage() . "\n";
} finally {
    // 5. Clean up the temporary test user
    if (isset($pdo) && isset($test_user_id)) {
        $stmt = $pdo->prepare("DELETE FROM applicant WHERE applicant_id = ?");
        $stmt->execute([$test_user_id]);
        echo "Test user " . $test_user_id . " deleted.\n";
    }
    // Clear session for the test
    session_unset();
    session_destroy();
}

?>