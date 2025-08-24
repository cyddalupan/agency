<?php

function getHealthUpdateData($postData)
{
    return [
        't1' => isset($postData['hasTattoo']) ? 1 : 0,
        't2' => isset($postData['hasHemorrhoids']) ? 1 : 0,
        't3' => isset($postData['hasDiabetes']) ? 1 : 0,
        't4' => isset($postData['hasHighBlood']) ? 1 : 0,
        't5' => isset($postData['hasHeartProblem']) ? 1 : 0,
        't6' => isset($postData['hasAllergies']) ? 1 : 0,
        't7' => isset($postData['hasCyst']) ? 1 : 0,
        't8' => isset($postData['hasAsthma']) ? 1 : 0,
        'is_manicure' => isset($postData['tattooNeck']) ? $postData['tattooNeck'] : null,
        'is_massage' => isset($postData['tattooBack']) ? $postData['tattooBack'] : null,
        'is_blower' => isset($postData['tattooHands']) ? $postData['tattooHands'] : null,
        'is_coloring' => isset($postData['tattooThigh']) ? $postData['tattooThigh'] : null,
        'is_sewing' => isset($postData['tattooLegs']) ? $postData['tattooLegs'] : null,
        'is_computer' => isset($postData['tattooFoot']) ? $postData['tattooFoot'] : null,
        'applicant_jobs' => isset($postData['medicalHistoryOthers']) ? $postData['medicalHistoryOthers'] : null,
        'covidme' => isset($postData['covidVaccin']) ? 1 : 0,
        'covid_name' => isset($postData['vaccineName']) ? $postData['vaccineName'] : null,
        'covid_date' => isset($postData['firstDose']) ? $postData['firstDose'] : null,
        'covid_date2' => isset($postData['secondDose']) ? $postData['secondDose'] : null,
        'covid_loc' => isset($postData['vaccineLocation']) ? $postData['vaccineLocation'] : null,
        'covid_yellow' => isset($postData['boqCard']) ? $postData['boqCard'] : null,
        'covid_cert' => isset($postData['vaccineCert']) ? $postData['vaccineCert'] : null,
        'covidb1' => isset($postData['booster']) ? 1 : 0,
        'covidb2' => isset($postData['boosterName']) ? $postData['boosterName'] : null,
        'covidb3' => isset($postData['boosterDate']) ? $postData['boosterDate'] : null,
    ];
}
