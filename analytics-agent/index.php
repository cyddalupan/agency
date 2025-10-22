<?php
// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'];

// Define the base path for the Angular application
$basePath = '/analytics-agent/';

// Check if the request is for an asset (CSS, JS, images, etc.)
// This is a simple check, you might need a more robust one depending on your asset structure
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot)(\?.*)?$/i', $requestUri)) {
    // If it's an asset, let Apache try to serve it directly
    // This assumes Apache's default handler will find the asset if it exists
    return false; // This tells PHP-FPM to pass the request back to Apache
}

// Otherwise, serve the Angular app's index.html
// Read the content of the Angular index.html file
$angularIndexHtmlPath = __DIR__ . '/index.html'; // __DIR__ refers to the current directory (analytics-agent/)

if (file_exists($angularIndexHtmlPath)) {
    // Set the correct content type
    header('Content-Type: text/html');
    // Output the content of index.html
    echo file_get_contents($angularIndexHtmlPath);
} else {
    // Fallback if index.html is not found (shouldn't happen if build is correct)
    header('HTTP/1.0 404 Not Found');
    echo 'Angular index.html not found.';
}
?>