<?php
// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'helloworld_user');
define('DB_PASS', 'p@ssw0rd_H3ll0W0rld!');
define('DB_NAME', 'iwebphil_everlast');

// Create a new PDO instance
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection successful!";
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
