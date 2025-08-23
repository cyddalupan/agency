<?php
// skilled/update_handlers/job_preferences.php

if (!defined('DB_HOST')) {
    require_once(dirname(dirname(__DIR__)) . '/config.php');
}

$fields = [
    'applicant_position_type' => $positionType,
    'currency' => $currency,
    'applicant_expected_salary' => $expectedSalary,
    'applicant_preferred_country' => $preferredCountry,
    'applicant_other_skills' => $otherSkills,
    'personalAbilities' => $personalAbilities,
];

$sql_parts = [];
$params = [];

foreach ($fields as $key => $value) {
    if ($value !== null) {
        $sql_parts[] = "`{$key}` = :{$key}";
        $params[":{$key}"] = $value;
    }
}
