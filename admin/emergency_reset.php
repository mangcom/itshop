<?php
// ระบุ Path ไปยัง db.php ให้ถูกต้อง (ตามที่เราคุยกันเรื่อง dirname)
require_once dirname(__DIR__) . '/db.php';

// กำหนด Username และ Password ใหม่ที่ต้องการ
$target_user = 'admin';
$new_password = 'password1234'; // <--- เปลี่ยนรหัสผ่านใหม่ที่นี่

// ทำการ Hash รหัสผ่าน
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // ตรวจสอบว่ามี User นี้อยู่จริงไหม
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$target_user]);
    $admin = $stmt->fetch();

    if ($admin) {
        // ทำการ Update รหัสผ่านใหม่
        $update = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $update->execute([$hashed_password, $admin['id']]);

        echo "<h2 style='color:green;'>สำเร็จ!</h2>";
        echo "<p>รหัสผ่านของ <b>$target_user</b> ถูกเปลี่ยนเป็น: <b>$new_password</b> เรียบร้อยแล้ว</p>";
        echo "<a href='login.php'>คลิกเพื่อไปหน้า Login</a>";
    } else {
        echo "<h2 style='color:red;'>ไม่พบชื่อผู้ใช้!</h2>";
        echo "<p>ไม่พบ Username: <b>$target_user</b> ในฐานข้อมูล</p>";
    }
} catch (Exception $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
