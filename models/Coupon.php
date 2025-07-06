<?php
require_once __DIR__ . '/../core/Database.php';

class Coupon {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findActiveByCodeAndUser($code, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE code = ? AND user_id = ? AND is_active = 1");
        $stmt->execute([$code, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deactivate($code, $userId) {
        $stmt = $this->db->prepare("UPDATE coupons SET is_active = 0 WHERE code = ? AND user_id = ?");
        $stmt->execute([$code, $userId]);
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO coupons (code, discount_percentage, expiration_date, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['code'], $data['discount_percentage'], $data['expiration_date'], $data['user_id']]);
    }

    public function deleteByUser($userId) {
        $stmt = $this->db->prepare("DELETE FROM coupons WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}
