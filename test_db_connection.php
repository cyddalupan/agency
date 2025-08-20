<?php

require_once(__DIR__ . '/config.php');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = ''"
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "Database connection successful!\n";

    // Optional: Perform a simple query to verify data access
    $stmt = $pdo->query("SELECT COUNT(*) FROM applicant");
    $count = $stmt->fetchColumn();
    echo "Number of applicants: " . $count . "\n";

} catch(PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

?>