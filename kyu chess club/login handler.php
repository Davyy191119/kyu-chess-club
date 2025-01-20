<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Generate session token
            $token = bin2hex(random_bytes(32));
            
            // Store session
            $stmt = $pdo->prepare("INSERT INTO sessions (user_id, session_token, expires_at) 
                                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))");
            $stmt->execute([$user['id'], $token]);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['token'] = $token;
            
            // Update last login
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Log successful login
            logActivity($user['id'], 'login', 'Successful login');
            
            header('Location: dashboard.php');
            exit;
        } else {
            throw new Exception('Invalid credentials');
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Login failed']);
    }
}
?>