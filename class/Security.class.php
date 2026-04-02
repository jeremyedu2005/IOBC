<?php
/**
 * Security utilities and functions
 * Comprehensive security layer for the application
 */

class Security
{
    /**
     * Generate CSRF token and store in session
     */
    public static function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token from POST request
     */
    public static function verifyCSRFToken($token = null)
    {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }

        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data, $type = 'text')
    {
        if (is_array($data)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $data);
        }

        $data = trim($data);

        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($data, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT);
            case 'text':
            default:
                return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Validate input data
     */
    public static function validateInput($data, $type = 'text', $required = true)
    {
        if ($required && empty($data)) {
            return false;
        }

        if (!$required && empty($data)) {
            return true;
        }

        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return filter_var($data, FILTER_VALIDATE_URL) !== false;
            case 'int':
                return filter_var($data, FILTER_VALIDATE_INT) !== false;
            case 'ipv4':
                return filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
            case 'username':
                // Alphanumeric, underscores, hyphens, 3-20 chars
                return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $data) === 1;
            case 'password':
                // At least 8 chars, 1 uppercase, 1 lowercase, 1 number
                return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $data) === 1;
            case 'text':
            default:
                return is_string($data) && strlen($data) > 0;
        }
    }

    /**
     * Hash password securely
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password against hash
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Sanitize file uploads
     */
    public static function validateFileUpload($file, $allowedMimes = [], $maxSize = 5242880)
    {
        // Check if file was actually uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'No file uploaded'];
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File too large'];
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!empty($allowedMimes) && !in_array($mime, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Invalid file type'];
        }

        return ['valid' => true, 'mime' => $mime];
    }

    /**
     * Escape output for HTML context
     */
    public static function escapeHTML($data)
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape output for JavaScript context
     */
    public static function escapeJS($data)
    {
        return json_encode($data);
    }

    /**
     * Escape output for URL context
     */
    public static function escapeURL($data)
    {
        return urlencode($data);
    }

    /**
     * Rate limiter - simple in-memory version
     * For production, use Redis
     */
    public static function checkRateLimit($identifier, $limit = 60, $window = 3600)
    {
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }

        $key = md5($identifier);
        $now = time();

        // Clean old entries
        if (isset($_SESSION['rate_limit'][$key])) {
            $_SESSION['rate_limit'][$key] = array_filter(
                $_SESSION['rate_limit'][$key],
                function($timestamp) use ($now, $window) {
                    return $timestamp > ($now - $window);
                }
            );
        }

        if (!isset($_SESSION['rate_limit'][$key])) {
            $_SESSION['rate_limit'][$key] = [];
        }

        if (count($_SESSION['rate_limit'][$key]) >= $limit) {
            return false; // Rate limit exceeded
        }

        $_SESSION['rate_limit'][$key][] = $now;
        return true;
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $data = [])
    {
        $logFile = __DIR__ . '/../logs/security.log';

        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => self::getClientIP(),
            'user_id' => $_SESSION['user_id'] ?? 'anonymous',
            'data' => $data
        ];

        file_put_contents(
            $logFile,
            json_encode($entry) . "\n",
            FILE_APPEND
        );
    }

    /**
     * Get client IP address safely
     */
    public static function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Require user authentication
     */
    public static function requireAuth($redirectUrl = 'index.php?login')
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Require role
     */
    public static function requireRole($role, $userRole = null)
    {
        if ($userRole !== $role) {
            http_response_code(403);
            exit('Access Denied');
        }
    }

    /**
     * Set secure session configuration
     */
    public static function setupSecureSession()
    {
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.cookie_lifetime', 3600);
        ini_set('session.gc_maxlifetime', 3600);
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regenerate'])) {
            $_SESSION['last_regenerate'] = time();
        } elseif (time() - $_SESSION['last_regenerate'] > 3600) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();
        }
    }

    /**
     * Set security headers
     */
    public static function setSecurityHeaders()
    {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Enable XSS protection (for older browsers)
        header('X-XSS-Protection: 1; mode=block');

        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Permissions policy (formerly Feature-Policy)
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com");

        // HSTS (if on HTTPS)
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

?>
