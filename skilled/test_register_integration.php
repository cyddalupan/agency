<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone to match the main application
date_default_timezone_set('Asia/Manila');

echo "Running test for skilled/register.php...\n";

// --- Test Setup ---

// 1. Define static POST data to simulate a form submission.
$_POST['firstName'] = 'Test';
$_POST['middleName'] = 'Unit';
$_POST['lastName'] = 'User';
$_POST['age'] = 25;
$_POST['contactNumber'] = '1234567890';
$_POST['email'] = 'test.user@example.com';
$_POST['password'] = 'password';
$_POST['remarks'] = 'This is a test remark.';
$_POST['workLocation'] = 'Manila';
$_POST['workDetails'] = 'Software Engineer';

// 2. Simulate a file upload.
$dummy_file_path = '/tmp/test_resume.txt';
file_put_contents($dummy_file_path, 'This is a dummy resume file.');
$_FILES['resume'] = [
    'name' => 'test_resume.txt',
    'type' => 'text/plain',
    'tmp_name' => $dummy_file_path,
    'error' => 0,
    'size' => filesize($dummy_file_path)
];

// --- Execute the Script ---

echo "Executing register.php...\n";
ob_start();
// The include path is relative to this test script's location
define('TESTING_MODE', true);
include 'register.php';
$register_output = ob_get_clean();
echo "register.php executed.\n";
echo "--- register.php output ---\n";
echo $register_output;
echo "--- End register.php output ---\n";


// --- Verification ---

require_once(dirname(__DIR__) . '/config.php');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "Database connection for verification successful.\n";

    // Find the record just inserted using the dynamic email
    $verification_email = $_POST['email'];
    $stmt = $pdo->prepare("SELECT * FROM applicant WHERE applicant_email = ? ORDER BY applicant_id DESC LIMIT 1");
    $stmt->execute([$verification_email]);
    $user = $stmt->fetch();

    if ($user) {
        echo "\n--- RESULT ---\n";
        
        $tests_passed = true;
        $error_messages = [];

        // Test 1: fra_remarks
        if ($user['fra_remarks'] !== $_POST['remarks']) {
            $tests_passed = false;
            $error_messages[] = "    - 'fra_remarks' is incorrect. Expected: '{$_POST['remarks']}', Actual: '{$user['fra_remarks']}'";
        }

        // Test 2: applicant_first
        if ($user['applicant_first'] !== $_POST['firstName']) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_first' is incorrect. Expected: '{$_POST['firstName']}', Actual: '{$user['applicant_first']}'";
        }

        // Test 3: applicant_middle
        if ($user['applicant_middle'] !== $_POST['middleName']) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_middle' is incorrect. Expected: '{$_POST['middleName']}', Actual: '{$user['applicant_middle']}'";
        }

        // Test 4: applicant_last
        if ($user['applicant_last'] !== $_POST['lastName']) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_last' is incorrect. Expected: '{$_POST['lastName']}', Actual: '{$user['applicant_last']}'";
        }
        
        // Test 5: applicant_email
        if ($user['applicant_email'] !== $_POST['email']) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_email' is incorrect. Expected: '{$_POST['email']}', Actual: '{$user['applicant_email']}'";
        }

        // Test 6: password
        if ($user['password'] !== $_POST['password']) {
            $tests_passed = false;
            $error_messages[] = "    - 'password' is incorrect. Expected: '{$_POST['password']}', Actual: '{$user['password']}'";
        }

        // Test 7: applicant_age
        if ($user['applicant_age'] != $_POST['age']) { // Use != for type flexibility with numbers
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_age' is incorrect. Expected: '{$_POST['age']}', Actual: '{$user['applicant_age']}'";
        }

        // Test 8: applicant_birthdate
        $expected_birthYear = date('Y') - $_POST['age'];
        $expected_birthdate = $expected_birthYear . "-01-01";
        if ($user['applicant_birthdate'] !== $expected_birthdate) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_birthdate' is incorrect. Expected: '{$expected_birthdate}', Actual: '{$user['applicant_birthdate']}'";
        }

        // Test 9: applicant_contacts
        if ($user['applicant_contacts'] !== $_POST['contactNumber']) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_contacts' is incorrect. Expected: '{$_POST['contactNumber']}', Actual: '{$user['applicant_contacts']}'";
        }

        // Test 10: applicant_cv
        if ($user['applicant_cv'] !== '') {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_cv' is incorrect. Expected: '', Actual: '{$user['applicant_cv']}'";
        }

        if ($tests_passed) {
            echo "✅ Test PASSED: All verified fields are correct.\n";
        } else {
            echo "❌ Test FAILED: Some fields were not inserted correctly.\n";
            foreach ($error_messages as $message) {
                echo $message . "\n";
            }
        }
        
        // --- Cleanup ---
        echo "Cleaning up test data...\n";
        $delete_stmt = $pdo->prepare("DELETE FROM applicant WHERE applicant_id = ?");
        $delete_stmt->execute([$user['applicant_id']]);
        echo "Test record deleted successfully.\n";

    } else {
        echo "\n--- RESULT ---\n";
        echo "❌ Test FAILED: Could not find the record with email '{$verification_email}' that should have been inserted by register.php.\n";
    }

} catch (PDOException $e) {
    echo "Verification failed: " . $e->getMessage() . "\n";
} finally {
    // All file and directory cleanup is removed
}