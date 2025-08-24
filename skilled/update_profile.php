<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    if (!defined('TESTING_MODE')) {
        header('Location: login.php');
        exit;
    }
}

require_once(dirname(__DIR__) . '/config.php');
require_once(dirname(__DIR__) . '/src/Database.php');
require_once(dirname(__DIR__) . '/src/updaters/ApplicantUpdater.php');
require_once(dirname(__DIR__) . '/src/updaters/ApplicantCertificateUpdater.php');
require_once(dirname(__DIR__) . '/src/updaters/ApplicantEducationUpdater.php');

// Include all handler functions
require_once(dirname(__DIR__) . '/src/handlers/PersonalInfoHandler.php');
require_once(dirname(__DIR__) . '/src/handlers/FamilyBackgroundHandler.php');
require_once(dirname(__DIR__) . '/src/handlers/JobPreferencesHandler.php');
require_once(dirname(__DIR__) . '/src/handlers/HealthHandler.php');
require_once(dirname(__DIR__) . '/src/handlers/DocumentsHandler.php');
require_once(dirname(__DIR__) . '/src/handlers/CertificatesHandler.php');
require_once(dirname(__DIR__) . '/src/handlers/EducationHandler.php');
require_once(dirname(__DIR__) . '/src/handlers/WorkExperienceHandler.php');

// Helper function for file uploads
function handleFileUpload($userId, $fileData, $targetDir = "uploads/")
{
    if (!is_array($fileData)) {
        return null;
    }
    if (isset($fileData['name']) && $fileData['error'] == 0) {
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $file_extension = pathinfo($fileData["name"], PATHINFO_EXTENSION);
        $unique_file_name = "resume_" . $userId . "_" . time() . "." . $file_extension;
        $target_file = $targetDir . $unique_file_name;
        $move_function = defined('TESTING_MODE') ? 'copy' : 'move_uploaded_file';
        if ($move_function($fileData["tmp_name"], $target_file)) {
            return $target_file;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['applicant_id']) ? (int)$_POST['applicant_id'] : null;
    $page = isset($_POST['page']) ? $_POST['page'] : 'personal_info'; // Default to personal_info

    if ($user_id === null) {
        $_SESSION['error'] = "User ID not provided.";
        if (!defined('TESTING_MODE')) {
            header('Location: profile.php');
            exit;
        }
    }

    $pdo = DatabaseConnection::getConnection();
    $success = false;
    $applicantData = [];

    try {
        $pdo->beginTransaction(); // Start transaction
        switch ($page) {
            case 'personal_info':
                $applicantData = getPersonalInfoUpdateData($_POST);
                error_log("PersonalInfoHandler returned: " . print_r($applicantData, true));
                break;
            case 'family_background':
                $applicantData = getFamilyBackgroundUpdateData($_POST);
                break;
            case 'documents':
                $resume_path = handleFileUpload($user_id, isset($_FILES['resume']) ? $_FILES['resume'] : []);
                $applicantData = getDocumentsUpdateData($_POST, $resume_path);
                break;
            case 'job_preferences':
                $applicantData = getJobPreferencesUpdateData($_POST);
                break;
            case 'health':
                $applicantData = getHealthUpdateData($_POST);
                break;
            case 'education':
                $educationData = getEducationUpdateData($_POST);
                $success = ApplicantEducationUpdater::updateApplicantEducation($pdo, $user_id, $educationData);
                break;
            case 'certificates':
                $certificateData = getCertificatesUpdateData($_POST);
                $success = ApplicantCertificateUpdater::updateApplicantCertificate($pdo, $user_id, $certificateData);
                break;
            case 'work_experience':
                $experienceData = isset($_POST['experience']) ? $_POST['experience'] : [];
                $success = handleWorkExperienceUpdate($pdo, $user_id, $experienceData);
                break;
            default:
                $_SESSION['error'] = "Invalid update section specified.";
                break;
        }

        // Handle password update separately if provided
        if (isset($_POST['password']) && !empty($_POST['password'])) {
            $applicantData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
        }

        // Update applicant table if there's data for it
        if (!empty($applicantData)) {
            $success = ApplicantUpdater::updateApplicant($pdo, $user_id, $applicantData);
        }

        $pdo->commit(); // Commit transaction

        if ($success) {
            $_SESSION['message'] = 'Profile updated successfully!';
        } else if (!isset($_SESSION['error'])) {
            $_SESSION['message'] = 'No changes were made or an unknown error occurred.';
        }

    } catch (PDOException $e) {
        $pdo->rollBack(); // Rollback transaction on error
        $_SESSION['error'] = "Database operation failed: " . $e->getMessage();
    } catch (Exception $e) {
        $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
    }

    if (!defined('TESTING_MODE')) {
        header('Location: profile.php?page=' . $page);
        exit;
    }

} else {
    if (!defined('TESTING_MODE')) {
        header('Location: profile.php');
        exit;
    }
}
?>