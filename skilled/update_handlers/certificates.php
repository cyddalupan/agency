<?php
// skilled/update_handlers/certificates.php

if (!defined('DB_HOST')) {
    require_once(dirname(dirname(__DIR__)) . '/config.php');
}

$certificate_fields = [
    'certificate_prc_type' => isset($certificate_prc_type) ? $certificate_prc_type : null,
    'certificate_prc_rating' => isset($certificate_prc_rating) ? $certificate_prc_rating : null,
    'certificate_prc_take' => isset($certificate_prc_take) ? $certificate_prc_take : null,
    'certificate_saudi_id' => isset($certificate_saudi_id) ? $certificate_saudi_id : null,
    'certificate_dha' => isset($certificate_dha) ? $certificate_dha : null,
    'certificate_ksa' => isset($certificate_ksa) ? $certificate_ksa : null,
    'certificate_haad' => isset($certificate_haad) ? $certificate_haad : null,
    'certificate_qatar' => isset($certificate_qatar) ? $certificate_qatar : null,
    'certificate_nclex' => isset($certificate_nclex) ? $certificate_nclex : null,
    'certificate_nclex_exam' => isset($certificate_nclex_exam) ? $certificate_nclex_exam : null,
    'certificate_ielts' => isset($certificate_ielts) ? $certificate_ielts : null,
    'certificate_ielts_overall' => isset($certificate_ielts_overall) ? $certificate_ielts_overall : null,
    'certificate_ielts_exam' => isset($certificate_ielts_exam) ? $certificate_ielts_exam : null,
    'certificate_cgfns' => isset($certificate_cgfns) ? $certificate_cgfns : null,
    'certificate_cgfns_id' => isset($certificate_cgfns_id) ? $certificate_cgfns_id : null,
    'certificate_cgfns_exam' => isset($certificate_cgfns_exam) ? $certificate_cgfns_exam : null,
    'certificate_vsh_exam' => isset($certificate_vsh_exam) ? $certificate_vsh_exam : null,
    'swab' => isset($swab) ? $swab : null,
    'swab_date' => isset($swab_date) ? $swab_date : null,
    'mmr' => isset($mmr) ? $mmr : null,
    'certificate_medical_clinic' => isset($medical_clinic) ? $medical_clinic : null,
    'certificate_medical_exam_date' => isset($medical_exam_date) ? $medical_exam_date : null,
    'medical_fit' => isset($medical_fit) ? $medical_fit : null,
    'certificate_medical_result' => isset($medical_result) ? $medical_result : null,
    'certificate_medical_remarks' => isset($medical_remarks) ? $medical_remarks : null,
    'certificate_medical_expiration' => isset($medical_expiration) ? $medical_expiration : null,
    'certificate_pt_result' => isset($pt_result) ? $pt_result : null,
    'certificate_pt_result_date' => isset($pt_result_date) ? $pt_result_date : null,
    'omma' => isset($omma) ? $omma : null,
    'omma_date' => isset($omma_date) ? $omma_date : null,
    'polio' => isset($polio) ? $polio : null,
    'polio_date' => isset($polio_date) ? $polio_date : null,
    'certificate_authenticated_nbi' => isset($authenticated_nbi) ? $authenticated_nbi : 0,
    'nbi_expired_date' => isset($nbi_expired_date) ? $nbi_expired_date : null,
    'certificate_insurance' => isset($insurance) ? $insurance : null,
    'insurance_no' => isset($insurance_no) ? $insurance_no : null,
    'certificate_coe' => isset($coe) ? $coe : null,
    'certificate_pdos' => isset($pdos) ? $pdos : 0,
    'fra_pdos' => isset($fra_pdos) ? $fra_pdos : null,
    'certificate_pdos_date' => isset($pdos_date) ? $pdos_date : null,
    'certificate_pdos_no' => isset($pdos_no) ? $pdos_no : null,
    'certificate_tesda' => isset($tesda) ? $tesda : 0,
    'certificate_tesda_name' => isset($tesda_name) ? $tesda_name : null,
    'certificate_tesda_date' => isset($tesda_date) ? $tesda_date : null,
    'certificate_tesda_release' => isset($tesda_release) ? $tesda_release : null,
    'certificate_tesda_assest' => isset($certificate_tesda_assest) ? $certificate_tesda_assest : null,
];

$sql_parts = [];
$params = [];

// Check if certificate record exists
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

$stmt = $pdo->prepare("SELECT certificate_id FROM applicant_certificate WHERE certificate_applicant = ?");
$stmt->execute([$user_id]);
$certificate_record = $stmt->fetch();

if ($certificate_record) {
    // Update existing record
    foreach ($certificate_fields as $key => $value) {
        if ($value !== null) {
            $sql_parts[] = "`{$key}` = :{$key}";
            $params[":{$key}"] = $value;
        }
    }
    $sql = "UPDATE `applicant_certificate` SET " . implode(', ', $sql_parts) . " WHERE `certificate_applicant` = :certificate_applicant";
    $params[':certificate_applicant'] = $user_id;
} else {
    // Insert new record
    $certificate_fields['certificate_applicant'] = $user_id;
    $columns = array_keys($certificate_fields);
    $placeholders = array_map(function($col) { return ":{$col}"; }, $columns);
    
    $sql = "INSERT INTO `applicant_certificate` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    foreach ($certificate_fields as $key => $value) {
        $params[":{$key}"] = $value;
    }
}
