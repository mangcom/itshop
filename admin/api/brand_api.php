<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/db.php';

$term = $_GET['term'] ?? '';

if (strlen($term) >= 3) {
    // ค้นหายี่ห้อที่คล้ายกับที่พิมพ์
    $stmt = $pdo->prepare("SELECT brand_name FROM brands WHERE brand_name LIKE ? LIMIT 5");
    $stmt->execute(["%$term%"]);
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($brands);
} else {
    echo json_encode([]);
}
