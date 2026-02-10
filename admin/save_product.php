<?php
require_once 'auth_check.php';
require_once 'db.php';

$upload_dir = "../uploads/";
$image_path = "";
$file_path = "";

// จัดการรูปภาพ
if ($_FILES['p_img']['error'] == 0) {
    $img_name = time() . "_" . $_FILES['p_img']['name'];
    move_uploaded_file($_FILES['p_img']['tmp_name'], $upload_dir . "images/" . $img_name);
    $image_path = "uploads/images/" . $img_name;
}

// จัดการ PDF
if ($_FILES['p_file']['error'] == 0) {
    $pdf_name = time() . "_" . $_FILES['p_file']['name'];
    move_uploaded_file($_FILES['p_file']['tmp_name'], $upload_dir . "docs/" . $pdf_name);
    $file_path = "uploads/docs/" . $pdf_name;
}

$sql = "INSERT INTO products (category_id, brand_id, model, price, unit, specifications, image_path, datasheet_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    $_POST['category_id'],
    $_POST['brand_id'],
    $_POST['model'],
    $_POST['price'],
    $_POST['unit'],
    $_POST['spec'],
    $image_path,
    $file_path
]);

header("Location: products.php?success=1");
