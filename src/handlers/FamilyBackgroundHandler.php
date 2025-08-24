<?php

function getFamilyBackgroundUpdateData($postData)
{
    return [
        'partner_husband' => isset($postData['partnerName']) ? $postData['partnerName'] : null,
        'partner_occupation' => isset($postData['partnerOccupation']) ? $postData['partnerOccupation'] : null,
        'children' => isset($postData['children']) ? $postData['children'] : null,
        'applicant_mothers' => isset($postData['motherName']) ? $postData['motherName'] : null,
        'occ_of_mom' => isset($postData['motherOccupation']) ? $postData['motherOccupation'] : null,
        'nam_of_fat' => isset($postData['fatherName']) ? $postData['fatherName'] : null,
        'occ_of_fat' => isset($postData['fatherOccupation']) ? $postData['fatherOccupation'] : null,
        'pos_in_fam' => isset($postData['positionInFamily']) ? $postData['positionInFamily'] : null,
        'no_of_bro' => isset($postData['numBrothers']) ? $postData['numBrothers'] : null,
        'no_of_sis' => isset($postData['numSisters']) ? $postData['numSisters'] : null,
        'relative_name' => isset($postData['relativeName']) ? $postData['relativeName'] : null,
        'relative_mobile' => isset($postData['relativeMobile']) ? $postData['relativeMobile'] : null,
        'applicant_incase_name' => isset($postData['emergencyContactName']) ? $postData['emergencyContactName'] : null,
        'applicant_incase_relation' => isset($postData['emergencyContactRelationship']) ? $postData['emergencyContactRelationship'] : null,
        'applicant_incase_contact' => isset($postData['emergencyContactNumber']) ? $postData['emergencyContactNumber'] : null,
        'applicant_incase_address' => isset($postData['emergencyContactAddress']) ? $postData['emergencyContactAddress'] : null,
    ];
}
