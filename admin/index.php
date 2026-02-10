<?php
require_once 'auth_check.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once dirname(__DIR__) . '/db.php';
$p_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$c_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
?>

<div id="page-content-wrapper" class="w-100">
    <nav class="navbar navbar-light bg-white border-bottom px-4 py-3">
        <span class="navbar-brand mb-0 h1">Dashboard</span>
    </nav>
    <div class="container-fluid p-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <h5>จำนวนสินค้าทั้งหมด</h5>
                        <h2><?php echo $p_count; ?> รายการ</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body">
                        <h5>จำนวนหมวดหมู่</h5>
                        <h2><?php echo $c_count; ?> หมวด</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>