<?php
// Function to load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove quotes if present
        if (preg_match('/^"(.+)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.+)'$/", $value, $matches)) {
            $value = $matches[1];
        }

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env file from the project root
loadEnv(__DIR__ . '/../.env');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: Restrict in production environments

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['context']) || !isset($data['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing context or message in request body']);
    exit();
}

$context = $data['context'];
$message = $data['message'];

// OpenAI API configuration
// IMPORTANT: Ensure OPENAI_API_KEY is set in your .env file or environment variables.
$openaiApiKey = getenv('OPENAI_API_KEY');
if (!$openaiApiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'OpenAI API key not configured.']);
    exit();
}

$openaiEndpoint = 'https://api.openai.com/v1/chat/completions';
$model = 'gpt-5-mini'; // As specified in CHATLOGIC.md (Note: 'gpt-5-mini' is a placeholder model name)

// Construct the messages array for OpenAI
$messages = [];

// Add context if it's a string or an array of messages
if (is_string($context)) {
    // If context is a string, treat it as a system message
    $messages[] = ['role' => 'system', 'content' => $context];
} elseif (is_array($context)) {
    // If context is an array, assume it's an array of message objects
    // e.g., [{'role': 'system', 'content': 'You are a helpful assistant.'}, {'role': 'user', 'content': 'Hello'}]
    $messages = array_merge($messages, $context);
}

// Add the user's message
$messages[] = ['role' => 'user', 'content' => $message];

$postFields = json_encode([
    'model' => $model,
    'messages' => $messages,
]);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $openaiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $openaiApiKey,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL Error: ' . $curlError]);
    exit();
}

// Forward the OpenAI response directly
http_response_code($httpCode);
echo $response;

?>