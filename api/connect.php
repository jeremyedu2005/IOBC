<?php
/**
 * api/connect.php - Send connection request to another user
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Security.class.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check CSRF token
$headers = getallheaders();
$csrf = $headers['X-CSRF-Token'] ?? $_POST['csrf_token'] ?? null;
if (!$csrf || !Security::validateCSRFToken($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token invalid']);
    exit;
}

// Rate limiting
if (!Security::checkRateLimit($_SESSION['user_id'], 'connect_request')) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Rate limit exceeded']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$target_user_id = (int)($data['user_id'] ?? 0);
$current_user = (int)$_SESSION['user_id'];

if ($target_user_id <= 0 || $target_user_id == $current_user) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if connection already exists
    $stmt = $pdo->prepare("
        SELECT id FROM connections 
        WHERE (requester_id = :req AND receiver_id = :rec) 
        OR (requester_id = :rec AND receiver_id = :req)
        LIMIT 1
    ");
    $stmt->execute([':req' => $current_user, ':rec' => $target_user_id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Connection already exists or pending']);
        exit;
    }

    // Create connection request
    $stmt = $pdo->prepare("
        INSERT INTO connections (requester_id, receiver_id, status, created_at) 
        VALUES (:req, :rec, 'pending', NOW())
    ");
    $stmt->execute([':req' => $current_user, ':rec' => $target_user_id]);

    // Log security event
    Security::logSecurityEvent('connection_request', "User $current_user sent connection request to $target_user_id");

    echo json_encode(['success' => true, 'message' => 'Connection request sent']);
} catch (Exception $e) {
    error_log("Connection error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
