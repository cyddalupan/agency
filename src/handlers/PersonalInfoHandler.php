<?php

function getPersonalInfoUpdateData($postData)
{
    // Calculate birthdate from age if age is provided
    $birthdate = isset($postData['age']) && $postData['age'] ? (date('Y') - $postData['age']) . "-01-01" : null;

    return [
                'applicant_first' => isset($postData['firstName']) ? (string)$postData['firstName'] : '',
        'applicant_middle' => isset($postData['middleName']) ? (string)$postData['middleName'] : '',
        'applicant_last' => isset($postData['lastName']) ? (string)$postData['lastName'] : '',
        'applicant_age' => isset($postData['age']) ? (int)$postData['age'] : 0,
        'applicant_contacts' => isset($postData['contactNumber']) ? (string)$postData['contactNumber'] : '',
        'applicant_email' => isset($postData['email']) ? (string)$postData['email'] : '',
        'fra_remarks' => isset($postData['remarks']) ? (string)$postData['remarks'] : '',
        'applicant_gender' => isset($postData['gender']) ? (string)$postData['gender'] : '',
        'applicant_nationality' => isset($postData['nationality']) ? (string)$postData['nationality'] : '',
        'applicant_civil_status' => isset($postData['civilStatus']) ? (string)$postData['civilStatus'] : '',
        'applicant_address' => isset($postData['address']) ? (string)$postData['address'] : '',
        'applicant_height' => isset($postData['height']) ? (int)$postData['height'] : 0,
        'applicant_weight' => isset($postData['weight']) ? (int)$postData['weight'] : 0,
        'applicant_religion' => isset($postData['religion']) ? (string)$postData['religion'] : '',
        'applicant_languages' => isset($postData['languages']) ? (string)$postData['languages'] : '',
    ];
}
