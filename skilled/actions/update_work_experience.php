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
require_once(dirname(dirname(__DIR__)) . '/src/handlers/WorkExperienceHandler.php');

$user_id = isset($_POST['applicant_id']) ? (int)$_POST['applicant_id'] : null;

// Ensure the user ID from the form matches the one in the session for security
if ($user_id === null || $user_id !== (int)$_SESSION['user_id']) {
    $_SESSION['error'] = "Authorization error.";
    header('Location: ../work_experience.php');
    exit;
}

$pdo = DatabaseConnection::getConnection();
$success = false;

try {
    $pdo->beginTransaction();

    // 1. Get experience data from POST
    $experienceData = isset($_POST['experience']) ? $_POST['experience'] : [];

    // 2. Update work experience
    $success = handleWorkExperienceUpdate($pdo, $user_id, $experienceData);

    $pdo->commit();

    if ($success) {
        $_SESSION['message'] = 'Work experience updated successfully!';
    } else {
        $_SESSION['message'] = 'No changes were made.';
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Database operation failed: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
}

// Redirect back to the work experience page
if (!defined('TESTING_MODE')) {
    header('Location: ../work_experience.php');
    exit;
}
