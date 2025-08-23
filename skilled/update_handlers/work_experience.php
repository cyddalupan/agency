<?php
// skilled/update_handlers/work_experience.php

if (!defined('DB_HOST')) {
    require_once(dirname(dirname(__DIR__)) . '/config.php');
}

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

// For simplicity, we'll delete all existing experiences and re-insert them.
// A more optimized approach would be to track individual deletions.
$delete_stmt = $pdo->prepare("DELETE FROM applicant_experiences WHERE experience_applicant = ?");
$delete_stmt->execute([$user_id]);

if (isset($_POST['experience']) && is_array($_POST['experience'])) {
    foreach ($_POST['experience'] as $exp) {
        $sql = "INSERT INTO applicant_experiences (experience_applicant, experience_company, experience_position, experience_from, experience_to, experience_country, experience_salary, reasonOfLeaving) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $exp['experience_company'],
            $exp['experience_position'],
            $exp['experience_from'],
            $exp['experience_to'],
            $exp['experience_country'],
            $exp['experience_salary'],
            $exp['reasonOfLeaving'],
        ]);
    }
}

// Since the logic is self-contained and doesn't need to merge with other queries, we can stop here.
// The main update_profile.php will handle the redirect.
$sql_parts = []; // Prevent the main script from running another query
