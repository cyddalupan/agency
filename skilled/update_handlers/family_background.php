<?php
// skilled/update_handlers/family_background.php

if (!defined('DB_HOST')) {
    require_once(dirname(dirname(__DIR__)) . '/config.php');
}

return [
    'partner_husband' => isset($partnerName) ? $partnerName : null,
    'partner_occupation' => isset($partnerOccupation) ? $partnerOccupation : null,
    'children' => isset($children) ? $children : null,
    'applicant_mothers' => isset($motherName) ? $motherName : null,
    'occ_of_mom' => isset($motherOccupation) ? $motherOccupation : null,
    'nam_of_fat' => isset($fatherName) ? $fatherName : null,
    'occ_of_fat' => isset($fatherOccupation) ? $fatherOccupation : null,
    'pos_in_fam' => isset($positionInFamily) ? $positionInFamily : null,
    'no_of_bro' => isset($numBrothers) ? $numBrothers : null,
    'no_of_sis' => isset($numSisters) ? $numSisters : null,
    'relative_name' => isset($relativeName) ? $relativeName : null,
    'relative_mobile' => isset($relativeMobile) ? $relativeMobile : null,
    'applicant_incase_name' => isset($emergencyContactName) ? $emergencyContactName : null,
    'applicant_incase_relation' => isset($emergencyContactRelationship) ? $emergencyContactRelationship : null,
    'applicant_incase_contact' => isset($emergencyContactNumber) ? $emergencyContactNumber : null,
    'applicant_incase_address' => isset($emergencyContactAddress) ? $emergencyContactAddress : null,
];
