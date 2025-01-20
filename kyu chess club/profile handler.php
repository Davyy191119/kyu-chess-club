<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

class ProfileManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getUserProfile($userId) {
        $stmt = $this->pdo->prepare("SELECT username, email, fide_id, phone, rating 
                                    FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function updateProfile($userId, $data) {
        try {
            $this->pdo->beginTransaction();
            
            // Store old values for audit
            $oldData = $this->getUserProfile($userId);
            
            $allowedFields = ['phone', 'fide_id', 'rating'];
            $updates = [];
            $params = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field]) && $data[$field] !== $oldData[$field]) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                    
                    // Log the change
                    $stmt = $this->pdo->prepare("INSERT INTO profile_updates 
                        (user_id, field_changed, old_value, new_value) 
                        VALUES (?, ?, ?, ?)");
                    $stmt->execute([$userId, $field, $oldData[$field], $data[$field]]);
                }
            }
            
            if (!empty($updates)) {
                $params[] = $userId;
                $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function getProfileHistory($userId) {
        $stmt = $this->pdo->prepare("SELECT field_changed, old_value, new_value, 
                                    update_date FROM profile_updates 
                                    WHERE user_id = ? ORDER BY update_date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
?>