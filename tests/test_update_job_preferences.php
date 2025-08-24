<?php

require_once('bootstrap.php');

$pdo = null;
$test_user_id = null;

try {
    // 1. Setup
    $pdo = get_test_pdo();
    $test_user_id = create_test_user($pdo, 'test.jobpref.' . time() . '@example.com', 'password123');
    $_SESSION['user_id'] = $test_user_id;

    // 2. Prepare POST data
    $new_position_type = "Manager";
    $new_salary = "50000";

    $_POST = [
        'applicant_id' => $test_user_id,
        'applicant_position_type' => $new_position_type,
        'currency' => 'USD',
        'applicant_expected_salary' => $new_salary,
        'preferredCountry' => 'Canada',
        'otherSkills' => 'PHP, MySQL',
        'personalAbilities' => 'Teamwork, Leadership'
    ];

    // 3. Execute
    include(dirname(__DIR__) . '/skilled/actions/update_job_preferences.php');

    // 4. Assert
    $stmt = $pdo->prepare("SELECT applicant_position_type, applicant_expected_salary FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$test_user_id]);
    $updated_user = $stmt->fetch();

    if (!$updated_user) {
        throw new Exception("Test user not found after update.");
    }

    if ($updated_user['applicant_position_type'] !== $new_position_type) {
        throw new Exception("Assertion failed: applicant_position_type was not updated.");
    }

    if ($updated_user['applicant_expected_salary'] != $new_salary) { // Use != for numeric comparison
        throw new Exception("Assertion failed: applicant_expected_salary was not updated.");
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
