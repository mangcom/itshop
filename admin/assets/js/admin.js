$(document).ready(function () {
  // === 1. การจัดการหมวดหมู่สินค้า (Categories) ===
  if ($("#catTable").length > 0) {
    let catTable = $("#catTable").DataTable({
      ajax: "api/category_api.php?action=list",
      columns: [
        { data: "id" },
        { data: "category_name" },
        {
          data: null,
          render: function (data) {
            return `<button class="btn btn-sm btn-danger" onclick="deleteCat(${data.id})">
                                    <i class="bi bi-trash"></i> ลบ
                                </button>`;
          },
        },
      ],
      order: [[1, "asc"]],
      language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json" },
    });

    // เพิ่มหมวดหมู่
    $("#addCatForm").on("submit", function (e) {
      e.preventDefault();
      $.ajax({
        url: "api/category_api.php?action=add",
        method: "POST",
        data: $(this).serialize(),
        success: function (response) {
          if (response.status === "success") {
            // ปรับปรุง: หายไปเองใน 1 วินาที
            Swal.fire({
              icon: "success",
              title: "สำเร็จ!",
              text: "เพิ่มหมวดหมู่เรียบร้อยแล้ว",
              timer: 1000,
              showConfirmButton: false,
            });
            $("#catModal").modal("hide");
            $("#addCatForm")[0].reset();
            catTable.ajax.reload();
          }
        },
      });
    });
  }

  // === 2. การจัดการรายการสินค้า (Products) ===
  if ($("#productTable").length > 0) {
    let productTable = $("#productTable").DataTable({
      ajax: "api/product_api.php?action=list_all",
      columns: [
        { data: "image_path", render: (data) => (data ? `<img src="../${data}" width="50" class="img-thumbnail">` : "ไม่มีรูป") },
        { data: "brand_name" },
        { data: "version" },
        { data: "model" },
        { data: "category_name" },
        { data: "price", render: (data) => "<strong>" + parseFloat(data).toLocaleString() + "</strong>" },
        {
          data: "deleted_at",
          render: function (data) {
            return data ? `<span class="badge bg-danger">ถูกลบ</span>` : `<span class="badge bg-success">ปกติ</span>`;
          },
        },
        {
          data: null,
          orderable: false,
          className: "text-center",
          render: function (data) {
            if (data.deleted_at) {
              return `<button class="btn btn-sm btn-info text-white" onclick="restoreProduct(${data.id})"><i class="bi bi-arrow-counterclockwise"></i> กู้คืน</button>`;
            }

            // เช็คไฟล์ PDF (ไอคอนสีแดง)
            let pdfBtn = data.datasheet_path
              ? `<a href="../${data.datasheet_path}" target="_blank" class="text-danger mx-1" title="เปิด PDF">
                 <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
               </a>`
              : "";

            return `
            <div class="d-flex align-items-center justify-content-center">
                <a href="javascript:void(0)" class="text-primary mx-1" onclick="viewDetailAdmin(${data.id})" title="ดูรายละเอียด">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                </a>
                
                ${pdfBtn}

                <a href="javascript:void(0)" class="text-warning mx-1" onclick="editProduct(${data.id})" title="แก้ไขข้อมูล">
                    <i class="bi bi-pencil-fill fs-5"></i>
                </a>

                <a href="javascript:void(0)" class="text-danger mx-1" onclick="deleteProduct(${data.id})" title="ลบสินค้า">
                    <i class="bi bi-trash-fill fs-5"></i>
                </a>
            </div>
        `;
          },
        },
      ],
      order: [
        [4, "asc"],
        [1, "asc"],
        [2, "asc"],
        [3, "asc"],
      ],
      language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json" },
    });

    // บันทึก/แก้ไขสินค้า
    $("#addProductForm").on("submit", function (e) {
      e.preventDefault();
      let formData = new FormData(this);

      $.ajax({
        url: "api/product_api.php?action=add",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function (res) {
          if (res.status === "success") {
            // ปรับปรุง: หายไปเองใน 1 วินาที
            Swal.fire({
              icon: "success",
              title: "สำเร็จ",
              text: res.message,
              timer: 1000,
              showConfirmButton: false,
            });
            $("#productModal").modal("hide");
            $("#addProductForm")[0].reset();
            $("#product_id").val("");
            productTable.ajax.reload();
          } else {
            Swal.fire("เกิดข้อผิดพลาด", res.message, "error");
          }
        },
      });
    });

    // ส่วนของ Brand Auto-complete (คงเดิม)
    $("#brand_input").on("keyup", function () {
      let term = $(this).val();
      let list = $("#brand_list");
      if (term.length >= 3) {
        $.getJSON("api/brand_api.php", { term: term }, function (data) {
          list.empty().show();
          if (data.length > 0) {
            data.forEach((item) => {
              list.append(`<a href="#" class="list-group-item list-group-item-action brand-item">${item.brand_name}</a>`);
            });
          } else {
            list.hide();
          }
        });
      } else {
        list.hide();
      }
    });

    $(document).on("click", ".brand-item", function (e) {
      e.preventDefault();
      $("#brand_input").val($(this).text());
      $("#brand_list").hide();
    });
  }

  // === 3. ระบบเปลี่ยนรหัสผ่าน ===
  $("#resetPassForm").on("submit", function (e) {
    e.preventDefault();
    if ($("#new_password").val() !== $("#confirm_password").val()) {
      Swal.fire("คำเตือน", "รหัสผ่านใหม่ไม่ตรงกัน", "warning");
      return;
    }
    $.ajax({
      url: "api/auth_api.php?action=reset_password",
      type: "POST",
      data: $(this).serialize(),
      success: function (res) {
        if (res.status === "success") {
          // ปรับปรุง: หายไปเองใน 1 วินาที
          Swal.fire({
            icon: "success",
            title: "สำเร็จ!",
            text: res.message,
            timer: 1000,
            showConfirmButton: false,
          }).then(() => {
            $("#resetPassForm")[0].reset();
          });
        } else {
          Swal.fire("ผิดพลาด", res.message, "error");
        }
      },
    });
  });
});

