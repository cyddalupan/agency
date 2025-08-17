<?php

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // No password
define('DB_NAME', 'iwebphil_everlast');

// Create a new PDO instance
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// For legacy mysql_connect functions
$con = mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME, $con);

// For mysqli_connect functions
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

?>