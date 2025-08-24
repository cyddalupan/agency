<?php

require_once('bootstrap.php');

$pdo = null;
$test_user_id = null;

try {
    // 1. Setup
    $pdo = get_test_pdo();
    $test_user_id = create_test_user($pdo, 'test.family.' . time() . '@example.com', 'password123');
    $_SESSION['user_id'] = $test_user_id;

    // 2. Prepare POST data
    $new_father_name = "John Doe Sr.";
    $new_mother_name = "Jane Doe";

    $_POST = [
        'applicant_id' => $test_user_id,
        'fatherName' => $new_father_name, // Correct POST key
        'fatherOccupation' => 'Farmer',
        'motherName' => $new_mother_name, // Correct POST key
        'motherOccupation' => 'Teacher',
        'partnerName' => '',
        'partnerOccupation' => '',
        'children' => '',
        'positionInFamily' => '',
        'numBrothers' => '',
        'numSisters' => '',
        'relativeName' => '',
        'relativeMobile' => '',
        'emergencyContactName' => '',
        'emergencyContactRelationship' => '',
        'emergencyContactNumber' => '',
        'emergencyContactAddress' => '',
    ];

    // 3. Execute
    include(dirname(__DIR__) . '/skilled/actions/update_family_background.php');

    // 4. Assert
    $stmt = $pdo->prepare("SELECT nam_of_fat, applicant_mothers FROM applicant WHERE applicant_id = ?"); // Correct column names
    $stmt->execute([$test_user_id]);
    $updated_user = $stmt->fetch();

    if (!$updated_user) {
        throw new Exception("Test user not found after update.");
    }

    if ($updated_user['nam_of_fat'] !== $new_father_name) {
        throw new Exception("Assertion failed: Father's name was not updated.");
    }

    if ($updated_user['applicant_mothers'] !== $new_mother_name) {
        throw new Exception("Assertion failed: Mother's name was not updated.");
    }

} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // 5. Teardown
    if ($pdo && $test_user_id) {
        delete_test_user($pdo, $test_user_id);
    }
    ob_end_clean();
}
