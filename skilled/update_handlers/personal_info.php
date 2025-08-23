<?php
// skilled/update_handlers/personal_info.php

if (!defined('DB_HOST')) {
    require_once(dirname(dirname(__DIR__)) . '/config.php');
}

return [
    'applicant_first' => isset($firstName) ? $firstName : null,
    'applicant_middle' => isset($middleName) ? $middleName : null,
    'applicant_last' => isset($lastName) ? $lastName : null,
    'applicant_age' => isset($age) ? $age : null,
    'applicant_contacts' => isset($contactNumber) ? $contactNumber : null,
    'contacts2' => isset($otherContactNumber) ? $otherContactNumber : null,
    'contacts3' => isset($anotherContactNumber) ? $anotherContactNumber : null,
    'applicant_email' => isset($email) ? $email : null,
    'fra_remarks' => isset($remarks) ? $remarks : null,
    'applicant_gender' => isset($gender) ? $gender : null,
    'applicant_nationality' => isset($nationality) ? $nationality : null,
    'applicant_civil_status' => isset($civilStatus) ? $civilStatus : null,
    'applicant_address' => isset($address) ? $address : null,
    'contacts4' => isset($placeOfBirth) ? $placeOfBirth : null,
    'applicant_height' => isset($height) ? $height : null,
    'applicant_weight' => isset($weight) ? $weight : null,
    'applicant_religion' => isset($religion) ? $religion : null,
    'applicant_languages' => isset($languages) ? $languages : null,
    'date_applied' => isset($dateApplied) ? $dateApplied : null,
    'applicant_training_branch' => isset($trainingBranch) ? $trainingBranch : null,
    'applicant_source' => isset($source) ? $source : null,
    'applicant_recruitment_agent' => isset($recruitmentAgent) ? $recruitmentAgent : null,
    'repat_checkbox' => isset($repatriated) ? $repatriated : 0,
    'repat_date' => isset($repatriationDate) ? $repatriationDate : null,
    'applicant_ex' => isset($applicantEx) ? $applicantEx : null,
    'typess' => isset($branch) ? $branch : null,
    'typess1' => isset($transferBranch) ? $transferBranch : null,
    'applicant_ppt_pay' => isset($waitlist) ? $waitlist : null,
    'other_source' => isset($otherSource) ? $otherSource : null,
    'date_by' => isset($interviewBy) ? $interviewBy : null,
    'remarks_3' => isset($remarksForResume) ? $remarksForResume : null,
    'applicant_birthdate' => isset($birthdate) ? $birthdate : null,
];
