<?php
session_start();

// Ensure the user is logged in and the request method is POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// Required files for this action
require_once(dirname(dirname(__DIR__)) . '/config.php');
require_once(dirname(dirname(__DIR__)) . '/src/Database.php');
require_once(dirname(dirname(__DIR__)) . '/src/updaters/ApplicantUpdater.php');
require_once(dirname(dirname(__DIR__)) . '/src/handlers/DocumentsHandler.php');
require_once(dirname(__DIR__) . '/includes/helpers.php'); // Include the new helper file

$user_id = isset($_POST['applicant_id']) ? (int)$_POST['applicant_id'] : null;

// Ensure the user ID from the form matches the one in the session for security
if ($user_id === null || $user_id !== (int)$_SESSION['user_id']) {
    $_SESSION['error'] = "Authorization error.";
    header('Location: ../documents.php');
    exit;
}

$pdo = DatabaseConnection::getConnection();
$success = false;

try {
    $pdo->beginTransaction();

    // 1. Handle the resume file upload
    $resume_path = handleFileUpload($user_id, isset($_FILES['resume']) ? $_FILES['resume'] : null);

    // 2. Get data from the handler, passing the resume path
    $applicantData = getDocumentsUpdateData($_POST, $resume_path);

    // 3. Update the applicant table
    if (!empty($applicantData)) {
        $success = ApplicantUpdater::updateApplicant($pdo, $user_id, $applicantData);
    }

    $pdo->commit();

    if ($success) {
        $_SESSION['message'] = 'Documents updated successfully!';
    } else {
        $_SESSION['message'] = 'No changes were made.';
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Database operation failed: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
}

// Redirect back to the documents page
if (!defined('TESTING_MODE')) {
    header('Location: ../documents.php');
    exit;
}
