<?php
/**
 * api/follow.php - Follow/Unfollow a user
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
if (!Security::checkRateLimit($_SESSION['user_id'], 'follow_action')) {
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

    // Check if already following
    $stmt = $pdo->prepare("
        SELECT id FROM follows 
        WHERE follower_id = :follower AND following_id = :following
        LIMIT 1
    ");
    $stmt->execute([':follower' => $current_user, ':following' => $target_user_id]);
    
    if ($stmt->fetch()) {
        // Already following - unfollow
        $stmt = $pdo->prepare("
            DELETE FROM follows 
            WHERE follower_id = :follower AND following_id = :following
        ");
        $stmt->execute([':follower' => $current_user, ':following' => $target_user_id]);
        $message = 'Unfollowed';
    } else {
        // Not following - follow
        $stmt = $pdo->prepare("
            INSERT INTO follows (follower_id, following_id, created_at) 
            VALUES (:follower, :following, NOW())
        ");
        $stmt->execute([':follower' => $current_user, ':following' => $target_user_id]);
        $message = 'Now following';
    }

    // Log security event
    Security::logSecurityEvent('follow_action', "User $current_user followed/unfollowed $target_user_id");

    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    error_log("Follow error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
