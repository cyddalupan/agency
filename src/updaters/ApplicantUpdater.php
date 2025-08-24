<?php

class ApplicantUpdater
{
    public static function updateApplicant($pdo, $userId, $data)
    {
        error_log("ApplicantUpdater::updateApplicant called");
        if (empty($data)) {
            return false;
        }

        $sqlParts = [];
        $params = [':applicant_id' => $userId];

        foreach ($data as $key => $value) {
            $sqlParts[] = "`{$key}` = :{$key}";
            $params[":{$key}"] = $value;
        }

        $sql = "UPDATE `applicant` SET " . implode(', ', $sqlParts) . " WHERE `applicant_id` = :applicant_id";

        error_log("ProfileUpdater SQL: " . $sql);
        error_log("ProfileUpdater Params: " . print_r($params, true));

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        error_log("ProfileUpdater rowCount: " . $stmt->rowCount());
        if (!$result) {
            error_log("ProfileUpdater::updateApplicant execute failed for applicant_id: " . $userId . ", SQL: " . $sql . ", Params: " . print_r($params, true));
            error_log("PDO ErrorInfo: " . print_r($stmt->errorInfo(), true));
            error_log("SQLSTATE: " . $stmt->errorCode());
        }
        return $result;
    }
}