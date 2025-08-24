<?php

// Start output buffering to capture any output from the scripts
ob_start();

// Define the testing mode constant
define('TESTING_MODE', true);

// Set error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set a default timezone
date_default_timezone_set('Asia/Manila');

// Include the main configuration file to get DB credentials etc.
require_once(dirname(__DIR__) . '/config.php');

// A helper function to get a fresh PDO connection for test assertions
function get_test_pdo() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

// A helper function to create a test user and return the ID
function create_test_user($pdo, $email, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO applicant (applicant_email, password, applicant_first, applicant_last) VALUES (?, ?, 'Test', 'User')");
    $stmt->execute([$email, $hashed_password]);
    return $pdo->lastInsertId();
}

// A helper function to clean up a test user
function delete_test_user($pdo, $user_id) {
    $stmt = $pdo->prepare("DELETE FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$user_id]);
}
