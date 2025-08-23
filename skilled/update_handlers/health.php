<?php
// skilled/update_handlers/health.php

if (!defined('DB_HOST')) {
    require_once(dirname(dirname(__DIR__)) . '/config.php');
}

return [
    't1' => isset($hasTattoo) ? $hasTattoo : 0,
    't2' => isset($hasHemorrhoids) ? $hasHemorrhoids : 0,
    't3' => isset($hasDiabetes) ? $hasDiabetes : 0,
    't4' => isset($hasHighBlood) ? $hasHighBlood : 0,
    't5' => isset($hasHeartProblem) ? $hasHeartProblem : 0,
    't6' => isset($hasAllergies) ? $hasAllergies : 0,
    't7' => isset($hasCyst) ? $hasCyst : 0,
    't8' => isset($hasAsthma) ? $hasAsthma : 0,
    'is_manicure' => isset($tattooNeck) ? $tattooNeck : null,
    'is_massage' => isset($tattooBack) ? $tattooBack : null,
    'is_blower' => isset($tattooHands) ? $tattooHands : null,
    'is_coloring' => isset($tattooThigh) ? $tattooThigh : null,
    'is_sewing' => isset($tattooLegs) ? $tattooLegs : null,
    'is_computer' => isset($tattooFoot) ? $tattooFoot : null,
    'applicant_jobs' => isset($medicalHistoryOthers) ? $medicalHistoryOthers : null,
    'covidme' => isset($covidVaccin) ? $covidVaccin : 0,
    'covid_name' => isset($vaccineName) ? $vaccineName : null,
    'covid_date' => isset($firstDose) ? $firstDose : null,
    'covid_date2' => isset($secondDose) ? $secondDose : null,
    'covid_loc' => isset($vaccineLocation) ? $vaccineLocation : null,
    'covid_yellow' => isset($boqCard) ? $boqCard : null,
    'covid_cert' => isset($vaccineCert) ? $vaccineCert : null,
    'covidb1' => isset($booster) ? $booster : 0,
    'covidb2' => isset($boosterName) ? $boosterName : null,
    'covidb3' => isset($boosterDate) ? $boosterDate : null,
];
