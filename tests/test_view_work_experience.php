<?php
require_once('bootstrap.php');
$pdo = null; $test_user_id = null;
try {
    $pdo = get_test_pdo();
    $test_user_id = create_test_user($pdo, 'test.view.work.' . time() . '@example.com', 'password123');
    $_SESSION['user_id'] = $test_user_id;
    include(dirname(__DIR__) . '/skilled/work_experience.php');
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if ($pdo && $test_user_id) { delete_test_user($pdo, $test_user_id); }
    ob_end_clean();
}

