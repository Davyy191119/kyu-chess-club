<?php
// security.php
class Security {
    // CSRF Protection
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCSRFToken($token) {
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // Rate Limiting
    public static function checkRateLimit($ip, $action, $limit = 5, $timeframe = 300) {
        global $pdo;
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits 
                              WHERE ip = ? AND action = ? 
                              AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$ip, $action, $timeframe]);
        
        if ($stmt->fetchColumn() >= $limit) {
            throw new Exception('Rate limit exceeded');
        }
        
        $stmt = $pdo->prepare("INSERT INTO rate_limits (ip, action) VALUES (?, ?)");
        $stmt->execute([$ip, $action]);
    }

    // Password Validation
    public static function validatePassword($password) {
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters');
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            throw new Exception('Password must contain at least one uppercase letter');
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            throw new Exception('Password must contain at least one lowercase letter');
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            throw new Exception('Password must contain at least one number');
        }
    }

    // Session Security
    public static function regenerateSession() {
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Update session token in database
        $newToken = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE sessions SET session_token = ? WHERE user_id = ?");
        $stmt->execute([$newToken, $_SESSION['user_id']]);
        
        $_SESSION['token'] = $newToken;
    }

    // XSS Protection
    public static function sanitizeOutput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeOutput($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
}

// Activity Logging
function logActivity($userId, $action, $details) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR']]);
}

// Authentication Middleware
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    // Verify session token
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE user_id = ? AND session_token = ? 
                          AND expires_at > NOW()");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['token']]);
    
    if (!$stmt->fetch()) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}
?>