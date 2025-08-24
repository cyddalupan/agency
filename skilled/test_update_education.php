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
$pdo = null;

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
    $stmt->execute(['Education', 'Test', 'education.test@example.com', 'password']);
    $user_id = $pdo->lastInsertId();

    // 2. Simulate a session
    $_SESSION['user_id'] = $user_id;

    // 3. Define static POST data to simulate a form submission. 
    $_POST['applicant_id'] = $user_id;
    $_POST['page'] = 'education';
    $_POST['education_highschool'] = 'Test High School';
    $_POST['education_highschool_year'] = '2010';
    $_POST['education_college'] = 'Test College';
    $_POST['education_college_year'] = '2014';
    $_POST['education_college_skills'] = 'BS Computer Science';
    $_POST['education_mba'] = 'Test University';
    $_POST['education_mba_year'] = '2016';
    $_POST['education_mba_course'] = 'MS Computer Science';
    $_POST['education_others'] = 'Test Certification';
    $_POST['education_others_year'] = '2017';

} catch (PDOException $e) {
    die("Test setup failed: " . $e->getMessage());
}

// --- Execute the Script ---
ob_start();
define('TESTING_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
include 'actions/update_education.php';
$update_output = ob_get_clean();


// --- Verification ---

try {
    $stmt = $pdo->prepare("SELECT * FROM applicant_education WHERE education_applicant = ?");
    $stmt->execute([$user_id]);
    $education_data = $stmt->fetch();

    if ($education_data) {
        echo "\n--- RESULT ---\n";
        
        $tests_passed = true;
        $error_messages = [];

        foreach ($_POST as $key => $value) {
            if (strpos($key, 'education_') === 0) {
                if ($education_data[$key] !== $value) {
                    $tests_passed = false;
                    $error_messages[] = "    - '{$key}' is incorrect. Expected: '{$value}', Actual: '{$education_data[$key]}'" ;
                }
            }
        }

        if ($tests_passed) {
            echo "✅ Test PASSED: All education fields updated correctly.\n";
        } else {
            echo "❌ Test FAILED: Some fields were not updated correctly.\n";
            foreach ($error_messages as $message) {
                echo $message . "\n";
            }
        }
        
    } else {
        echo "\n--- RESULT ---\n";
        echo "❌ Test FAILED: Could not find the education record for user ID '{$user_id}'.\n";
    }

} catch (PDOException $e) {
    echo "Verification failed: " . $e->getMessage() . "\n";
} finally {
    // --- Cleanup ---
    if ($user_id) {
        $delete_stmt = $pdo->prepare("DELETE FROM applicant WHERE applicant_id = ?");
        $delete_stmt->execute([$user_id]);
        $delete_stmt = $pdo->prepare("DELETE FROM applicant_education WHERE education_applicant = ?");
        $delete_stmt->execute([$user_id]);
    }
}

?>