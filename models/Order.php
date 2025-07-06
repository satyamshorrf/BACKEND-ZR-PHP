<?php
require_once __DIR__ . '/../core/Database.php';

class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO orders (user_id, total_amount, stripe_session_id) VALUES (?, ?, ?)");
        $stmt->execute([$data['user_id'], $data['total_amount'], $data['stripe_session_id']]);
        return $this->db->lastInsertId();
    }
}
