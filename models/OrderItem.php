<?php
require_once __DIR__ . '/../core/Database.php';

class OrderItem {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function add($data) {
        $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['order_id'], $data['product_id'], $data['quantity'], $data['price']]);
    }
}


