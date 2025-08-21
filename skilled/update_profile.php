<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once(dirname(__DIR__) . '/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['applicant_id'] ?? null;
    $firstName = $_POST['firstName'] ?? null;
    $middleName = $_POST['middleName'] ?? null;
    $lastName = $_POST['lastName'] ?? null;

    if ($user_id && $firstName && $lastName) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            $stmt = $pdo->prepare("UPDATE applicant SET applicant_first = ?, applicant_middle = ?, applicant_last = ? WHERE applicant_id = ?");
            $stmt->execute([$firstName, $middleName, $lastName, $user_id]);

            $_SESSION['message'] = 'Profile updated successfully!';
            header('Location: profile.php');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Database update failed: " . $e->getMessage();
            header('Location: profile.php');
            exit;
        }
    } else {
        $_SESSION['error'] = 'Invalid input for profile update.';
        header('Location: profile.php');
        exit;
    }
} else {
    header('Location: profile.php');
    exit;
}
?>