<?php require_once 'auth_check.php'; ?>
<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<div id="page-content-wrapper" class="w-100">
    <nav class="navbar navbar-light bg-white border-bottom px-4 py-3">
        <span class="navbar-brand mb-0 h1">ตั้งค่าบัญชีผู้ใช้</span>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Reset Password</li>
            </ol>
        </nav>
    </nav>

    <div class="container-fluid p-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="bi bi-key me-2"></i>เปลี่ยนรหัสผ่าน</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="resetPassForm">
                            <div class="mb-3">
                                <label class="form-label">รหัสผ่านใหม่</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                            </div>
                            <hr>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>อัปเดตรหัสผ่าน
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>