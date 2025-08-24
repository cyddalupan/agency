<?php

require_once('bootstrap.php');

$pdo = null;
$test_user_id = null;

try {
    // 1. Setup
    $pdo = get_test_pdo();
    $test_user_id = create_test_user($pdo, 'test.health.' . time() . '@example.com', 'password123');
    $_SESSION['user_id'] = $test_user_id;

    // 2. Prepare POST data
    $vaccine_name = "Pfizer";

    $_POST = [
        'applicant_id' => $test_user_id,
        'hasTattoo' => '1', // This will set t1 to 1
        'vaccineName' => $vaccine_name,
        // Add other fields to avoid notices, though the handler checks `isset`
        'hasHemorrhoids' => null,
        'hasDiabetes' => null,
        'hasHighBlood' => null,
        'hasHeartProblem' => null,
        'hasAllergies' => null,
        'hasCyst' => null,
        'hasAsthma' => null,
        'tattooNeck' => '',
        'tattooBack' => '',
        'tattooHands' => '',
        'tattooThigh' => '',
        'tattooLegs' => '',
        'tattooFoot' => '',
        'medicalHistoryOthers' => '',
        'covidVaccin' => null,
        'firstDose' => '',
        'secondDose' => '',
        'vaccineLocation' => '',
        'boqCard' => '',
        'vaccineCert' => '',
        'booster' => null,
        'boosterName' => '',
        'boosterDate' => '',
    ];

    // 3. Execute
    include(dirname(__DIR__) . '/skilled/actions/update_health.php');

    // 4. Assert
    $stmt = $pdo->prepare("SELECT t1, covid_name FROM applicant WHERE applicant_id = ?");
    $stmt->execute([$test_user_id]);
    $updated_user = $stmt->fetch();

    if (!$updated_user) {
        throw new Exception("Test user not found after update.");
    }

    if ($updated_user['t1'] != 1) {
        throw new Exception("Assertion failed: t1 (hasTattoo) was not updated correctly.");
    }

    if ($updated_user['covid_name'] !== $vaccine_name) {
        throw new Exception("Assertion failed: covid_name was not updated.");
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
