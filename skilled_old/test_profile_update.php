<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone to match the main application
date_default_timezone_set('Asia/Manila');

// --- Test Setup ---

require_once(dirname(__DIR__) . '/config.php');

$test_user_id = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // 1. Create a test user
    $sql = "INSERT INTO applicant (applicant_first, applicant_last, applicant_email, password) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['Initial Test', 'User', 'test.user.profile@example.com', 'password']);
    $test_user_id = $pdo->lastInsertId();

} catch (PDOException $e) {
    die("Test setup failed: " . $e->getMessage());
}

// 2. Simulate a logged-in user
$_SESSION['user_id'] = $test_user_id;

// 3. Define static POST data to simulate a form submission.
$_POST['applicant_id'] = $test_user_id;
$_POST['firstName'] = 'Test Updated';

// --- Execute the Script ---
ob_start();
define('TESTING_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
include 'actions/update_personal_info.php';
$update_output = ob_get_clean();

// --- Verification ---
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $stmt = $pdo->prepare("SELECT * FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$test_user_id]);
    $user = $stmt->fetch();

    if ($user) {
        $tests_passed = true;
        $error_messages = [];

        if ($user['applicant_first'] !== $_POST['firstName']) {
            $tests_passed = false;
            $error_messages[] = "    - 'applicant_first' is incorrect. Expected: '{$_POST['firstName']}', Actual: '{$user['applicant_first']}'";
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
        echo "\n--- RESULT ---\\n";
        echo "❌ Test FAILED: Could not find the record with ID '{$test_user_id}'.\n";
    }

} catch (PDOException $e) {
    echo "Verification failed: " . $e->getMessage() . "\n";
} finally {
    // --- Cleanup ---
    echo "Test user ID: " . $test_user_id . "\n";
    $delete_stmt = $pdo->prepare("DELETE FROM applicant WHERE applicant_id = ?");
    $delete_stmt->execute([$test_user_id]);
    $pdo = null;
}
?>
