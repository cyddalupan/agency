<?php

class ApplicantCertificateUpdater
{
    public static function updateApplicantCertificate($pdo, $userId, $data)
    {
        if (empty($data)) {
            return false;
        }

        $stmt = $pdo->prepare("SELECT certificate_id FROM applicant_certificate WHERE certificate_applicant = ?");
        $stmt->execute([$userId]);
        $certificateRecord = $stmt->fetch();

        if ($certificateRecord) {
            // Update existing record
            $sqlParts = [];
            $params = [':certificate_applicant' => $userId];

            foreach ($data as $key => $value) {
                $sqlParts[] = "`{$key}` = :{$key}";
                $params[":{$key}"] = $value;
            }

            $sql = "UPDATE `applicant_certificate` SET " . implode(', ', $sqlParts) . " WHERE `certificate_applicant` = :certificate_applicant";
        } else {
            // Insert new record
            $data['certificate_applicant'] = $userId;
            $columns = array_keys($data);
            $placeholders = array_map(function($col) { return ":{$col}"; }, $columns);
            
            $sql = "INSERT INTO `applicant_certificate` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $params = [];
            foreach ($data as $key => $value) {
                $params[":{$key}"] = $value;
            }
        }

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        if (!$result) {
            // error_log("ApplicantCertificateUpdater::updateApplicantCertificate execute failed for applicant_id: " . $userId . ", SQL: " . $sql . ", Params: " . print_r($params, true));
            // error_log("PDO ErrorInfo: " . print_r($stmt->errorInfo(), true));
        }
        return $result;
    }
}
