<?php

require_once('bootstrap.php');

$pdo = null;
$test_user_id = null;

try {
    // 1. Setup
    $pdo = get_test_pdo();
    $test_user_id = create_test_user($pdo, 'test.workexp.' . time() . '@example.com', 'password123');
    $_SESSION['user_id'] = $test_user_id;

    // Add a dummy initial experience to ensure it gets deleted
    $pdo->prepare("INSERT INTO applicant_experiences (experience_applicant, experience_company) VALUES (?, 'Old Company')")->execute([$test_user_id]);

    // 2. Prepare POST data
    $new_company = "New Company Inc.";
    $new_position = "Software Developer";

    $_POST = [
        'applicant_id' => $test_user_id,
        'experience' => [
            [
                'experience_company' => $new_company,
                'experience_position' => $new_position,
                'experience_from' => '2022-01-01',
                'experience_to' => '2023-01-01',
                'experience_country' => 'USA',
                'experience_salary' => '80000',
                'reasonOfLeaving' => 'Better opportunity'
            ],
            // Add a second experience to test multiple inserts
            [
                'experience_company' => 'Another Company',
                'experience_position' => 'Web Developer',
                'experience_from' => '2021-01-01',
                'experience_to' => '2022-01-01',
                'experience_country' => 'Canada',
                'experience_salary' => '70000',
                'reasonOfLeaving' => 'Project ended'
            ]
        ]
    ];

    // 3. Execute
    include(dirname(__DIR__) . '/skilled/actions/update_work_experience.php');

    // 4. Assert
    $stmt = $pdo->prepare("SELECT * FROM applicant_experiences WHERE experience_applicant = ? ORDER BY experience_from DESC");
    $stmt->execute([$test_user_id]);
    $experiences = $stmt->fetchAll();

    if (count($experiences) !== 2) {
        throw new Exception("Assertion failed: Expected 2 work experiences, but found " . count($experiences));
    }

    if ($experiences[0]['experience_company'] !== $new_company) {
        throw new Exception("Assertion failed: Company name was not updated correctly.");
    }
    
    if ($experiences[0]['experience_position'] !== $new_position) {
        throw new Exception("Assertion failed: Position was not updated correctly.");
    }
    
    if ($experiences[1]['experience_company'] !== 'Another Company') {
        throw new Exception("Assertion failed: Second company name was not updated correctly.");
    }

} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // 5. Teardown
    if ($pdo && $test_user_id) {
        // The action script already deletes old experiences, but we'll be thorough
        $pdo->prepare("DELETE FROM applicant_experiences WHERE experience_applicant = ?")->execute([$test_user_id]);
        delete_test_user($pdo, $test_user_id);
    }
    ob_end_clean();
}

