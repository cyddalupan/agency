<?php
// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'];

// Define the base path for the Angular application
$basePath = '/analytics-agent/';

// Get the path relative to the base path
$relativePath = substr($requestUri, strlen($basePath));

// Construct the full physical path to the requested file
$physicalFilePath = __DIR__ . '/' . $relativePath;

// --- DEBUGGING START ---
error_log("Request URI: " . $requestUri);
error_log("Base Path: " . $basePath);
error_log("Relative Path: " . $relativePath);
error_log("Physical File Path: " . $physicalFilePath);
error_log("File Exists: " . (file_exists($physicalFilePath) ? 'true' : 'false'));
// --- DEBUGGING END ---

// Check if the requested file is a static asset and exists
if (file_exists($physicalFilePath)) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $physicalFilePath);
    finfo_close($finfo);

    if ($mimeType) {
        header('Content-Type: ' . $mimeType);
        readfile($physicalFilePath);
        exit; // Stop further execution after serving the file
    }
}

// If it's not an existing static asset, serve the Angular app's index.html
$angularIndexHtmlPath = __DIR__ . '/index.html';

if (file_exists($angularIndexHtmlPath)) {
    header('Content-Type: text/html');
    echo file_get_contents($angularIndexHtmlPath);
} else {
    header('HTTP/1.0 404 Not Found');
    echo 'Angular index.html not found.';
}
?>