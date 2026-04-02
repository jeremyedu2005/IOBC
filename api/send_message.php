<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Check CSRF token
require_once(__DIR__ . '/../inc/poo.inc.php');
if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF token invalid']);
    exit;
}

// Check rate limiting
if (!Security::checkRateLimit($_SERVER['REMOTE_ADDR'])) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Too many requests']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['contact_id']) || empty($input['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$contact_id = (int)$input['contact_id'];
$content = $input['content'];

// Validate input
if (!Security::validateInt($contact_id) || $contact_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid contact ID']);
    exit;
}

if (!Security::validateText($content, 1, 5000)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Message too long or too short']);
    exit;
}

// Sanitize content
$content = Security::sanitizeText($content);

try {
    require_once(__DIR__ . '/../config.php');
    
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if contact exists
    $stmt = $db->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $contact_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Contact not found']);
        exit;
    }
    
    // Insert message
    $stmt = $db->prepare('
        INSERT INTO messages (sender_id, receiver_id, content, created_at)
        VALUES (:sender_id, :receiver_id, :content, NOW())
    ');
    
    $stmt->execute([
        ':sender_id' => $_SESSION['user_id'],
        ':receiver_id' => $contact_id,
        ':content' => $content
    ]);
    
    // Log security event
    Security::logSecurityEvent('message_sent', $_SESSION['user_id'], $_SERVER['REMOTE_ADDR']);
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    
} catch (PDOException $e) {
    error_log('Database error in send_message.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
} catch (Exception $e) {
    error_log('Error in send_message.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
