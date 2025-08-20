<?php
// Conditionally start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(dirname(__DIR__) . '/config.php');

$error_message = ''; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            $sql = "SELECT applicant_id, applicant_first, applicant_last, applicant_email, password FROM applicant WHERE applicant_email = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && $password == $user['password']) { // Plain text password check as per register.php
                $_SESSION['user_id'] = $user['applicant_id'];
                $_SESSION['user_email'] = $user['applicant_email'];
                $_SESSION['user_name'] = $user['applicant_first'] . ' ' . $user['applicant_last'];

                if (!defined('TESTING_MODE')) {
                    header("Location: profile.php");
                    exit;
                }
                // In TESTING_MODE, we don't redirect or output HTML.
                // The test script will check $_SESSION directly.
            } else {
                $error_message = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
