<?php
require_once(__DIR__ . '/config.php');

// Create a new PDO instance
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection successful!";
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
