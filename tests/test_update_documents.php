<?php

require_once('bootstrap.php');

$pdo = null;
$test_user_id = null;
$uploaded_file_path = null;
$dummy_resume_path = __DIR__ . '/dummy_resume.txt';

try {
    // 1. Setup
    $pdo = get_test_pdo();
    $test_user_id = create_test_user($pdo, 'test.docs.' . time() . '@example.com', 'password123');
    $_SESSION['user_id'] = $test_user_id;

    // Create a dummy file for upload
    file_put_contents($dummy_resume_path, 'This is a test resume file.');

    // 2. Prepare POST and FILES data
    $_POST = [
        'applicant_id' => $test_user_id,
        'passportNumber' => 'TEST12345',
        // Add other post fields with dummy data
        'passportDateIssued' => '2020-01-01',
        'passportPlaceIssue' => 'Manila',
        'passportExpiration' => '2030-01-01',
        'visaNumber' => '',
        'visaExpiry' => '',
        'visaDuration' => '',
        'medicalExpiry' => '',
        'medicalStatus' => '',
        'medicalRemarks' => '',
        'policeClearanceExpiry' => '',
        'policeClearanceStatus' => '',
        'policeClearanceRemarks' => '',
        'nbiExpiry' => '',
        'nbiStatus' => '',
        'nbiRemarks' => '',
        'prcLicenseExpiry' => '',
        'prcLicenseStatus' => '',
        'prcLicenseRemarks' => '',
        'tesdaCertificateExpiry' => '',
        'tesdaCertificateStatus' => '',
        'tesdaCertificateRemarks' => '',
        'otherCertificateExpiry' => '',
        'otherCertificateStatus' => '',
    ];

    $_FILES['resume'] = [
        'name' => 'test_resume.txt',
        'type' => 'text/plain',
        'tmp_name' => $dummy_resume_path,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($dummy_resume_path)
    ];

    // 3. Execute
    include(dirname(__DIR__) . '/skilled/actions/update_documents.php');

    // 4. Assert
    $stmt = $pdo->prepare("SELECT applicant_cv, passport_number FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$test_user_id]);
    $updated_user = $stmt->fetch();

    if (!$updated_user) {
        throw new Exception("Test user not found after update.");
    }

    // Assert DB record was updated
    if (empty($updated_user['applicant_cv'])) {
        throw new Exception("Assertion failed: applicant_cv column is empty.");
    }
    if ($updated_user['passport_number'] !== 'TEST12345') {
        throw new Exception("Assertion failed: passport_number was not updated.");
    }

    // Assert file was "uploaded"
    $uploaded_file_path = $updated_user['applicant_cv'];
    if (!file_exists($uploaded_file_path)) {
        throw new Exception("Assertion failed: Uploaded file does not exist at path: " . $uploaded_file_path);
    }

} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // 5. Teardown
    if ($pdo && $test_user_id) {
        delete_test_user($pdo, $test_user_id);
    }
    if (file_exists($dummy_resume_path)) {
        unlink($dummy_resume_path);
    }
    if ($uploaded_file_path && file_exists($uploaded_file_path)) {
        unlink($uploaded_file_path);
    }
    ob_end_clean();
}
