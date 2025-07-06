<?php
require_once __DIR__ . '/../core/Database.php';

class Event {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO events (date, time, address) VALUES (?, ?, ?)");
        return $stmt->execute([$data['date'], $data['time'], $data['address']]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM events ORDER BY date ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE events SET date = ?, time = ?, address = ? WHERE id = ?");
        return $stmt->execute([$data['date'], $data['time'], $data['address'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
