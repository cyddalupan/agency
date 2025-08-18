<?php

// Enable full error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test (PDO Method)</h1>";
echo "<p>This script will test the database connection using the PDO library and the credentials from <strong>config.php</strong>.</p>";

// --- 1. Load Configuration ---
echo "<h2>Step 1: Loading config.php</h2>";
require_once('config.php');
echo "<p style='color:green;'>Successfully included config.php.</p>";

// --- 2. Display Loaded Credentials ---
echo "<h2>Step 2: Displaying Credentials</h2>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . DB_HOST . "</li>";
echo "<li><strong>Database:</strong> " . DB_NAME . "</li>";
echo "<li><strong>User:</strong> " . DB_USER . "</li>";
echo "</ul>";

// --- 3. Attempt Connection ---
echo "<h2>Step 3: Connecting to MySQL using PDO...</h2>";

try {
    // Construct the DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    
    // PDO connection options
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Create a new PDO instance
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    echo "<p style='color:green;'><strong>Connection Successful!</strong></p>";
    echo "<p>The script was able to connect to the database '<strong>" . DB_NAME . "</strong>' successfully using PDO.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'><strong>Connection Failed.</strong></p>";
    echo "<p><strong>Error Message:</strong> " . $e->getMessage() . "</p>";
}

?>