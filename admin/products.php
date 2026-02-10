<?php require_once 'auth_check.php'; ?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>
<?php
require_once dirname(__DIR__) . '/db.php';
$cats = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands")->fetchAll();
?>

<div id="page-content-wrapper" class="w-100">
    <nav class="navbar navbar-light bg-white border-bottom px-4 py-3">
        <span class="navbar-brand mb-0 h1">รายการอุปกรณ์คอมพิวเตอร์</span>
    </nav>
    <div class="container-fluid p-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#productModal">
                    <i class="bi bi-plus"></i> เพิ่มสินค้าใหม่
                </button>
                <table id="productTable" class="table table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">รูป</th>
                            <th>ยี่ห้อ</th>
                            <th>รุ่น (Series)</th>
                            <th>Model</th>
                            <th>หมวดหมู่</th>
                            <th>ราคา</th>
                            <th width="10%">สถานะ</th>
                            <th width="15%">จัดการ</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="addProductForm" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="product_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>รายละเอียดสินค้า</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>หมวดหมู่</label>
                            <select name="category_id" class="form-select">
                                <?php foreach ($cats as $c) echo "<option value='{$c['id']}'>{$c['category_name']}</option>"; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3 position-relative">
                            <label>ยี่ห้อ (Brand)</label>
                            <input type="text" name="brand_name" id="brand_input" class="form-control" placeholder="พิมพ์ชื่อยี่ห้อ..." autocomplete="off" required>
                            <div id="brand_list" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; display: none;"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>รุ่น (Version/Series)</label>
                            <input type="text" name="version" class="form-control" placeholder="เช่น ROG Strix, Gaming Pro" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>โมเดล (Model Code)</label>
                            <input type="text" name="model" class="form-control" placeholder="เช่น G513RC-HN002W" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>ราคา</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>หน่วยนับ</label>
                            <input type="text" name="unit" class="form-control" placeholder="ชิ้น/ชุด">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>คุณสมบัติเพิ่มเติม</label>
                            <textarea name="specifications" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label>รูปภาพสินค้า</label>
                            <input type="file" name="p_img" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label>ไฟล์ Datasheet (PDF)</label>
                            <input type="file" name="p_file" class="form-control" accept="application/pdf">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>