// === ฟังก์ชัน Global ===

function editProduct(id) {
  $.getJSON(`api/product_api.php?action=get&id=${id}`, function (res) {
    if (res.status === "success") {
      let p = res.data;
      $("#product_id").val(p.id);
      $('select[name="category_id"]').val(p.category_id);
      $("#brand_input").val(p.brand_name);
      $('input[name="version"]').val(p.version);
      $('input[name="model"]').val(p.model);
      $('input[name="price"]').val(p.price);
      $('input[name="unit"]').val(p.unit);

      let specValue = p.specifications;
      try {
        let specObj = JSON.parse(p.specifications);
        if (typeof specObj === "object" && specObj !== null && specObj.detail) {
          specValue = specObj.detail;
        }
      } catch (e) {
        specValue = p.specifications;
      }
      $('textarea[name="specifications"]').val(specValue);
      $(".modal-title").text("แก้ไขข้อมูลสินค้า");
      $("#productModal").modal("show");
    }
  });
}

function deleteProduct(id) {
  Swal.fire({
    title: "ยืนยันการลบ?",
    text: "คุณต้องการลบสินค้านี้ใช่หรือไม่!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "ใช่, ลบเลย!",
    cancelButtonText: "ยกเลิก",
  }).then((result) => {
    if (result.isConfirmed) {
      $.post(
        "api/product_api.php?action=delete",
        { id: id },
        function (res) {
          if (res.status === "success") {
            // ปรับปรุง: หายไปเองใน 1 วินาที
            Swal.fire({
              icon: "success",
              title: "ลบแล้ว!",
              text: "ข้อมูลถูกลบออกจากระบบ",
              timer: 1000,
              showConfirmButton: false,
            });
            $("#productTable").DataTable().ajax.reload();
          }
        },
        "json",
      );
    }
  });
}

function deleteCat(id) {
  Swal.fire({
    title: "ยืนยันการลบหมวดหมู่?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "ลบ",
    cancelButtonText: "ยกเลิก",
  }).then((result) => {
    if (result.isConfirmed) {
      $.post(
        "api/category_api.php?action=delete",
        { id: id },
        function (res) {
          if (res.status === "success") {
            // ปรับปรุง: หายไปเองใน 1 วินาที
            Swal.fire({
              icon: "success",
              title: "สำเร็จ",
              text: "หมวดหมู่ถูกลบแล้ว",
              timer: 1000,
              showConfirmButton: false,
            });
            $("#catTable").DataTable().ajax.reload();
          }
        },
        "json",
      );
    }
  });
}

function restoreProduct(id) {
  $.post("api/product_api.php?action=restore", { id: id }, function (res) {
    if (res.status === "success") {
      // ปรับปรุง: หายไปเองใน 1 วินาที
      Swal.fire({
        icon: "success",
        title: "กู้คืนแล้ว",
        text: "รายการสินค้ากลับมาใช้งานได้ปกติ",
        timer: 1000,
        showConfirmButton: false,
      });
      $("#productTable").DataTable().ajax.reload();
    }
  });
}
function viewDetailAdmin(id) {
  $.getJSON(`api/product_api.php?action=get&id=${id}`, function (res) {
    if (res.status === "success") {
      let p = res.data;

      // จัดการ Bullet Points ใน Specifications
      let specHtml = "<li>ไม่มีข้อมูลเพิ่มเติม</li>";
      if (p.specifications) {
        try {
          let specObj = JSON.parse(p.specifications);
          let rawText = specObj.detail || p.specifications;
          let lines = rawText
            .split(/\r?\n/)
            .map((line) => line.trim())
            .filter((line) => line.length > 0);
          if (lines.length > 0) {
            specHtml = lines.map((line) => `<li style="padding-left: 1.5em; text-indent: -1em; margin-bottom: 5px;">- ${line}</li>`).join("");
          }
        } catch (e) {
          specHtml = `<li>- ${p.specifications}</li>`;
        }
      }

      let html = `
        <div class="modal-header bg-light">
            <h5 class="modal-title">ตัวอย่างการแสดงผล: ${p.brand_name}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row mb-4">
                <div class="col-md-5 text-center">
                    <img src="../${p.image_path || "assets/img/no-image.png"}" class="img-fluid rounded shadow-sm" style="max-height: 250px; object-fit: contain;">
                </div>
                <div class="col-md-7">
                    <h4 class="text-primary fw-bold">${p.brand_name}</h4>
                    <h5 class="text-secondary">${p.version}</h5>
                    <p class="text-muted mb-1">Model: <code>${p.model}</code></p>
                    <p class="text-muted mb-1">ประเภท: <strong>${p.category_name}</strong></p>
                    <h3 class="text-danger fw-bold mt-3">฿${parseFloat(p.price).toLocaleString()}</h3>
                </div>
            </div>
            <hr>
            <h6 class="fw-bold"><i class="bi bi-list-ul"></i> คุณสมบัติสินค้า:</h6>
            <ul class="list-unstyled mt-2" style="line-height: 1.6; color: #444;">${specHtml}</ul>
        </div>
        <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            ${p.datasheet_path ? `<a href="../${p.datasheet_path}" target="_blank" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> ดู Datasheet (PDF)</a>` : ""}
        </div>`;

      $("#modal-content-area").html(html); // ใช้ ID เดียวกับหน้าบ้านได้ถ้ามี HTML รองรับ
      $("#productDetailModal").modal("show");
    }
  });
}
