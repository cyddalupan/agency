<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log debugging information to a file
function log_debug($message) {
    file_put_contents(__DIR__ . '/query_executor_debug.log', date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
}

log_debug('Script started.');
log_debug('$_SERVER contents: ' . print_r($_SERVER, true));

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: Restrict in production environments

// Function to generate a daily rotating API key
function generateDailyApiKey($baseString = 'cyd') {
    $date = (new DateTime())->format('Y-m-d');
    return hash('sha256', $baseString . $date);
}

// Validate API Key
$expectedApiKey = generateDailyApiKey();
$receivedApiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : '';

log_debug('Expected API Key: ' . $expectedApiKey);
log_debug('Received API Key: ' . $receivedApiKey);

if ($receivedApiKey !== $expectedApiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Invalid API Key']);
    exit();
}

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input: Expect 'sql' and 'params'
if (!isset($data['sql']) || !isset($data['params'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing sql or params in request body']);
    exit();
}

$sql = trim($data['sql']);
$params = $data['params']; // Array of {type: string, value: any}

// --- Security Checks ---

// 1. Whitelist Query Types
$allowed_query_types = ['SELECT', 'INSERT', 'UPDATE', 'DELETE'];
$query_type = strtoupper(substr($sql, 0, strpos($sql, ' ')));

if (!in_array($query_type, $allowed_query_types)) {
    http_response_code(403);
    echo json_encode(['error' => 'Query type not allowed.']);
    exit();
}

// 2. Destructive Pattern Check (basic)
$dangerous_patterns = [
    'DROP TABLE', 'TRUNCATE TABLE', 'ALTER TABLE', 'CREATE TABLE',
    'UNION ALL', // Can be used for information disclosure
    'LOAD DATA INFILE', 'INTO OUTFILE', // File system access
    '--', '#', // Comments, potential for injection
    '/*', '*/', // Multi-line comments
];

foreach ($dangerous_patterns as $pattern) {
    if (stripos($sql, $pattern) !== false) {
        http_response_code(403);
        echo json_encode(['error' => 'Dangerous SQL pattern detected.']);
        exit();
    }
}

// Include database configuration
require_once __DIR__ . '/../config.php';

// Establish mysqli connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit();
}

// --- Prepared Statement Execution ---
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement: ' . mysqli_error($conn)]);
    mysqli_close($conn);
    exit();
}

// Bind parameters
if (!empty($params)) {
    $types = '';
    $bind_values = [];
    foreach ($params as $param) {
        $types .= $param['type'];
        $bind_values[] = &$param['value']; // Pass by reference for call_user_func_array
    }
    array_unshift($bind_values, $types);
    call_user_func_array([$stmt, 'bind_param'], $bind_values);
}

// Execute statement
$execute_success = mysqli_stmt_execute($stmt);

if ($execute_success === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Statement execution failed: ' . mysqli_stmt_error($stmt)]);
} else {
    if ($query_type === 'SELECT') {
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            echo json_encode(['data' => $rows]);
            mysqli_free_result($result);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to get result set: ' . mysqli_stmt_error($stmt)]);
        }
    } else {
        // For INSERT, UPDATE, DELETE, return affected rows or success message
        echo json_encode([
            'message' => 'Query executed successfully',
            'affected_rows' => mysqli_stmt_affected_rows($stmt),
            'insert_id' => mysqli_stmt_insert_id($stmt) // Will be 0 for UPDATE/DELETE
        ]);
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>