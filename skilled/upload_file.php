<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once(dirname(__DIR__) . '/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicant_id = isset($_POST['applicant_id']) ? $_POST['applicant_id'] : null;
    $file_type = isset($_POST['file_type']) ? $_POST['file_type'] : null;

    if (!$applicant_id || !$file_type || !isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters.']);
        exit;
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File upload error: ' . $_FILES['file']['error']]);
        exit;
    }

    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
    $unique_file_name = $file_type . "_" . $applicant_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $unique_file_name;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            $sql = "INSERT INTO applicant_files (file_applicant, file_name, file_type, file_path, file_createdby, file_created) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $applicant_id,
                $_FILES['file']['name'],
                $file_type,
                $target_file,
                $_SESSION['user_id'],
            ]);

            echo json_encode(['success' => true, 'file_path' => $target_file]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to move uploaded file.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>