<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: For development, allows requests from any origin. Restrict this in production.

echo json_encode([
    'message' => 'Hello from PHP!',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>