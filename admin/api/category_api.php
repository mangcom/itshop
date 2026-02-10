<?php
header('Content-Type: application/json');
require_once '../../db.php'; // ปรับ Path ตามโครงสร้างไฟล์ของคุณ

$action = $_GET['action'] ?? '';

if ($action == 'list') {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $data]);
} elseif ($action == 'add') {
    $name = $_POST['category_name'] ?? '';
    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
        if ($stmt->execute([$name])) {
            echo json_encode(['status' => 'success']);
        }
    }
} elseif ($action == 'delete') {
    $id = $_POST['id'] ?? '';
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success']);
    }
}
