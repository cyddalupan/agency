<?php

class ApplicantEducationUpdater
{
    public static function updateApplicantEducation($pdo, $userId, $data)
    {
        // Check if a record for this applicant_id already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM applicant_education WHERE education_applicant = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            // Update existing record
            $sqlParts = [];
            $params = ['education_applicant' => $userId];

            foreach ($data as $key => $value) {
                $sqlParts[] = "`{$key}` = :{$key}";
                $params[":{$key}"] = $value;
            }

            $sql = "UPDATE `applicant_education` SET " . implode(', ', $sqlParts) . " WHERE `education_applicant` = :education_applicant";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($params);
        } else {
            // Insert new record
            $data['education_applicant'] = $userId; // Add applicant ID to data for insert
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));

            $sql = "INSERT INTO `applicant_education` ({$columns}) VALUES ({$placeholders})";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($data);
        }
    }
}