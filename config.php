<?php

// Site URL
define('SITE_URL', 'https://upgraded-engine-rjg6pprp4vf54xq-8080.app.github.dev/');

// Database credentials
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'database_name');

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