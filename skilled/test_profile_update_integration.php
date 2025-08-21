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
    $test_first = 'Test';
    $test_middle = 'User';
    $test_last = 'Profile';
    $test_email = 'test.profile@example.com';
    $test_password = password_hash('password123', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO applicant (applicant_first, applicant_middle, applicant_last, applicant_email, applicant_password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$test_first, $test_middle, $test_last, $test_email, $test_password]);
    $test_user_id = $pdo->lastInsertId();

    echo "Test user created with ID: " . $test_user_id . "\n";

    // Set session for the test user
    $_SESSION['user_id'] = $test_user_id;

    // 2. Simulate POST request to update_profile.php
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'applicant_id' => $test_user_id,
        'firstName' => 'UpdatedFirst',
        'middleName' => 'UpdatedMiddle',
        'lastName' => 'UpdatedLast',
    ];

    // Capture output and prevent headers from being sent
    ob_start();
    include 'update_profile.php';
    ob_end_clean();

    // 3. Verify the database reflects the changes
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
    // 4. Clean up the temporary test user
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