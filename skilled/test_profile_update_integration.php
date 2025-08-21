<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone to match the main application
date_default_timezone_set('Asia/Manila');

// --- Test Setup ---

require_once(dirname(__DIR__) . '/config.php');

$user_id = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = ''"
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // 1. Create a dummy user for testing
    $stmt = $pdo->prepare("INSERT INTO applicant (applicant_first, applicant_last, applicant_email, password) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Test', 'User', 'test.profile.update@example.com', 'password']);
    $user_id = $pdo->lastInsertId();

    // 2. Simulate a session
    $_SESSION['user_id'] = $user_id;


    // 3. Define static POST data to simulate a form submission.
    $_POST['applicant_id'] = $user_id;
    $_POST['firstName'] = 'Test';
    $_POST['lastName'] = 'User';
    $_POST['positionType'] = 'Skilled';
    $_POST['remarks'] = 'Test remarks';
    $_POST['personalAbilities'] = 'Test personal abilities';
    $_POST['currency'] = 'USD';
    $_POST['expectedSalary'] = 5000;

} catch (PDOException $e) {
    die("Test setup failed: " . $e->getMessage());
}

// --- Execute the Script ---
ob_start();
// The include path is relative to this test script's location
define('TESTING_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
include 'update_profile.php';
$update_output = ob_get_clean();


// --- Verification ---

try {
    $stmt = $pdo->prepare("SELECT * FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        echo "\n--- RESULT ---\n";
        
        $tests_passed = true;
        $error_messages = [];

        // Test 1: applicant_position_type
        if ($user['applicant_position_type'] !== $_POST['positionType']) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_position_type' is incorrect. Expected: '{$_POST['positionType']}', Actual: '{$user['applicant_position_type']}'";
        }

        // Test 2: currency
        if ($user['currency'] !== $_POST['currency']) {
            $tests_passed = false;
            $error_messages[] = "    - 'currency' is incorrect. Expected: '{$_POST['currency']}', Actual: '{$user['currency']}'";
        }

        // Test 3: applicant_expected_salary
        if ($user['applicant_expected_salary'] != $_POST['expectedSalary']) { // Use != for type flexibility with numbers
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_expected_salary' is incorrect. Expected: '{$_POST['expectedSalary']}', Actual: '{$user['applicant_expected_salary']}'";
        }

        if ($tests_passed) {
            echo "✅ Test PASSED: All verified fields are correct.\n";
        } else {
            echo "❌ Test FAILED: Some fields were not updated correctly.\n";
            foreach ($error_messages as $message) {
                echo $message . "\n";
            }
        }
        
    } else {
        echo "\n--- RESULT ---\
";
        echo "❌ Test FAILED: Could not find the user with ID '{$user_id}'.\n";
    }

} catch (PDOException $e) {
    echo "Verification failed: " . $e->getMessage() . "\n";
} finally {
    // --- Cleanup ---
    if ($user_id) {
        $delete_stmt = $pdo->prepare("DELETE FROM applicant WHERE applicant_id = ?");
        $delete_stmt->execute([$user_id]);
    }
}

?>