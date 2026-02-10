<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/db.php';

$action = $_GET['action'] ?? '';

// 1. ดึงข้อมูลรายชิ้นสำหรับแก้ไข
if ($action == 'get') {
    $id = $_GET['id'] ?? 0;
    // เพิ่ม LEFT JOIN categories c ON p.category_id = c.id เข้าไป
    $sql = "SELECT p.*, b.brand_name, c.category_name 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

// 2. ดึงรายการทั้งหมดสำหรับ DataTable
if ($action == 'list') {
    try {
        $sql = "SELECT p.*, c.category_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id 
                ORDER BY p.id DESC";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $data]);
    } catch (Exception $e) {
        echo json_encode(['data' => [], 'error' => $e->getMessage()]);
    }
    exit;
}

// 3. เพิ่ม หรือ แก้ไข ข้อมูล
if ($action == 'add') {
    try {
        $product_id  = $_POST['product_id'] ?? '';
        $category_id = $_POST['category_id'] ?? null;
        $brand_name  = $_POST['brand_name'] ?? '';
        $version     = $_POST['version'] ?? '';
        $model       = $_POST['model'] ?? '';
        $price       = $_POST['price'] ?? 0;
        $unit        = $_POST['unit'] ?? '';
        $spec_raw    = $_POST['specifications'] ?? '';

        // จัดการรูปแบบ JSON สำหรับ specifications
        $specifications = json_encode(['detail' => $spec_raw], JSON_UNESCAPED_UNICODE);

        // --- ส่วนที่ 1: จัดการ Brand (ตรวจสอบ/เพิ่มใหม่) ---
        $brand_id = null;
        if (!empty($brand_name)) {
            $st_brand = $pdo->prepare("SELECT id FROM brands WHERE brand_name = ?");
            $st_brand->execute([$brand_name]);
            $brand_data = $st_brand->fetch();
            if ($brand_data) {
                $brand_id = $brand_data['id'];
            } else {
                $ins_brand = $pdo->prepare("INSERT INTO brands (brand_name) VALUES (?)");
                $ins_brand->execute([$brand_name]);
                $brand_id = $pdo->lastInsertId();
            }
        }

        // --- ส่วนที่ 2: จัดการอัปโหลดไฟล์ ---
        $image_path = "";
        $file_path  = "";
        $upload_root = dirname(__DIR__, 2) . "/uploads/";

        if (!is_dir($upload_root . "images/")) mkdir($upload_root . "images/", 0777, true);
        if (!is_dir($upload_root . "docs/")) mkdir($upload_root . "docs/", 0777, true);

        if (!empty($_FILES['p_img']['name'])) {
            $img_name = "img_" . time() . "_" . $_FILES['p_img']['name'];
            if (move_uploaded_file($_FILES['p_img']['tmp_name'], $upload_root . "images/" . $img_name)) {
                $image_path = "uploads/images/" . $img_name;
            }
        }

        if (!empty($_FILES['p_file']['name'])) {
            $pdf_name = "doc_" . time() . "_" . $_FILES['p_file']['name'];
            if (move_uploaded_file($_FILES['p_file']['tmp_name'], $upload_root . "docs/" . $pdf_name)) {
                $file_path = "uploads/docs/" . $pdf_name;
            }
        }

        // --- ส่วนที่ 3: บันทึกลงฐานข้อมูล (Update หรือ Insert) ---
        if (!empty($product_id)) {
            // กรณีแก้ไข (Update)
            $sql = "UPDATE products SET 
                    category_id = ?, brand_id = ?, version = ?, model = ?, 
                    price = ?, unit = ?, specifications = ?" .
                (!empty($image_path) ? ", image_path = ?" : "") .
                (!empty($file_path) ? ", datasheet_path = ?" : "") .
                " WHERE id = ?";

            $params = [$category_id, $brand_id, $version, $model, $price, $unit, $specifications];
            if (!empty($image_path)) $params[] = $image_path;
            if (!empty($file_path)) $params[] = $file_path;
            $params[] = $product_id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลเรียบร้อย']);
        } else {
            // กรณีเพิ่มใหม่ (Insert)
            $sql = "INSERT INTO products (category_id, brand_id, version, model, price, unit, specifications, image_path, datasheet_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$category_id, $brand_id, $version, $model, $price, $unit, $specifications, $image_path, $file_path]);
            echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อย']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// 4. ลบข้อมูล
if ($action == 'delete') {
    $id = $_POST['id'] ?? '';
    $stmt = $pdo->prepare("SELECT image_path, datasheet_path FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();

    if ($file) {
        $root = dirname(__DIR__, 2) . "/";
        if ($file['image_path'] && file_exists($root . $file['image_path'])) @unlink($root . $file['image_path']);
        if ($file['datasheet_path'] && file_exists($root . $file['datasheet_path'])) @unlink($root . $file['datasheet_path']);

        $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $del->execute([$id]);
        echo json_encode(['status' => 'success']);
    }
    exit;
}
