<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า - IT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body class="bg-light p-4">
    <div class="container-fluid px-5 mt-4">
        <div class="card shadow-sm border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="bi bi-cart3"></i> ตะกร้าสินค้าของคุณ</h4>
                <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> กลับไปเลือกสินค้า</a>
            </div>

            <table id="cartTable" class="table align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>รูปภาพ</th>
                        <th>ประเภท</th>
                        <th>ยี่ห้อ</th>
                        <th>รุ่น</th>
                        <th>Model</th>
                        <th>ราคาต่อหน่วย</th>
                        <th width="120">จำนวน</th>
                        <th>ราคารวม</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody id="cart-list">
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="7" class="text-end">ราคารวมสุทธิทั้งหมด:</td>
                        <td id="grand-total" class="text-danger">฿0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="text-end mt-4">
                <!-- <button class="btn btn-outline-primary" onclick="exportPDF()">
                    <i class="bi bi-file-earmark-pdf"></i> Export เป็น PDF
                </button> -->
                <a href="export_pdf.php" target="_blank" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-pdf"></i> Export เป็น PDF
                </a>
                <button class="btn btn-danger" onclick="clearCart()"><i class="bi bi-trash"></i> ล้างตะกร้า</button>
                <button class="btn btn-success px-5" onclick="checkout()"><i class="bi bi-check-circle"></i> ยืนยันรายการ</button>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let cartTable;

        function renderCart() {
            let cart = Cookies.get('it_shop_cart') ? JSON.parse(Cookies.get('it_shop_cart')) : [];
            let html = '';
            let grandTotal = 0;

            cart.forEach((item, idx) => {
                let subtotal = item.price * item.qty;
                grandTotal += subtotal;
                html += `
                <tr>
                    <td><img src="${item.img || 'https://via.placeholder.com/60'}" class="img-cart shadow-sm"></td>
                    <td><span class="badge bg-secondary">${item.category_name || 'ทั่วไป'}</span></td>
                    <td>${item.brand_name}</td>
                    <td>${item.version}</td>
                    <td><small class="text-muted">${item.model}</small></td>
                    <td>฿${parseFloat(item.price).toLocaleString()}</td>
                    <td>
                        <div class="input-group input-group-sm">
                            <button class="btn btn-outline-dark" onclick="changeQty(${idx}, -1)">-</button>
                            <input type="text" class="form-control text-center bg-white" value="${item.qty}" readonly>
                            <button class="btn btn-outline-dark" onclick="changeQty(${idx}, 1)">+</button>
                        </div>
                    </td>
                    <td class="fw-bold">฿${subtotal.toLocaleString()}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${idx})">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>`;
            });

            if ($.fn.DataTable.isDataTable('#cartTable')) {
                cartTable.destroy();
            }

            $('#cart-list').html(html);
            $('#grand-total').text('฿' + grandTotal.toLocaleString());

            cartTable = $('#cartTable').DataTable({
                pageLength: 50,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json'
                },
                columnDefs: [{
                    orderable: false,
                    targets: [0, 6, 8]
                }]
            });
        }

        function changeQty(idx, delta) {
            let cart = JSON.parse(Cookies.get('it_shop_cart'));
            cart[idx].qty += delta;
            if (cart[idx].qty <= 0) {
                removeItem(idx);
            } else {
                Cookies.set('it_shop_cart', JSON.stringify(cart), {
                    expires: 0.5
                });
                renderCart();
            }
        }

        function removeItem(idx) {
            let cart = JSON.parse(Cookies.get('it_shop_cart'));
            cart.splice(idx, 1);
            Cookies.set('it_shop_cart', JSON.stringify(cart), {
                expires: 0.5
            });
            renderCart();
        }

        function clearCart() {
            Swal.fire({
                title: 'ล้างตะกร้า?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ล้างเลย'
            }).then((res) => {
                if (res.isConfirmed) {
                    Cookies.remove('it_shop_cart');
                    renderCart();
                }
            });
        }

        $(document).ready(renderCart);

        // function exportPDF() {
        //     const {
        //         jsPDF
        //     } = window.jspdf;
        //     const doc = new jsPDF();

        //     // ตั้งค่าฟอนต์ (เนื่องจาก PDF มาตรฐานไม่รองรับไทย ต้องใช้การตั้งค่าพิเศษหรือใช้ Canvas)
        //     // ในที่นี้จะใช้การดึงข้อมูลจากตาราง HTML โดยตรง

        //     doc.text("รายการสินค้าที่เลือก (IT SHOP)", 14, 15);

        //     // ดึงข้อมูลจาก Cookie
        //     let cart = Cookies.get('it_shop_cart') ? JSON.parse(Cookies.get('it_shop_cart')) : [];

        //     if (cart.length === 0) {
        //         Swal.fire('ผิดพลาด', 'ไม่มีสินค้าในตะกร้าสำหรับ Export', 'error');
        //         return;
        //     }

        //     let body = [];
        //     let grandTotal = 0;

        //     cart.forEach((item, index) => {
        //         let subtotal = item.price * item.qty;
        //         grandTotal += subtotal;
        //         body.push([
        //             index + 1,
        //             item.category_name,
        //             `${item.brand_name} ${item.version}`,
        //             item.model,
        //             parseFloat(item.price).toLocaleString(),
        //             item.qty,
        //             subtotal.toLocaleString()
        //         ]);
        //     });

        //     // สร้างตารางใน PDF
        //     doc.autoTable({
        //         startY: 20,
        //         head: [
        //             ['#', 'ประเภท', 'รายการ', 'Model', 'ราคา/หน่วย', 'จำนวน', 'ราคารวม']
        //         ],
        //         body: body,
        //         foot: [
        //             ['', '', '', '', '', 'ราคารวมสุทธิ', grandTotal.toLocaleString() + ' บาท']
        //         ],
        //         styles: {
        //             font: 'courier',
        //             fontSize: 8
        //         }, // หมายเหตุ: Courier ไม่รองรับไทยโดยตรง
        //         theme: 'grid'
        //     });

        //     doc.save(`it-shop-cart-${Date.now()}.pdf`);
        // }
        function exportPDF() {
            // ซ่อนปุ่มต่างๆ ก่อนพิมพ์
            const originalHeader = document.title;
            document.title = "รายการสั่งซื้อสินค้า_IT_SHOP";

            // ใช้คำสั่งพิมพ์ของ Browser
            window.print();

            document.title = originalHeader;
        }
    </script>
</body>

</html>