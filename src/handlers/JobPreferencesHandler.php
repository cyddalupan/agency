<?php

function getJobPreferencesUpdateData($postData)
{
    return [
        'applicant_position_type' => isset($postData['applicant_position_type']) ? $postData['applicant_position_type'] : null,
        'currency' => isset($postData['currency']) ? $postData['currency'] : null,
        'applicant_expected_salary' => isset($postData['applicant_expected_salary']) ? $postData['applicant_expected_salary'] : null,
        'applicant_preferred_country' => isset($postData['preferredCountry']) ? $postData['preferredCountry'] : null,
        'applicant_other_skills' => isset($postData['otherSkills']) ? $postData['otherSkills'] : null,
        'personalAbilities' => isset($postData['personalAbilities']) ? $postData['personalAbilities'] : null,
    ];
}
