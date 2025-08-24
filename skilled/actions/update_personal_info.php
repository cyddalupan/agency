<?php
session_start();

// Ensure the user is logged in and the request method is POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to login or an error page if not authorized or method is wrong
    header('Location: ../login.php');
    exit;
}

// Required files for this action
require_once(dirname(dirname(__DIR__)) . '/config.php');
require_once(dirname(dirname(__DIR__)) . '/src/Database.php');
require_once(dirname(dirname(__DIR__)) . '/src/updaters/ApplicantUpdater.php');
require_once(dirname(dirname(__DIR__)) . '/src/handlers/PersonalInfoHandler.php');

$user_id = isset($_POST['applicant_id']) ? (int)$_POST['applicant_id'] : null;

// Ensure the user ID from the form matches the one in the session for security
if ($user_id === null || $user_id !== (int)$_SESSION['user_id']) {
    $_SESSION['error'] = "Authorization error.";
    header('Location: ../personal_info.php');
    exit;
}

$pdo = DatabaseConnection::getConnection();
$success = false;

    // 1. Get data from the handler
    $applicantData = getPersonalInfoUpdateData($_POST);
    error_log("Applicant Data before update: " . print_r($applicantData, true));

    // 2. Handle password update separately if provided
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        // WARNING: Storing plain text password. This is a security vulnerability.
        // For production, always hash passwords using password_hash().
        $applicantData['password'] = $_POST['password'];
    }

    // 3. Update the applicant table
    if (!empty($applicantData)) {
        $success = ApplicantUpdater::updateApplicant($pdo, $user_id, $applicantData);
    }

    if ($success) {
        $_SESSION['message'] = 'Personal information updated successfully!';
    } else {
        $_SESSION['message'] = 'No changes were made.';
    }

// Redirect back to the personal info page
if (!defined('TESTING_MODE')) {
    header('Location: ../personal_info.php');
    exit;
}
