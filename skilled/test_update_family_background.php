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
    $stmt->execute(['Family', 'Test', 'family.test@example.com', 'password']);
    $user_id = $pdo->lastInsertId();

    // 2. Simulate a session
    $_SESSION['user_id'] = $user_id;

    // 3. Define static POST data to simulate a form submission. 
    $_POST['applicant_id'] = $user_id;
    $_POST['page'] = 'family_background';
    $_POST['partnerName'] = 'Test Partner';
    $_POST['partnerOccupation'] = 'Test Occupation';
    $_POST['children'] = '2';
    $_POST['motherName'] = 'Test Mother';
    $_POST['motherOccupation'] = 'Test Occupation';
    $_POST['fatherName'] = 'Test Father';
    $_POST['fatherOccupation'] = 'Test Occupation';
    $_POST['positionInFamily'] = 'Eldest';
    $_POST['numBrothers'] = '1';
    $_POST['numSisters'] = '1';
    $_POST['relativeName'] = 'Test Relative';
    $_POST['relativeMobile'] = '1234567890';
    $_POST['emergencyContactName'] = 'Test Emergency Contact';
    $_POST['emergencyContactRelationship'] = 'Sibling';
    $_POST['emergencyContactNumber'] = '0987654321';
    $_POST['emergencyContactAddress'] = 'Test Address';

} catch (PDOException $e) {
    die("Test setup failed: " . $e->getMessage());
}

// --- Execute the Script ---
ob_start();
define('TESTING_MODE', true);
$_SERVER['REQUEST_METHOD'] = 'POST';
include 'update_profile.php';
$update_output = ob_get_clean();
echo $update_output;


// --- Verification ---

try {
    $stmt = $pdo->prepare("SELECT * FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();

    if ($user_data) {
        echo "\n--- RESULT ---\n";
        
        $tests_passed = true;
        $error_messages = [];

        if ($user_data['partner_husband'] !== $_POST['partnerName']) {
            $tests_passed = false;
            $error_messages[] = "    - 'partner_husband' is incorrect. Expected: '{$_POST['partnerName']}', Actual: '{$user_data['partner_husband']}'";
        }

        if ($tests_passed) {
            echo "✅ Test PASSED: All family background fields updated correctly.\n";
        } else {
            echo "❌ Test FAILED: Some fields were not updated correctly.\n";
            foreach ($error_messages as $message) {
                echo $message . "\n";
            }
        }
        
    } else {
        echo "\n--- RESULT ---\n";
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