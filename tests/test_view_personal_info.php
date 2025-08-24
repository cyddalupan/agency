<?php

require_once('bootstrap.php');

$pdo = null;
$test_user_id = null;

try {
    // 1. Setup
    $pdo = get_test_pdo();
    $test_user_id = create_test_user($pdo, 'test.view.personal.' . time() . '@example.com', 'password123');
    $_SESSION['user_id'] = $test_user_id;

    // 2. Execute
    // Simply include the page. The bootstrap file already started output buffering.
    // If there is a fatal error, the test runner's `set -e` will catch it.
    include(dirname(__DIR__) . '/skilled/personal_info.php');

} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // 3. Teardown
    if ($pdo && $test_user_id) {
        delete_test_user($pdo, $test_user_id);
    }
    // Discard the output buffer
    ob_end_clean();
}

