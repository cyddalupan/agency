<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone to match the main application
date_default_timezone_set('Asia/Manila');

// 1. Database Connection
require_once(dirname(__DIR__) . '/config.php');

function run_test() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = ''" // Disable strict mode
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        echo "Connection successful!\n";

        // Simple INSERT statement with minimal required fields
        $sql = "INSERT INTO applicant (applicant_first, applicant_last, password, applicant_date_applied) VALUES ('TestFirst', 'TestLast', 'TestPass', NOW())";
        $pdo->exec($sql);
        echo "Simple insert successful!\n";

    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

run_test();
