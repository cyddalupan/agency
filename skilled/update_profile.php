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
    $page = isset($_POST['page']) ? $_POST['page'] : 'personal_info'; // Default to personal_info

    // Dynamically include all POST data as variables for the handlers to use
    foreach ($_POST as $key => $value) {
        $key = $value;
    }

    // Calculate birthdate from age if age is provided
    $birthdate = isset($age) && $age ? (date('Y') - $age) . "-01-01" : null;

    // Handle file upload centrally
    $resume_path = null;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $file_extension = pathinfo($_FILES["resume"]["name"], PATHINFO_EXTENSION);
        $unique_file_name = "resume_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $unique_file_name;
        $move_function = defined('TESTING_MODE') ? 'copy' : 'move_uploaded_file';
        if ($move_function($_FILES["resume"]["tmp_name"], $target_file)) {
            $resume_path = $target_file;
        }
    }

    $handler_path = __DIR__ . '/update_handlers/' . $page . '.php';

    if (file_exists($handler_path)) {
        $fields = include $handler_path;

        $sql_parts = [];
        $params = [];

        if (is_array($fields)) {
            foreach ($fields as $key => $value) {
                if ($value !== null) {
                    $sql_parts[] = "`{$key}` = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }
        }
        
        if (isset($password) && $password) {
            $sql_parts[] = "`password` = :password";
            $params[":password"] = $password;
        }

        if ($user_id && !empty($sql_parts)) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

                $sql = "UPDATE `applicant` SET " . implode(', ', $sql_parts) . " WHERE `applicant_id` = :applicant_id";
                $params[':applicant_id'] = $user_id;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $_SESSION['message'] = 'Profile updated successfully!';
            } catch (PDOException $e) {
                $_SESSION['error'] = "Database update failed: " . $e->getMessage();
            }
        } else {
            // No fields to update, or user_id not provided
            $_SESSION['message'] = 'No changes were made.';
        }
    } else {
        $_SESSION['error'] = "Invalid update section specified.";
    }

    if (!defined('TESTING_MODE')) {
        header('Location: profile.php?page=' . $page);
        exit;
    }

} else {
    if (!defined('TESTING_MODE')) {
        header('Location: profile.php');
        exit;
    }
}
?>
