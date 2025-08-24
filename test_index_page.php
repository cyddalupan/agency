<?php

$url = 'http://localhost';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "HTTP Status 200 OK. Index page loaded successfully.\n";
    // Optionally, you can check for specific content on the page
    // if (strpos($response, 'Welcome to CodeIgniter') !== false) {
    //     echo "Expected content found.\n";
    // } else {
    //     echo "Expected content NOT found.\n";
    // }
} else {
    echo "Failed to load index page. HTTP Status: " . $http_code . "\n";
    echo "Response:\n" . $response . "\n";
}

?>