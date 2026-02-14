<?php require_once 'db.php';
// ดึงข้อมูลหมวดหมู่ทั้งหมดสำหรับ Select Option
$cats = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>IT Shop - รายการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body class="bg-light">
    <div class="container-fluid px-5 mt-4">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
            <div class="container-fluid px-3">
                <a class="navbar-brand fw-bold" href="index.php">IT SHOP</a>
                <a href="cart.php" class="btn btn-primary position-relative">
                    <i class="bi bi-cart3"></i> ตะกร้าของฉัน
                    <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
                </a>
            </div>
        </nav>


        <div class="card border-0 shadow-sm p-4">
            <div class="row mb-4 align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">รายการสินค้า</h4>
                </div>
                <div class="col-md-3 ms-auto text-end">
                    <label class="form-label small">กรองตามประเภท:</label>
                    <select id="filter-category" class="form-select">
                        <option value="">ดูทุกประเภท</option>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['category_name']); ?>">
                                <?php echo $c['category_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <table id="customerProductTable" class="table table-hover w-100">
                <thead class="table-light">
                    <tr>
                        <th>รูปภาพ</th>
                        <th>ประเภท</th>
                        <th>ยี่ห้อ</th>
                        <th>รุ่น/Model</th>
                        <th>ราคา/หน่วย</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="productDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0">
                <div id="modal-content-area"></div>
            </div>
        </div>
    </div>

    <div id="image-preview-popup" class="shadow rounded" style="display: none; position: absolute; z-index: 1055; background: #fff; padding: 5px; border: 1px solid rgba(0,0,0,0.1);">
        <img src="" style="max-width: 300px; max-height: 300px; object-fit: contain; display: block;" class="rounded">
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/customer_app.js"></script>
</body>

</html>