<?php
require_once __DIR__ . '/../core/Database.php';

class CartItem {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($userId, $productId) {
        $stmt = $this->db->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($userId, $productId) {
        $stmt = $this->db->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$userId, $productId]);
    }

    public function updateQuantity($userId, $productId, $quantity) {
        $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $userId, $productId]);
    }

    public function remove($userId, $productId) {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
    }

    public function clear($userId) {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}
