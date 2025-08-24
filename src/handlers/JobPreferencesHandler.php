<?php

function getJobPreferencesUpdateData($postData)
{
    return [
        'applicant_position_type' => isset($postData['positionType']) ? (string)$postData['positionType'] : '',
        'currency' => isset($postData['currency']) ? (string)$postData['currency'] : '',
        'applicant_expected_salary' => isset($postData['expectedSalary']) ? (float)$postData['expectedSalary'] : 0.0,
        'applicant_preferred_country' => isset($postData['preferredCountry']) ? (string)$postData['preferredCountry'] : '',
        'applicant_other_skills' => isset($postData['otherSkills']) ? (string)$postData['otherSkills'] : '',
        'personalAbilities' => isset($postData['personalAbilities']) ? (string)$postData['personalAbilities'] : '',
    ];
}
