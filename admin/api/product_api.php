<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/db.php';

$action = $_GET['action'] ?? '';

// ฟังก์ชันสำหรับจัดการชื่อไฟล์ (รองรับภาษาไทย และเปลี่ยนช่องว่างเป็น -)
function sanitizeFileName($string)
{
    // แทนที่ช่องว่างด้วย -
    $string = str_replace(' ', '-', $string);
    // ลบตัวอักษรพิเศษที่ไม่เหมาะสมกับชื่อไฟล์ (เหลือไว้แต่ไทย อังกฤษ ตัวเลข และ -)
    return preg_replace('/[^A-Za-z0-9\x{0E00}-\x{0E7F}-]/u', '', $string);
}

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

// // 2. ดึงรายการทั้งหมดสำหรับ DataTable
// if ($action == 'list') {
//     try {
//         $sql = "SELECT p.*, c.category_name, b.brand_name 
//         FROM products p 
//         LEFT JOIN categories c ON p.category_id = c.id 
//         LEFT JOIN brands b ON p.brand_id = b.id 
//         ORDER BY c.category_name ASC, b.brand_name ASC, p.version ASC, p.model ASC";
//         $stmt = $pdo->query($sql);
//         $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
//         echo json_encode(['data' => $data]);
//     } catch (Exception $e) {
//         echo json_encode(['data' => [], 'error' => $e->getMessage()]);
//     }
//     exit;
// }
// 1. ดึงรายการทั้งหมด (ปรับให้ดึงเฉพาะที่ยังไม่ได้ลบ - Soft Delete)
if ($action == 'list') {
    try {
        $sql = "SELECT p.*, c.category_name, b.brand_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN brands b ON p.brand_id = b.id 
                WHERE p.deleted_at IS NULL 
                ORDER BY c.category_name ASC, b.brand_name ASC, p.version ASC, p.model ASC";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $data]);
    } catch (Exception $e) {
        echo json_encode(['data' => [], 'error' => $e->getMessage()]);
    }
    exit;
}
// 2. เพิ่ม หรือ แก้ไข ข้อมูล
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
        function getPureDetail($input)
        {
            $decoded = json_decode($input, true);
            // ถ้า decode แล้วเป็น array และมี key 'detail' แสดงว่ามันถูกหุ้ม JSON มา
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['detail'])) {
                return getPureDetail($decoded['detail']); // ทำซ้ำจนกว่าจะไม่เจอ JSON ซ้อน
            }
            return $input; // คืนค่าข้อความจริงๆ ออกมา
        }
        $clean_spec = getPureDetail($spec_raw);
        $specifications = json_encode(['detail' => $clean_spec], JSON_UNESCAPED_UNICODE);
        // $specifications = json_encode(['detail' => $spec_raw], JSON_UNESCAPED_UNICODE);

        // จัดการ Brand (ตรวจสอบ/เพิ่มใหม่)
        $brand_id = null;
        if (!empty($brand_name)) {
            $st_brand = $pdo->prepare("SELECT id FROM brands WHERE brand_name = ?");
            $st_brand->execute([$brand_name]);
            $brand_data = $st_brand->fetch();
            $brand_id = $brand_data ? $brand_data['id'] : null;
            if (!$brand_id) {
                $ins_brand = $pdo->prepare("INSERT INTO brands (brand_name) VALUES (?)");
                $ins_brand->execute([$brand_name]);
                $brand_id = $pdo->lastInsertId();
            }
        }

        // เตรียมชื่อไฟล์ตามรูปแบบ ยี่ห้อ_รุ่น_model
        $base_name = sanitizeFileName($brand_name) . "_" . sanitizeFileName($version) . "_" . sanitizeFileName($model);

        $image_path = "";
        $file_path  = "";
        $upload_root = dirname(__DIR__, 2) . "/uploads/";
        $root_path = dirname(__DIR__, 2) . "/";

        // หากเป็นการแก้ไข ดึงข้อมูลเก่ามาเพื่อตรวจสอบไฟล์เดิม
        $old_data = null;
        if (!empty($product_id)) {
            $st_old = $pdo->prepare("SELECT image_path, datasheet_path FROM products WHERE id = ?");
            $st_old->execute([$product_id]);
            $old_data = $st_old->fetch();
        }

        // จัดการอัปโหลดรูปภาพ
        if (!empty($_FILES['p_img']['name'])) {
            $img_ext = pathinfo($_FILES['p_img']['name'], PATHINFO_EXTENSION);
            $img_full_name = $base_name . "_" . time() . "." . $img_ext; // ใส่ time กันชื่อซ้ำกรณีอัปโหลดใหม่
            if (move_uploaded_file($_FILES['p_img']['tmp_name'], $upload_root . "images/" . $img_full_name)) {
                $image_path = "uploads/images/" . $img_full_name;
                // ลบไฟล์เก่าถ้ามี
                if ($old_data && $old_data['image_path'] && file_exists($root_path . $old_data['image_path'])) {
                    @unlink($root_path . $old_data['image_path']);
                }
            }
        }

        // จัดการอัปโหลด PDF
        if (!empty($_FILES['p_file']['name'])) {
            $pdf_full_name = $base_name . "_" . time() . ".pdf";
            if (move_uploaded_file($_FILES['p_file']['tmp_name'], $upload_root . "docs/" . $pdf_full_name)) {
                $file_path = "uploads/docs/" . $pdf_full_name;
                // ลบไฟล์เก่าถ้ามี
                if ($old_data && $old_data['datasheet_path'] && file_exists($root_path . $old_data['datasheet_path'])) {
                    @unlink($root_path . $old_data['datasheet_path']);
                }
            }
        }

        if (!empty($product_id)) {
            // โหมด Update
            $sql = "UPDATE products SET category_id = ?, brand_id = ?, version = ?, model = ?, price = ?, unit = ?, specifications = ?" .
                (!empty($image_path) ? ", image_path = ?" : "") .
                (!empty($file_path) ? ", datasheet_path = ?" : "") .
                " WHERE id = ?";
            $params = [$category_id, $brand_id, $version, $model, $price, $unit, $specifications];
            if (!empty($image_path)) $params[] = $image_path;
            if (!empty($file_path)) $params[] = $file_path;
            $params[] = $product_id;
            $pdo->prepare($sql)->execute($params);
            echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลเรียบร้อย']);
        } else {
            // โหมด Insert
            $sql = "INSERT INTO products (category_id, brand_id, version, model, price, unit, specifications, image_path, datasheet_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$category_id, $brand_id, $version, $model, $price, $unit, $specifications, $image_path, $file_path]);
            echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อย']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// 3. การลบข้อมูล (Soft Delete)
if ($action == 'delete') {
    $id = $_POST['id'] ?? '';
    if ($id) {
        $stmt = $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
    }
    exit;
}
// เพิ่ม action ใหม่สำหรับกู้คืน
if ($action == 'restore') {
    $id = $_POST['id'] ?? '';
    $stmt = $pdo->prepare("UPDATE products SET deleted_at = NULL WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success']);
    exit;
}
// ปรับ action list_all สำหรับ Admin
if ($action == 'list_all') {
    $sql = "SELECT p.*, c.category_name, b.brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            ORDER BY c.category_name ASC, b.brand_name ASC, p.version ASC, p.model ASC";
    $stmt = $pdo->query($sql);
    echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}
