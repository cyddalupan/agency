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
                        applicant_first = ?, applicant_middle = ?, applicant_last = ?, applicant_age = ?,
                        applicant_contacts = ?, applicant_email = ?, fra_remarks = ?, applicant_gender = ?,
                        applicant_nationality = ?, applicant_civil_status = ?, applicant_address = ?,
                        applicant_height = ?, applicant_weight = ?, applicant_religion = ?, applicant_languages = ?,
                        applicant_position_type = :applicant_position_type, currency = :currency, applicant_expected_salary = :applicant_expected_salary,
                        applicant_preferred_country = :applicant_preferred_country, applicant_other_skills = :applicant_other_skills, personalAbilities = :personalAbilities,
                        applicant_birthdate = ?";
            
            $params = [
                $firstName, $middleName, $lastName, $age, $contactNumber, $email, $remarks, $gender,
                $nationality, $civilStatus, $address, $height, $weight, $religion, $languages,
                ':applicant_position_type' => $positionType,
                ':currency' => $currency,
                ':applicant_expected_salary' => $expectedSalary,
                ':applicant_preferred_country' => $preferredCountry,
                ':applicant_other_skills' => $otherSkills,
                ':personalAbilities' => $personalAbilities,
                $birthdate
            ];

            if ($password) {
                $sql .= ", password = ?";
                $params[] = $password;
            }

            if ($resume_path) {
                $sql .= ", applicant_cv = ?";
                $params[] = $resume_path;
            }

            $sql .= " WHERE applicant_id = ?";
            $params[] = $user_id;

            // 4. Execute the query
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo "--- DEBUG: Affected rows ---
";
            var_dump($stmt->rowCount());

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