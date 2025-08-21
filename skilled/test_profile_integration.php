<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone to match the main application
date_default_timezone_set('Asia/Manila');

try {
    echo "Running test for skilled/profile.php update functionality...\n";

    // --- Test Setup ---

    require_once(dirname(__DIR__) . '/config.php');

    $pdo = null;
    $dummy_user_id = null;

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "Database connection for test setup successful.\n";

    // 1. Insert a dummy user into the applicant table with all required fields (STATIC INSERT)
    $sql_insert = "INSERT INTO applicant (
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
        'Test Remarks', 'applicantNumber_val', 'sub_employer_val', 'Static', 'Test', 'User', 'initial_password',
        '', '1990-01-01', 30, 'Male', '1234567890', 'contact2',
        'contact3', 'Initial Address', 'static.user@example.com', 'Filipino', 'Single',
        'Christian', 'English', 170, 60, 'TypeA',
        0, 'USD', 'mothers_val', 'children_val', 50000.0,
        0, 'Initial Other Skills', 'Initial Personal Abilities', '', 'photo_val',
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
        't6_val', 't7_val', 't8_val', 'localflight2_val', 'fb_link_val', 'remarks2_val', 'remarks3_val', 0, 'contacts4_val'
    )";

    $stmt = $pdo->prepare($sql_insert);
    $stmt->execute(); // No parameters for static query

    // Get the ID of the inserted user
    $dummy_user_id = $pdo->lastInsertId();
    echo "Dummy user inserted with ID: " . $dummy_user_id . "\n";

    // 2. Define new values for the update
    $newFirstName = 'Updated';
    $newMiddleName = 'New';
    $newLastName = 'Profile';
    $newAge = 35;
    $newContactNumber = '0987654321';
    $newEmail = 'updated.user@example.com';
    $newPassword = 'new_password';
    $newRemarks = 'Updated remarks for the applicant.';
    $newBirthdate = (date('Y') - $newAge) . "-01-01";
    $newResumeFileName = 'updated_resume.pdf';
    $newResumePath = 'uploads/' . $newResumeFileName;
    $newGender = 'Female';
    $newNationality = 'American';
    $newCivilStatus = 'Married';
    $newAddress = 'Updated Address, City, Country';
    $newHeight = 180;
    $newWeight = 75;
    $newReligion = 'Atheist';
    $newLanguages = 'English, Spanish';
    $newPositionType = 'Skilled';
    $newPreferredPosition = 0; // Changed to integer
    $newCurrency = 'EUR';
    $newExpectedSalary = 60000.0;
    $newPreferredCountry = 1; // Changed to integer
    $newOtherSkills = 'Updated Other Skills';
    $newPersonalAbilities = 'Updated Personal Abilities';

    // 3. Build and execute the UPDATE query
    $sql_update = "UPDATE applicant SET 
                    applicant_first = ?, applicant_middle = ?, applicant_last = ?, applicant_age = ?,
                    applicant_contacts = ?, applicant_email = ?, password = ?, fra_remarks = ?, applicant_cv = ?,
                    applicant_gender = ?, applicant_nationality = ?, applicant_civil_status = ?, applicant_address = ?,
                    applicant_height = ?, applicant_weight = ?, applicant_religion = ?, applicant_languages = ?,
                    applicant_position_type = ?, applicant_preferred_position = ?, currency = ?, applicant_expected_salary = ?,
                    applicant_preferred_country = ?, applicant_other_skills = ?, personalAbilities = ?, applicant_birthdate = ?
                WHERE applicant_id = ?";
    
    $params = [
        $newFirstName, $newMiddleName, $newLastName, $newAge, $newContactNumber, $newEmail, $newPassword, $newRemarks, $newResumePath,
        $newGender, $newNationality, $newCivilStatus, $newAddress, $newHeight, $newWeight, $newReligion, $newLanguages,
        $newPositionType, $newPreferredPosition, $newCurrency, $newExpectedSalary, $newPreferredCountry, $newOtherSkills,
        $newPersonalAbilities, $newBirthdate, $dummy_user_id
    ];

    $stmt = $pdo->prepare($sql_update);
    $stmt->execute($params);
    echo "UPDATE query executed. Affected rows: " . $stmt->rowCount() . "\n";


    // --- Verification ---

    echo "Verifying updated data...\n";
    $stmt = $pdo->prepare("SELECT * FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$dummy_user_id]);
    $updated_user_data = $stmt->fetch();

    $tests_passed = true;
    $error_messages = [];

    if ($updated_user_data) {
        if ($updated_user_data['applicant_first'] !== $newFirstName) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_first' is incorrect. Expected: '{$newFirstName}', Actual: '{$updated_user_data['applicant_first']}'";
        }
        if ($updated_user_data['applicant_middle'] !== $newMiddleName) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_middle' is incorrect. Expected: '{$newMiddleName}', Actual: '{$updated_user_data['applicant_middle']}'";
        }
        if ($updated_user_data['applicant_last'] !== $newLastName) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_last' is incorrect. Expected: '{$newLastName}', Actual: '{$updated_user_data['applicant_last']}'";
        }
        if ($updated_user_data['applicant_age'] != $newAge) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_age' is incorrect. Expected: '{$newAge}', Actual: '{$updated_user_data['applicant_age']}'";
        }
        if ($updated_user_data['applicant_contacts'] !== $newContactNumber) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_contacts' is incorrect. Expected: '{$newContactNumber}', Actual: '{$updated_user_data['applicant_contacts']}'";
        }
        if ($updated_user_data['applicant_email'] !== $newEmail) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_email' is incorrect. Expected: '{$newEmail}', Actual: '{$updated_user_data['applicant_email']}'";
        }
        if ($updated_user_data['applicant_birthdate'] !== $newBirthdate) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_birthdate' is incorrect. Expected: '{$newBirthdate}', Actual: '{$updated_user_data['applicant_birthdate']}'";
        }
        if ($updated_user_data['password'] !== $newPassword) {
            $tests_passed = false;
            $error_messages[] = "    - 'password' is incorrect. Expected: '{$newPassword}', Actual: '{$updated_user_data['password']}'";
        }
        if ($updated_user_data['fra_remarks'] !== $newRemarks) {
            $tests_passed = false;
            $error_messages[] = "    - 'fra_remarks' is incorrect. Expected: '{$newRemarks}', Actual: '{$updated_user_data['fra_remarks']}'";
        }
        if ($updated_user_data['applicant_cv'] !== $newResumePath) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_cv' is incorrect. Expected: '{$newResumePath}', Actual: '{$updated_user_data['applicant_cv']}'";
        }
        if ($updated_user_data['applicant_gender'] !== $newGender) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_gender' is incorrect. Expected: '{$newGender}', Actual: '{$updated_user_data['applicant_gender']}'";
        }
        if ($updated_user_data['applicant_nationality'] !== $newNationality) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_nationality' is incorrect. Expected: '{$newNationality}', Actual: '{$updated_user_data['applicant_nationality']}'";
        }
        if ($updated_user_data['applicant_civil_status'] !== $newCivilStatus) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_civil_status' is incorrect. Expected: '{$newCivilStatus}', Actual: '{$updated_user_data['applicant_civil_status']}'";
        }
        if ($updated_user_data['applicant_address'] !== $newAddress) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_address' is incorrect. Expected: '{$newAddress}', Actual: '{$updated_user_data['applicant_address']}'";
        }
        if ($updated_user_data['applicant_height'] != $newHeight) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_height' is incorrect. Expected: '{$newHeight}', Actual: '{$updated_user_data['applicant_height']}'";
        }
        if ($updated_user_data['applicant_weight'] != $newWeight) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_weight' is incorrect. Expected: '{$newWeight}', Actual: '{$updated_user_data['applicant_weight']}'";
        }
        if ($updated_user_data['applicant_religion'] !== $newReligion) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_religion' is incorrect. Expected: '{$newReligion}', Actual: '{$updated_user_data['applicant_religion']}'";
        }
        if ($updated_user_data['applicant_languages'] !== $newLanguages) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_languages' is incorrect. Expected: '{$newLanguages}', Actual: '{$updated_user_data['applicant_languages']}'";
        }
        if ($updated_user_data['applicant_position_type'] !== $newPositionType) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_position_type' is incorrect. Expected: '{$newPositionType}', Actual: '{$updated_user_data['applicant_position_type']}'";
        }
        if ($updated_user_data['currency'] !== $newCurrency) {
            $tests_passed = false;
            $error_messages[] = "    - 'currency' is incorrect. Expected: '{$newCurrency}', Actual: '{$updated_user_data['currency']}'";
        }
        if ($updated_user_data['applicant_expected_salary'] != $newExpectedSalary) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_expected_salary' is incorrect. Expected: '{$newExpectedSalary}', Actual: '{$updated_user_data['applicant_expected_salary']}'";
        }
        if ($updated_user_data['applicant_preferred_country'] != $newPreferredCountry) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_preferred_country' is incorrect. Expected: '{$newPreferredCountry}', Actual: '{$updated_user_data['applicant_preferred_country']}'";
        }
        if ($updated_user_data['applicant_other_skills'] !== $newOtherSkills) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_other_skills' is incorrect. Expected: '{$newOtherSkills}', Actual: '{$updated_user_data['applicant_other_skills']}'";
        }
        if ($updated_user_data['personalAbilities'] !== $newPersonalAbilities) {
            $tests_passed = false;
            $error_messages[] = "    - 'personalAbilities' is incorrect. Expected: '{$newPersonalAbilities}', Actual: '{$updated_user_data['personalAbilities']}'";
        }

        if ($tests_passed) {
            echo "✅ Test PASSED: All fields updated correctly.\n";
        } else {
            echo "❌ Test FAILED: Some fields were not updated correctly.\n";
            foreach ($error_messages as $message) {
                echo $message . "\n";
            }
        }
    } else {
        echo "❌ Test FAILED: Could not retrieve updated user data.\n";
        $tests_passed = false;
    }

} catch (PDOException $e) {
    echo "Test failed due to database error: " . $e->getMessage() . "\n";
    $tests_passed = false;
} finally {
    // --- Cleanup ---
    echo "Cleaning up test data...\n";
    if ($pdo && $dummy_user_id) {
        $delete_stmt = $pdo->prepare("DELETE FROM applicant WHERE applicant_id = ?");
        $delete_stmt->execute([$dummy_user_id]);
        echo "Dummy user record deleted successfully.\n";
    }
    // Clean up dummy uploaded file and directory
    $dummy_upload_dir = __DIR__ . '/uploads_test/';
    $dummy_file_path = $dummy_upload_dir . 'test_resume.txt';
    if (file_exists($dummy_file_path)) {
        unlink($dummy_file_path);
    }
    if (is_dir($dummy_upload_dir)) {
        rmdir($dummy_upload_dir);
    }
    // Clear session variables
    session_unset();
    session_destroy();
    echo "Session cleared.\n";
}

echo "\nTest finished.\n";
?>