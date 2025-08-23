<?php
// skilled/update_handlers/education.php

if (!defined('DB_HOST')) {
    require_once(dirname(dirname(__DIR__)) . '/config.php');
}

$education_fields = [
    'education_highschool' => isset($education_highschool) ? $education_highschool : null,
    'education_highschool_year' => isset($education_highschool_year) ? $education_highschool_year : null,
    'education_college' => isset($education_college) ? $education_college : null,
    'education_college_year' => isset($education_college_year) ? $education_college_year : null,
    'education_college_skills' => isset($education_college_skills) ? $education_college_skills : null,
    'education_mba' => isset($education_mba) ? $education_mba : null,
    'education_mba_year' => isset($education_mba_year) ? $education_mba_year : null,
    'education_mba_course' => isset($education_mba_course) ? $education_mba_course : null,
    'education_others' => isset($education_others) ? $education_others : null,
    'education_others_year' => isset($education_others_year) ? $education_others_year : null,
];

$sql_parts = [];
$params = [];

// Check if education record exists
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

$stmt = $pdo->prepare("SELECT education_id FROM applicant_education WHERE education_applicant = ?");
$stmt->execute([$user_id]);
$education_record = $stmt->fetch();

if ($education_record) {
    // Update existing record
    foreach ($education_fields as $key => $value) {
        if ($value !== null) {
            $sql_parts[] = "`{$key}` = :{$key}";
            $params[":{$key}"] = $value;
        }
    }
    $sql = "UPDATE `applicant_education` SET " . implode(', ', $sql_parts) . " WHERE `education_applicant` = :education_applicant";
    $params[':education_applicant'] = $user_id;
} else {
    // Insert new record
    $education_fields['education_applicant'] = $user_id;
    $columns = array_keys($education_fields);
    $placeholders = array_map(function($col) { return ":{$col}"; }, $columns);
    
    $sql = "INSERT INTO `applicant_education` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    foreach ($education_fields as $key => $value) {
        $params[":{$key}"] = $value;
    }
}
