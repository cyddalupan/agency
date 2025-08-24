<?php

function getPersonalInfoUpdateData($postData)
{
    // Calculate birthdate from age if age is provided
    $birthdate = isset($postData['age']) && $postData['age'] ? (date('Y') - $postData['age']) . "-01-01" : null;

    return [
        'applicant_first' => isset($postData['firstName']) ? $postData['firstName'] : null,
        'applicant_middle' => isset($postData['middleName']) ? $postData['middleName'] : null,
        'applicant_last' => isset($postData['lastName']) ? $postData['lastName'] : null,
        'applicant_age' => isset($postData['age']) ? (int)$postData['age'] : null,
        'applicant_contacts' => isset($postData['contactNumber']) ? $postData['contactNumber'] : null,
        'contacts2' => isset($postData['otherContactNumber']) ? $postData['otherContactNumber'] : null,
        'contacts3' => isset($postData['anotherContactNumber']) ? $postData['anotherContactNumber'] : null,
        'applicant_email' => isset($postData['email']) ? $postData['email'] : null,
        'fra_remarks' => isset($postData['remarks']) ? $postData['remarks'] : null,
        'applicant_gender' => isset($postData['gender']) ? $postData['gender'] : null,
        'applicant_nationality' => isset($postData['nationality']) ? $postData['nationality'] : null,
        'applicant_civil_status' => isset($postData['civilStatus']) ? $postData['civilStatus'] : null,
        'applicant_address' => isset($postData['address']) ? $postData['address'] : null,
        'contacts4' => isset($postData['placeOfBirth']) ? $postData['placeOfBirth'] : null,
        'applicant_height' => isset($postData['height']) ? (int)$postData['height'] : null,
        'applicant_weight' => isset($postData['weight']) ? (int)$postData['weight'] : null,
        'applicant_religion' => isset($postData['religion']) ? $postData['religion'] : null,
        'applicant_languages' => isset($postData['languages']) ? $postData['languages'] : null,
        'date_applied' => isset($postData['dateApplied']) ? $postData['dateApplied'] : null,
        'applicant_training_branch' => isset($postData['trainingBranch']) ? $postData['trainingBranch'] : null,
        'applicant_source' => isset($postData['source']) ? (string)$postData['source'] : null,
        'applicant_recruitment_agent' => isset($postData['recruitmentAgent']) ? $postData['recruitmentAgent'] : null,
        'repat_checkbox' => isset($postData['repatriated']) ? 1 : 0,
        'repat_date' => isset($postData['repatriationDate']) ? $postData['repatriationDate'] : null,
        'applicant_ex' => isset($postData['applicantEx']) ? $postData['applicantEx'] : null,
        'typess' => isset($postData['branch']) ? (string)$postData['branch'] : null,
        'typess1' => isset($postData['transferBranch']) ? (string)$postData['transferBranch'] : null,
        'applicant_ppt_pay' => isset($postData['waitlist']) ? $postData['waitlist'] : null,
        'other_source' => isset($postData['otherSource']) ? $postData['otherSource'] : null,
        'date_by' => isset($postData['interviewBy']) ? $postData['interviewBy'] : null,
        'remarks_3' => isset($postData['remarksForResume']) ? $postData['remarksForResume'] : null,
        'applicant_birthdate' => $birthdate,
    ];
}
