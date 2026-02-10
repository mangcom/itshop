$(document).ready(function () {
  // โหลด DataTable โดยดึงข้อมูลจาก API ตัวเดียวกับหลังบ้าน
  let table = $("#customerProductTable").DataTable({
    ajax: "admin/api/product_api.php?action=list",
    columns: [
      {
        data: "image_path",
        render: function (data) {
          return data ? `<img src="${data}" width="60" class="img-thumbnail">` : "ไม่มีรูป";
        },
      },
      { data: "category_name" },
      { data: "brand_name" },
      {
        data: null,
        render: function (data) {
          return `${data.version} <br> <small class="text-muted">${data.model}</small>`;
        },
      },
      {
        data: "price",
        render: function (data) {
          return `<strong>${parseFloat(data).toLocaleString()}</strong> .-`;
        },
      },
      {
        data: null,
        render: function (data) {
          return `
                        <button class="btn btn-sm btn-outline-primary" onclick="viewDetail(${data.id})">ดูรายละเอียด</button>
                        <button class="btn btn-sm btn-success" onclick='addToCart(${JSON.stringify(data)})'>
                            <i class="bi bi-cart-plus"></i>
                        </button>
                    `;
        },
      },
    ],
    language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json" },
  });

  // ระบบกรองตามประเภท (Select Option)
  $("#filter-category").on("change", function () {
    table.column(1).search(this.value).draw();
  });

  updateCartCount();
});

// ฟังก์ชันดูรายละเอียดใน Modal
function viewDetail(id) {
  $.getJSON(`admin/api/product_api.php?action=get&id=${id}`, function (res) {
    if (res.status === "success") {
      let p = res.data;

      // ตรวจสอบการ Parse JSON ของ specifications
      let specDetail = "ไม่มีข้อมูลเพิ่มเติม";
      try {
        if (p.specifications) {
          let specObj = JSON.parse(p.specifications);
          specDetail = specObj.detail || p.specifications;
        }
      } catch (e) {
        specDetail = p.specifications;
      }

      let html = `
                <div class="modal-header">
                    <h5 class="modal-title">รายละเอียดสินค้า: ${p.brand_name}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 text-center">
                            <img src="${p.image_path || "https://via.placeholder.com/400x300?text=No+Image"}" 
                                 class="img-fluid rounded shadow-sm mb-3" style="max-height: 300px;">
                        </div>
                        <div class="col-md-7">
                            <h4 class="text-primary">${p.brand_name}</h4>
                            <h5 class="mb-3">${p.version}</h5>
                            
                            <table class="table table-sm border-0">
                                <tr>
                                    <td width="30%" class="text-muted">ประเภท:</td>
                                    <td><strong>${p.category_name || "ไม่ระบุ"}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">รุ่น (Version):</td>
                                    <td>${p.version || "-"}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Model:</td>
                                    <td><code>${p.model || "-"}</code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">ราคาต่อหน่วย:</td>
                                    <td><span class="text-danger h4">฿${parseFloat(p.price).toLocaleString()}</span></td>
                                </tr>
                            </table>

                            <hr>
                            <h6>คุณสมบัติ/สเปก:</h6>
                            <p class="text-muted small" style="white-space: pre-line;">${specDetail}</p>
                            
                            <div class="d-grid gap-2 mt-4">
                                ${
                                  p.datasheet_path
                                    ? `<a href="${p.datasheet_path}" target="_blank" class="btn btn-outline-info">
                                        <i class="bi bi-file-earmark-pdf"></i> ดู Specsheet (PDF)
                                     </a>`
                                    : ""
                                }
                                <button class="btn btn-success py-2" onclick='addToCart(${JSON.stringify(p)})'>
                                    <i class="bi bi-cart-plus"></i> เพิ่มเข้าตะกร้าสินค้า
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;

      $("#modal-content-area").html(html);
      $("#productDetailModal").modal("show");
    } else {
      Swal.fire("ผิดพลาด", "ไม่สามารถโหลดข้อมูลได้", "error");
    }
  });
}

// ระบบตะกร้าสินค้า (Cookie 12 ชั่วโมง)
function addToCart(product) {
  let cart = Cookies.get("it_shop_cart");
  cart = cart ? JSON.parse(cart) : [];

  let item = cart.find((i) => i.id === product.id);
  if (item) {
    item.qty += 1;
  } else {
    cart.push({
      id: product.id,
      category_name: product.category_name,
      brand_name: product.brand_name,
      version: product.version,
      model: product.model,
      price: product.price,
      img: product.image_path,
      qty: 1,
    });
  }

  Cookies.set("it_shop_cart", JSON.stringify(cart), { expires: 0.5 }); // 0.5 วัน = 12 ชม.
  updateCartCount();
  Swal.fire({ toast: true, position: "top-end", icon: "success", title: "เพิ่มลงตะกร้าแล้ว", showConfirmButton: false, timer: 1000 });
}

function updateCartCount() {
  let cart = Cookies.get("it_shop_cart");
  cart = cart ? JSON.parse(cart) : [];
  let total = cart.reduce((acc, curr) => acc + curr.qty, 0);
  $("#cart-count").text(total);
}
