<?php

function handleWorkExperienceUpdate($pdo, $userId, $experienceData)
{
    try {
        // Start a transaction to ensure atomicity
        $pdo->beginTransaction();

        // Delete all existing experiences for the user
        $delete_stmt = $pdo->prepare("DELETE FROM applicant_experiences WHERE experience_applicant = ?");
        $delete_stmt->execute([$userId]);

        // Re-insert new/updated experiences
        if (!empty($experienceData)) {
            foreach ($experienceData as $exp) {
                $sql = "INSERT INTO applicant_experiences (experience_applicant, experience_company, experience_position, experience_from, experience_to, experience_country, experience_salary, reasonOfLeaving) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $userId,
                    isset($exp['experience_company']) ? $exp['experience_company'] : null,
                    isset($exp['experience_position']) ? $exp['experience_position'] : null,
                    isset($exp['experience_from']) ? $exp['experience_from'] : null,
                    isset($exp['experience_to']) ? $exp['experience_to'] : null,
                    isset($exp['experience_country']) ? $exp['experience_country'] : null,
                    isset($exp['experience_salary']) ? $exp['experience_salary'] : null,
                    isset($exp['reasonOfLeaving']) ? $exp['reasonOfLeaving'] : null,
                ]);
            }
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        // In a real application, you would log this error
        error_log("Work experience update failed: " . $e->getMessage());
        return false;
    }
}
