<?php require_once 'auth_check.php'; ?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<div id="page-content-wrapper" class="w-100">
    <nav class="navbar navbar-light bg-white border-bottom px-4 py-3">
        <span class="navbar-brand mb-0 h1">จัดการหมวดหมู่สินค้า</span>
    </nav>
    <div class="container-fluid p-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#catModal">
                    <i class="bi bi-plus"></i> เพิ่มหมวดหมู่
                </button>
                <table id="catTable" class="table table-striped w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อหมวดหมู่</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="addCatForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>เพิ่มหมวดหมู่</h5>
                </div>
                <div class="modal-body">
                    <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">บันทึก</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>