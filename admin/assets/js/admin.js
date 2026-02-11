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
            Swal.fire("สำเร็จ!", "เพิ่มหมวดหมู่เรียบร้อยแล้ว", "success");
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
    // let productTable = $("#productTable").DataTable({
    //   ajax: "api/product_api.php?action=list",
    //   order: [
    //     [4, "asc"],
    //     [1, "asc"],
    //     [2, "asc"],
    //     [3, "asc"],
    //   ],
    //   columns: [
    //     {
    //       data: "image_path",
    //       render: function (data) {
    //         return data ? `<img src="../${data}" width="50" class="img-thumbnail shadow-sm">` : `<span class="text-muted small">ไม่มีรูป</span>`;
    //       },
    //     },
    //     { data: "brand_name" },
    //     { data: "version" },
    //     { data: "model" },
    //     { data: "category_name" },
    //     {
    //       data: "price",
    //       render: function (data) {
    //         return "<strong>" + parseFloat(data).toLocaleString() + "</strong> .-";
    //       },
    //     },
    //     {
    //       data: null,
    //       render: function (data) {
    //         let btn = `
    //                         <div class="btn-group">
    //                             <button class="btn btn-sm btn-warning" onclick="editProduct(${data.id})">
    //                                 <i class="bi bi-pencil"></i>
    //                             </button>
    //                             <button class="btn btn-sm btn-danger" onclick="deleteProduct(${data.id})">
    //                                 <i class="bi bi-trash"></i>
    //                             </button>`;
    //         if (data.datasheet_path) {
    //           btn += ` <a href="../${data.datasheet_path}" target="_blank" class="btn btn-sm btn-info">
    //                                     <i class="bi bi-file-earmark-pdf"></i>
    //                                  </a>`;
    //         }
    //         btn += `</div>`;
    //         return btn;
    //       },
    //     },
    //   ],
    //   language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json" },
    // });
    let productTable = $("#productTable").DataTable({
      ajax: "api/product_api.php?action=list_all", // เปลี่ยนไปใช้ action ที่ดึงทั้งหมดรวมที่ลบแล้ว
      columns: [
        { data: "image_path", render: (data) => (data ? `<img src="../${data}" width="50">` : "ไม่มีรูป") },
        { data: "brand_name" },
        { data: "version" },
        { data: "model" },
        { data: "category_name" },
        { data: "price", render: (data) => parseFloat(data).toLocaleString() },
        {
          data: "deleted_at",
          render: function (data) {
            return data ? `<span class="badge bg-danger">ถูกลบเมื่อ ${data}</span>` : `<span class="badge bg-success">ปกติ</span>`;
          },
        },
        {
          data: null,
          render: function (data) {
            if (data.deleted_at) {
              return `<button class="btn btn-sm btn-info" onclick="restoreProduct(${data.id})"><i class="bi bi-arrow-counterclockwise"></i> กู้คืน</button>`;
            }
            return `
                    <button class="btn btn-sm btn-warning" onclick="editProduct(${data.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(${data.id})"><i class="bi bi-trash"></i></button>
                `;
          },
        },
      ],
      order: [
        [4, "asc"],
        [1, "asc"],
        [2, "asc"],
        [3, "asc"],
      ], // เรียงลำดับตามเงื่อนไขคุณ
    });

    // บันทึก/แก้ไขสินค้า (รองรับไฟล์)
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
            Swal.fire("สำเร็จ", res.message, "success");
            $("#productModal").modal("hide");
            $("#addProductForm")[0].reset();
            $("#product_id").val(""); // เคลียร์ ID หลังบันทึก
            productTable.ajax.reload();
          } else {
            Swal.fire("เกิดข้อผิดพลาด", res.message, "error");
          }
        },
      });
    });

    // ระบบ Auto-complete ยี่ห้อ (พิมพ์ 3 ตัวขึ้นไป)
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

  // === 3. ระบบเปลี่ยนรหัสผ่าน (Reset Password) ===
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
          Swal.fire("สำเร็จ!", res.message, "success").then(() => {
            $("#resetPassForm")[0].reset();
          });
        } else {
          Swal.fire("ผิดพลาด", res.message, "error");
        }
      },
    });
  });
});

// === ฟังก์ชัน Global (เรียกใช้จาก onclick ใน HTML) ===

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
        // ลองตรวจสอบว่าเป็น JSON หรือไม่
        let specObj = JSON.parse(p.specifications);
        // ถ้าเป็น Object และมี key ชื่อ detail ให้ดึงค่าออกมา
        if (typeof specObj === "object" && specObj !== null && specObj.detail) {
          specValue = specObj.detail;
        }
      } catch (e) {
        // ถ้าไม่ใช่ JSON (เช่นเป็นข้อความธรรมดา) ให้ใช้ค่าเดิม
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
            Swal.fire("ลบแล้ว!", "ข้อมูลถูกลบออกจากระบบ", "success");
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
            Swal.fire("สำเร็จ", "หมวดหมู่ถูกลบแล้ว", "success");
            $("#catTable").DataTable().ajax.reload();
          }
        },
        "json",
      );
    }
  });
}

// ฟังก์ชันกู้คืนข้อมูล
function restoreProduct(id) {
  $.post("api/product_api.php?action=restore", { id: id }, function (res) {
    if (res.status === "success") {
      Swal.fire("กู้คืนแล้ว", "รายการสินค้ากลับมาใช้งานได้ปกติ", "success");
      productTable.ajax.reload();
    }
  });
}
