<?php

function getFamilyBackgroundUpdateData($postData)
{
    return [
        'partner_husband' => isset($postData['partnerName']) ? (string)$postData['partnerName'] : '',
        'partner_occupation' => isset($postData['partnerOccupation']) ? (string)$postData['partnerOccupation'] : '',
        'children' => isset($postData['children']) ? (int)$postData['children'] : 0,
        'applicant_mothers' => isset($postData['motherName']) ? (string)$postData['motherName'] : '',
        'occ_of_mom' => isset($postData['motherOccupation']) ? (string)$postData['motherOccupation'] : '',
        'nam_of_fat' => isset($postData['fatherName']) ? (string)$postData['fatherName'] : '',
        'occ_of_fat' => isset($postData['fatherOccupation']) ? (string)$postData['fatherOccupation'] : '',
        'pos_in_fam' => isset($postData['positionInFamily']) ? (string)$postData['positionInFamily'] : '',
        'no_of_bro' => isset($postData['numBrothers']) ? (int)$postData['numBrothers'] : 0,
        'no_of_sis' => isset($postData['numSisters']) ? (int)$postData['numSisters'] : 0,
        'relative_name' => isset($postData['relativeName']) ? (string)$postData['relativeName'] : '',
        'relative_mobile' => isset($postData['relativeMobile']) ? (string)$postData['relativeMobile'] : '',
        'applicant_incase_name' => isset($postData['emergencyContactName']) ? (string)$postData['emergencyContactName'] : '',
        'applicant_incase_relation' => isset($postData['emergencyContactRelationship']) ? (string)$postData['emergencyContactRelationship'] : '',
        'applicant_incase_contact' => isset($postData['emergencyContactNumber']) ? (string)$postData['emergencyContactNumber'] : '',
        'applicant_incase_address' => isset($postData['emergencyContactAddress']) ? (string)$postData['emergencyContactAddress'] : '',
    ];
}
