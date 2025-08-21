<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    if (!defined('TESTING_MODE')) {
        header('Location: login.php');
        exit;
    }
}

require_once(dirname(__DIR__) . '/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Retrieve all form data
    $user_id = isset($_POST['applicant_id']) ? $_POST['applicant_id'] : null;
    $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : null;
    $middleName = isset($_POST['middleName']) ? $_POST['middleName'] : null;
    $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : null;
    $age = isset($_POST['age']) ? $_POST['age'] : null;
    $contactNumber = isset($_POST['contactNumber']) ? $_POST['contactNumber'] : null;
    $otherContactNumber = isset($_POST['otherContactNumber']) ? $_POST['otherContactNumber'] : null;
    $anotherContactNumber = isset($_POST['anotherContactNumber']) ? $_POST['anotherContactNumber'] : null;
    $placeOfBirth = isset($_POST['placeOfBirth']) ? $_POST['placeOfBirth'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;
    $gender = isset($_POST['gender']) ? $_POST['gender'] : null;
    $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : null;
    $civilStatus = isset($_POST['civilStatus']) ? $_POST['civilStatus'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    $height = isset($_POST['height']) ? $_POST['height'] : null;
    $weight = isset($_POST['weight']) ? $_POST['weight'] : null;
    $religion = isset($_POST['religion']) ? $_POST['religion'] : null;
    $languages = isset($_POST['languages']) ? $_POST['languages'] : null;
    $positionType = isset($_POST['positionType']) ? $_POST['positionType'] : null;
    $currency = isset($_POST['currency']) ? $_POST['currency'] : null;
    $expectedSalary = isset($_POST['expectedSalary']) ? $_POST['expectedSalary'] : null;
    $preferredCountry = isset($_POST['preferredCountry']) ? $_POST['preferredCountry'] : null;
    $otherSkills = isset($_POST['otherSkills']) ? $_POST['otherSkills'] : null;
    $personalAbilities = isset($_POST['personalAbilities']) ? $_POST['personalAbilities'] : null;
    $dateApplied = isset($_POST['dateApplied']) ? $_POST['dateApplied'] : null;
    $trainingBranch = isset($_POST['trainingBranch']) ? $_POST['trainingBranch'] : null;
    $source = isset($_POST['source']) ? $_POST['source'] : null;
    $recruitmentAgent = isset($_POST['recruitmentAgent']) ? $_POST['recruitmentAgent'] : null;
    $repatriated = isset($_POST['repatriated']) ? $_POST['repatriated'] : 0;
    $repatriationDate = isset($_POST['repatriationDate']) ? $_POST['repatriationDate'] : null;
    $birthdate = $age ? (date('Y') - $age) . "-01-01" : null;

    // 2. Handle File Upload
    $resume_path = null;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        // Create a unique file name to avoid overwriting
        $file_extension = pathinfo($_FILES["resume"]["name"], PATHINFO_EXTENSION);
        $unique_file_name = "resume_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $unique_file_name;
        
        $move_function = defined('TESTING_MODE') ? 'copy' : 'move_uploaded_file';
        if ($move_function($_FILES["resume"]["tmp_name"], $target_file)) {
            $resume_path = $target_file;
        }
    }

    if ($user_id && $firstName && $lastName) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // 3. Build the full UPDATE query
            $sql = "UPDATE applicant SET 
                        applicant_first = :firstName, applicant_middle = :middleName, applicant_last = :lastName, applicant_age = :age,
                        applicant_contacts = :contactNumber, contacts2 = :otherContactNumber, contacts3 = :anotherContactNumber, applicant_email = :email, fra_remarks = :remarks, applicant_gender = :gender,
                        applicant_nationality = :nationality, applicant_civil_status = :civilStatus, applicant_address = :address, contacts4 = :placeOfBirth,
                        applicant_height = :height, applicant_weight = :weight, applicant_religion = :religion, applicant_languages = :languages,
                        applicant_position_type = :positionType, currency = :currency, applicant_expected_salary = :expectedSalary,
                        applicant_preferred_country = :preferredCountry, applicant_other_skills = :otherSkills, personalAbilities = :personalAbilities,
                        date_applied = :dateApplied, applicant_training_branch = :trainingBranch, applicant_source = :source,
                        applicant_recruitment_agent = :recruitmentAgent, repat_checkbox = :repatriated, repat_date = :repatriationDate,
                        applicant_birthdate = :birthdate";
            
            $params = [
                ':firstName' => $firstName,
                ':middleName' => $middleName,
                ':lastName' => $lastName,
                ':age' => $age,
                ':contactNumber' => $contactNumber,
                ':otherContactNumber' => $otherContactNumber,
                ':anotherContactNumber' => $anotherContactNumber,
                ':email' => $email,
                ':remarks' => $remarks,
                ':gender' => $gender,
                ':nationality' => $nationality,
                ':civilStatus' => $civilStatus,
                ':address' => $address,
                ':placeOfBirth' => $placeOfBirth,
                ':height' => $height,
                ':weight' => $weight,
                ':religion' => $religion,
                ':languages' => $languages,
                ':positionType' => $positionType,
                ':currency' => $currency,
                ':expectedSalary' => $expectedSalary,
                ':preferredCountry' => $preferredCountry,
                ':otherSkills' => $otherSkills,
                ':personalAbilities' => $personalAbilities,
                ':dateApplied' => $dateApplied,
                ':trainingBranch' => $trainingBranch,
                ':source' => $source,
                ':recruitmentAgent' => $recruitmentAgent,
                ':repatriated' => $repatriated,
                ':repatriationDate' => $repatriationDate,
                ':birthdate' => $birthdate
            ];

            if ($password) {
                $sql .= ", password = :password";
                $params[':password'] = $password;
            }

            if ($resume_path) {
                $sql .= ", applicant_cv = :resume_path";
                $params[':resume_path'] = $resume_path;
            }

            $sql .= " WHERE applicant_id = :user_id";
            $params[':user_id'] = $user_id;

            // 4. Execute the query
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $_SESSION['message'] = 'Profile updated successfully!';
            if (!defined('TESTING_MODE')) {
                header('Location: profile.php');
                exit;
            }

        } catch (PDOException $e) {
            $_SESSION['error'] = "Database update failed: " . $e->getMessage();
            if (!defined('TESTING_MODE')) {
                header('Location: profile.php');
                exit;
            }
        }
    } else {
        $_SESSION['error'] = 'Invalid input for profile update.';
        if (!defined('TESTING_MODE')) {
            header('Location: profile.php');
            exit;
        }
    }
} else {
    if (!defined('TESTING_MODE')) {
        header('Location: profile.php');
        exit;
    }
}
?>