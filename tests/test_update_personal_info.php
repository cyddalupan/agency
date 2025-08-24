<?php

require_once('bootstrap.php');

$pdo = null;
$test_user_id = null;

try {
    // 1. Setup: Create a test user
    $pdo = get_test_pdo();
    $test_user_id = create_test_user($pdo, 'test.user.' . time() . '@example.com', 'password123');

    // 2. Simulate a logged-in user session
    $_SESSION['user_id'] = $test_user_id;

    // 3. Prepare the POST data for the form submission
    $new_first_name = "UpdatedFirstName";
    $new_last_name = "UpdatedLastName";
    $new_email = 'updated.email.' . time() . '@example.com';

    $_POST = [
        'applicant_id' => $test_user_id,
        'firstName' => $new_first_name,
        'lastName' => $new_last_name,
        'email' => $new_email,
        // Add other fields from personal_info.php as needed, with default values
        'middleName' => 'Middle',
        'gender' => 'Male',
        'nationality' => 'Filipino',
        'civilStatus' => 'Single',
        'birthdate' => '1990-01-01',
        'age' => 30,
        'placeOfBirth' => 'Manila',
        'contactNumber' => '1234567890',
        'otherContactNumber' => '',
        'anotherContactNumber' => '',
        'address' => '123 Test St',
        'height' => 170,
        'weight' => 70,
        'religion' => 'Christian',
        'languages' => 'English',
        'password' => '', // Leave blank to not update
        'dateApplied' => '2025-01-01',
        'trainingBranch' => 'Main',
        'source' => 'Online',
        'recruitmentAgent' => 'Agent Smith',
        'repatriated' => '0',
        'repatriationDate' => '',
        'applicantEx' => 'No',
        'branch' => 'Skilled',
        'transferBranch' => '',
        'waitlist' => '',
        'otherSource' => '',
        'interviewBy' => 'Interviewer',
        'remarksForResume' => 'Some remarks for resume.',
        'remarks' => 'Some general remarks.'
    ];

    // 4. Execute the script to be tested
    // Use include instead of require to avoid fatal error on script exit
    include(dirname(__DIR__) . '/skilled/actions/update_personal_info.php');

    // 5. Assertions: Check if the database was updated correctly
    $stmt = $pdo->prepare("SELECT applicant_first, applicant_last, applicant_email FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$test_user_id]);
    $updated_user = $stmt->fetch();

    if (!$updated_user) {
        throw new Exception("Test user not found after update.");
    }

    if ($updated_user['applicant_first'] !== $new_first_name) {
        throw new Exception("Assertion failed: First name was not updated. Expected: $new_first_name, Got: " . $updated_user['applicant_first']);
    }

    if ($updated_user['applicant_last'] !== $new_last_name) {
        throw new Exception("Assertion failed: Last name was not updated. Expected: $new_last_name, Got: " . $updated_user['applicant_last']);
    }
    
    if ($updated_user['applicant_email'] !== $new_email) {
        throw new Exception("Assertion failed: Email was not updated. Expected: $new_email, Got: " . $updated_user['applicant_email']);
    }

    // If we reach here, the main assertions passed.

} catch (Exception $e) {
    // If any exception occurs, print it and exit with a non-zero status
    echo "Test failed: " . $e->getMessage() . "\n";
    // Cleanup even on failure
    if ($pdo && $test_user_id) {
        delete_test_user($pdo, $test_user_id);
    }
    exit(1);
} finally {
    // 6. Teardown: Clean up the test user
    if ($pdo && $test_user_id) {
        delete_test_user($pdo, $test_user_id);
    }
    // Clean up output buffer
    ob_end_clean();
}

// If the script reaches here without exiting, the test is considered passed.
?>
