<?php

function getDocumentsUpdateData($postData, $resumePath = null)
{
    $data = [
        'passport_number' => isset($postData['passportNumber']) ? $postData['passportNumber'] : null,
        'passport_issue' => isset($postData['passportDateIssued']) ? $postData['passportDateIssued'] : null,
        'passport_issue_place' => isset($postData['passportPlaceIssue']) ? $postData['passportPlaceIssue'] : null,
        'passport_expiration' => isset($postData['passportExpiration']) ? $postData['passportExpiration'] : null,
        'applicant_visa_number' => isset($postData['visaNumber']) ? $postData['visaNumber'] : null,
        'applicant_visa_expiry' => isset($postData['visaExpiry']) ? $postData['visaExpiry'] : null,
        'cyd_visa_duration' => isset($postData['visaDuration']) ? $postData['visaDuration'] : null,
        'applicant_medical_expiry' => isset($postData['medicalExpiry']) ? $postData['medicalExpiry'] : null,
        'applicant_medical_status' => isset($postData['medicalStatus']) ? $postData['medicalStatus'] : null,
        'applicant_medical_remarks' => isset($postData['medicalRemarks']) ? $postData['medicalRemarks'] : null,
        'applicant_police_clearance_expiry' => isset($postData['policeClearanceExpiry']) ? $postData['policeClearanceExpiry'] : null,
        'applicant_police_clearance_status' => isset($postData['policeClearanceStatus']) ? $postData['policeClearanceStatus'] : null,
        'applicant_police_clearance_remarks' => isset($postData['policeClearanceRemarks']) ? $postData['policeClearanceRemarks'] : null,
        'applicant_nbi_expiry' => isset($postData['nbiExpiry']) ? $postData['nbiExpiry'] : null,
        'applicant_nbi_status' => isset($postData['nbiStatus']) ? $postData['nbiStatus'] : null,
        'applicant_nbi_remarks' => isset($postData['nbiRemarks']) ? $postData['nbiRemarks'] : null,
        'applicant_prc_license_expiry' => isset($postData['prcLicenseExpiry']) ? $postData['prcLicenseExpiry'] : null,
        'applicant_prc_license_status' => isset($postData['prcLicenseStatus']) ? $postData['prcLicenseStatus'] : null,
        'applicant_prc_license_remarks' => isset($postData['prcLicenseRemarks']) ? $postData['prcLicenseRemarks'] : null,
        'applicant_tesda_certificate_expiry' => isset($postData['tesdaCertificateExpiry']) ? $postData['tesdaCertificateExpiry'] : null,
        'applicant_tesda_certificate_status' => isset($postData['tesdaCertificateStatus']) ? $postData['tesdaCertificateStatus'] : null,
        'applicant_tesda_certificate_remarks' => isset($postData['tesdaCertificateRemarks']) ? $postData['tesdaCertificateRemarks'] : null,
        'applicant_other_certificate_expiry' => isset($postData['otherCertificateExpiry']) ? $postData['otherCertificateExpiry'] : null,
        'applicant_other_certificate_status' => isset($postData['otherCertificateStatus']) ? $postData['otherCertificateStatus'] : null,
    ];

    if ($resumePath !== null) {
        $data['applicant_cv'] = $resumePath;
    }

    return $data;
}
