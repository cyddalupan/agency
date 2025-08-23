<?php
// skilled/update_handlers/documents.php

if (!defined('DB_HOST')) {
    require_once(dirname(dirname(__DIR__)) . '/config.php');
}

$fields = [
    'passport_number' => $passportNumber,
    'passport_issue' => $passportDateIssued,
    'passport_issue_place' => $passportPlaceIssue,
    'passport_expiration' => $passportExpiration,
    'applicant_visa_number' => $visaNumber,
    'applicant_visa_expiry' => $visaExpiry,
    'cyd_visa_duration' => $visaDuration,
    'applicant_medical_expiry' => $medicalExpiry,
    'applicant_medical_status' => $medicalStatus,
    'applicant_medical_remarks' => $medicalRemarks,
    'applicant_police_clearance_expiry' => $policeClearanceExpiry,
    'applicant_police_clearance_status' => $policeClearanceStatus,
    'applicant_police_clearance_remarks' => $policeClearanceRemarks,
    'applicant_nbi_expiry' => $nbiExpiry,
    'applicant_nbi_status' => $nbiStatus,
    'applicant_nbi_remarks' => $nbiRemarks,
    'applicant_prc_license_expiry' => $prcLicenseExpiry,
    'applicant_prc_license_status' => $prcLicenseStatus,
    'applicant_prc_license_remarks' => $prcLicenseRemarks,
    'applicant_tesda_certificate_expiry' => $tesdaCertificateExpiry,
    'applicant_tesda_certificate_status' => $tesdaCertificateStatus,
    'applicant_tesda_certificate_remarks' => $tesdaCertificateRemarks,
    'applicant_other_certificate_expiry' => $otherCertificateExpiry,
    'applicant_other_certificate_status' => $otherCertificateStatus,
];

$sql_parts = [];
$params = [];

foreach ($fields as $key => $value) {
    if ($value !== null) {
        $sql_parts[] = "`{$key}` = :{$key}";
        $params[":{$key}"] = $value;
    }
}

if ($resume_path) {
    $sql_parts[] = "`applicant_cv` = :applicant_cv";
    $params[":applicant_cv"] = $resume_path;
}
