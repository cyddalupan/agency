<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: Restrict in production environments

// Function to generate a daily rotating API key
function generateDailyApiKey($baseString = 'cyd') {
    $date = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
    return hash('sha256', $baseString . $date);
}

// Validate API Key
$expectedApiKey = generateDailyApiKey();
$receivedApiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : '';

if ($receivedApiKey !== $expectedApiKey) {
    http_response_code(401);
    echo json_encode(array('error' => 'Unauthorized: Invalid API Key'));
    exit();
}

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method Not Allowed'));
    exit();
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input: Expect 'sql' and 'params'
if (!isset($data['sql']) || !isset($data['params'])) {
    http_response_code(400);
    echo json_encode(array('error' => 'Missing sql or params in request body'));
    exit();
}

$sql = trim($data['sql']);
$params = $data['params']; // Array of {type: string, value: any}

// --- Security Checks ---

// 1. Whitelist Query Types
$allowed_query_types = array('SELECT', 'INSERT', 'UPDATE', 'DELETE');
$query_type = strtoupper(substr($sql, 0, strpos($sql, ' ')));

if (!in_array($query_type, $allowed_query_types)) {
    http_response_code(403);
    echo json_encode(array('error' => 'Query type not allowed.'));
    exit();
}

// 2. Destructive Pattern Check (basic)
$dangerous_patterns = array(
    'DROP TABLE', 'TRUNCATE TABLE', 'ALTER TABLE', 'CREATE TABLE',
    'UNION ALL', // Can be used for information disclosure
    'LOAD DATA INFILE', 'INTO OUTFILE', // File system access
    '--', '#', // Comments, potential for injection
    '/*', '*/', // Multi-line comments
);

foreach ($dangerous_patterns as $pattern) {
    if (stripos($sql, $pattern) !== false) {
        http_response_code(403);
        echo json_encode(array('error' => 'Dangerous SQL pattern detected.'));
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
    echo json_encode(array('error' => 'Database connection failed: ' . mysqli_connect_error()));
    exit();
}

// --- Prepared Statement Execution ---
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(array('error' => 'Failed to prepare statement: ' . mysqli_error($conn)));
    mysqli_close($conn);
    exit();
}

// Bind parameters
if (!empty($params)) {
    $types = '';
    $bind_names = array('types' => &$types);
    for ($i = 0; $i < count($params); $i++) {
        $types .= $params[$i]['type'];
        $bind_name = 'p' . $i;
        $$bind_name = $params[$i]['value'];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
}

// Execute statement
$execute_success = mysqli_stmt_execute($stmt);

if ($execute_success) {
    if ($query_type === 'SELECT') {
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            $rows = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            echo json_encode(array('data' => $rows));
            mysqli_free_result($result);
        } else {
            http_response_code(500);
            echo json_encode(array('error' => 'Failed to get result set: ' . mysqli_stmt_error($stmt)));
        }
    } else {
        // For INSERT, UPDATE, DELETE, return affected rows or success message
        echo json_encode(array(
            'message' => 'Query executed successfully',
            'affected_rows' => mysqli_stmt_affected_rows($stmt),
            'insert_id' => mysqli_stmt_insert_id($stmt)
        ));
    }
} else {
    // Handle FAILED execution for any query type
    http_response_code(500);
    echo json_encode(array(
        'error' => 'Statement execution failed: ' . mysqli_stmt_error($stmt),
        'sql_state' => mysqli_stmt_sqlstate($stmt),
        'errno' => mysqli_stmt_errno($stmt)
    ));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>