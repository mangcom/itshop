$(document).ready(function () {
  // กล่องสำหรับแสดงภาพขยาย (Hover Preview)
  const popup = $("#image-preview-popup");
  const popupImg = popup.find("img");

  let table = $("#customerProductTable").DataTable({
    ajax: "admin/api/product_api.php?action=list",
    columns: [
      {
        data: "image_path",
        render: function (data) {
          let imgPath = data ? data : "https://via.placeholder.com/60x60?text=No+Image";
          return `<img src="${imgPath}" width="50" height="50" class="img-thumbnail img-hover-preview shadow-sm" data-large-src="${imgPath}" style="cursor: pointer; object-fit: cover;">`;
        },
      },
      { data: "category_name" },
      { data: "brand_name" },
      {
        data: null,
        render: function (data) {
          // ปรับให้ รุ่น และ Model อยู่บรรทัดเดียวกัน
          return `${data.version} <span class="text-muted small">(${data.model})</span>`;
        },
      },
      {
        data: "price",
        render: function (data) {
          return `<strong class="text-dark">${parseFloat(data).toLocaleString()}</strong> .-`;
        },
      },
      {
        data: null,
        orderable: false, // ปิดการเรียงลำดับในคอลัมน์จัดการ
        className: "text-center align-middle", // จัดกึ่งกลาง
        width: "100px", // กำหนดความกว้างให้แคบพอดีไอคอน
        render: function (data) {
          // ไอคอน PDF สีแดง (text-danger) หากมีไฟล์
          let datasheetIcon = data.datasheet_path
            ? `<a href="${data.datasheet_path}" target="_blank" class="text-danger me-2" title="Datasheet PDF">
                <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
               </a>`
            : "";

          return `
                <div class="d-flex align-items-center justify-content-center">
                    <a href="javascript:void(0)" class="text-primary me-2" onclick="viewDetail(${data.id})" title="ดูรายละเอียด">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                    </a>
                    ${datasheetIcon}
                    <a href="javascript:void(0)" class="text-success" onclick='addToCart(${JSON.stringify(data)})' title="เพิ่มลงตะกร้า">
                        <i class="bi bi-cart-plus-fill fs-5"></i>
                    </a>
                </div>
            `;
        },
      },
    ],
    language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json" },
    // จัดลำดับการเรียงเริ่มต้นตามที่คุณต้องการ
    order: [
      [1, "asc"],
      [2, "asc"],
      [3, "asc"],
    ],
  });

  // ระบบ Hover Preview
  $("body").on("mouseenter", ".img-hover-preview", function (e) {
    const largeSrc = $(this).data("large-src");
    if (largeSrc && !largeSrc.includes("via.placeholder.com")) {
      popupImg.attr("src", largeSrc);
      popup
        .css({
          top: e.pageY + 10 + "px",
          left: e.pageX + 10 + "px",
        })
        .stop(true, true)
        .fadeIn(150);
    }
  });

  $("body").on("mouseleave", ".img-hover-preview", function () {
    popup.stop(true, true).fadeOut(150);
  });

  $("body").on("mousemove", ".img-hover-preview", function (e) {
    popup.css({ top: e.pageY + 10 + "px", left: e.pageX + 10 + "px" });
  });

  $("#filter-category").on("change", function () {
    table.column(1).search(this.value).draw();
  });

  updateCartCount();
});

function viewDetail(id) {
  $.getJSON(`admin/api/product_api.php?action=get&id=${id}`, function (res) {
    if (res.status === "success") {
      let p = res.data;

      // --- จัดการคุณสมบัติให้เป็น Bullet พร้อมรองรับการจัดข้อความเยื้อง (Indentation) ---
      let specHtml = "<li style='margin-bottom: 8px;'>ไม่มีข้อมูลเพิ่มเติม</li>";
      if (p.specifications) {
        try {
          let specObj = JSON.parse(p.specifications);
          let rawText = specObj.detail || p.specifications;
          let lines = rawText
            .split(/\r?\n/)
            .map((line) => line.trim())
            .filter((line) => line.length > 0);

          if (lines.length > 0) {
            // ใช้ CSS text-indent เพื่อดันบรรทัดที่ 2 เป็นต้นไปให้เยื้องเข้าไป
            specHtml = lines
              .map(
                (line) =>
                  `<li style="padding-left: 1.5em; text-indent: -1em; margin-bottom: 8px;">
                                - ${line}
                            </li>`,
              )
              .join("");
          }
        } catch (e) {
          specHtml = `<li style="padding-left: 1.5em; text-indent: -1em;">- ${p.specifications}</li>`;
        }
      }

      let html = `
                <div class="modal-header bg-light">
                    <h5 class="modal-title">รายละเอียดสินค้า: ${p.brand_name}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-5 text-center">
                            <img src="${p.image_path || "https://via.placeholder.com/400x300?text=No+Image"}" 
                                 class="img-fluid rounded shadow-sm" style="max-height: 250px; object-fit: contain;">
                        </div>
                        <div class="col-md-7">
                            <h4 class="text-primary fw-bold">${p.brand_name}</h4>
                            <h5 class="text-secondary">${p.version}</h5>
                            <p class="text-muted mb-1">Model: <code>${p.model}</code></p>
                            <p class="text-muted mb-1">ประเภท: <strong>${p.category_name || "ไม่ระบุ"}</strong></p>
                            <h3 class="text-danger fw-bold mt-3">฿${parseFloat(p.price).toLocaleString()}</h3>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12 px-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-list-ul"></i> คุณสมบัติสินค้า:</h6>
                            <ul class="list-unstyled mt-2" style="line-height: 1.6; color: #444;">
                                ${specHtml}
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <div class="row w-100">
                        <div class="col-6">
                            ${
                              p.datasheet_path
                                ? `<a href="${p.datasheet_path}" target="_blank" class="btn btn-outline-info w-100">
                                    <i class="bi bi-file-earmark-pdf"></i> Datasheet
                                 </a>`
                                : `<button class="btn btn-outline-secondary w-100" disabled>ไม่มี Datasheet</button>`
                            }
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success w-100" onclick='addToCart(${JSON.stringify(p)})'>
                                <i class="bi bi-cart-plus"></i> เพิ่มลงตะกร้า
                            </button>
                        </div>
                    </div>
                </div>`;

      $("#modal-content-area").html(html);
      $("#productDetailModal").modal("show");
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
