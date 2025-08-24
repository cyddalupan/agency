<?php

require_once('bootstrap.php');

$pdo = null;
$test_user_id = null;

try {
    // 1. Setup
    $pdo = get_test_pdo();
    $test_user_id = create_test_user($pdo, 'test.certs.' . time() . '@example.com', 'password123');
    $_SESSION['user_id'] = $test_user_id;

    // ===================================================================
    // Test Case 1: INSERT a new certificate record
    // ===================================================================
    $prc_type_insert = "Nurse";
    $tesda_name_insert = "Caregiving NC II";

    $_POST = [
        'applicant_id' => $test_user_id,
        'certificate_prc_type' => $prc_type_insert,
        'tesda_name' => $tesda_name_insert,
        // Add other fields as needed
    ];

    // Execute the action script
    include(dirname(__DIR__) . '/skilled/actions/update_certificates.php');

    // Assertions for INSERT
    $stmt = $pdo->prepare("SELECT * FROM applicant_certificate WHERE certificate_applicant = ?");
    $stmt->execute([$test_user_id]);
    $cert_record = $stmt->fetch();

    if (!$cert_record) {
        throw new Exception("INSERT failed: Certificate record not found for user.");
    }
    if ($cert_record['certificate_prc_type'] !== $prc_type_insert) {
        throw new Exception("INSERT failed: certificate_prc_type mismatch.");
    }
    if ($cert_record['certificate_tesda_name'] !== $tesda_name_insert) {
        throw new Exception("INSERT failed: certificate_tesda_name mismatch.");
    }

    // ===================================================================
    // Test Case 2: UPDATE the existing certificate record
    // ===================================================================
    $prc_type_update = "Midwife";
    $tesda_name_update = "Health Care Services NC II";

    $_POST = [
        'applicant_id' => $test_user_id,
        'certificate_prc_type' => $prc_type_update,
        'tesda_name' => $tesda_name_update,
        // Add other fields as needed
    ];

    // Execute the action script again
    include(dirname(__DIR__) . '/skilled/actions/update_certificates.php');

    // Assertions for UPDATE
    $stmt->execute([$test_user_id]); // Re-fetch the data
    $cert_record_updated = $stmt->fetch();

    if (!$cert_record_updated) {
        throw new Exception("UPDATE failed: Certificate record not found for user.");
    }
    if ($cert_record_updated['certificate_prc_type'] !== $prc_type_update) {
        throw new Exception("UPDATE failed: certificate_prc_type was not updated.");
    }
    if ($cert_record_updated['certificate_tesda_name'] !== $tesda_name_update) {
        throw new Exception("UPDATE failed: certificate_tesda_name was not updated.");
    }


} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // 5. Teardown
    if ($pdo && $test_user_id) {
        $pdo->prepare("DELETE FROM applicant_certificate WHERE certificate_applicant = ?")->execute([$test_user_id]);
        delete_test_user($pdo, $test_user_id);
    }
    ob_end_clean();
}
