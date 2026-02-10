// ฟังก์ชันเพิ่มสินค้าลงตะกร้า (เก็บใน Cookie)
function addToCart(product) {
  let cart = Cookies.get("it_shop_cart");
  cart = cart ? JSON.parse(cart) : [];

  // เช็คว่ามีสินค้าเดิมอยู่ในตะกร้าไหม
  let found = cart.find((item) => item.id === product.id);
  if (found) {
    found.quantity += 1;
  } else {
    cart.push({
      id: product.id,
      brand: product.brand_name,
      model: product.version + " " + product.model,
      price: product.price,
      image: product.image_path,
      quantity: 1,
    });
  }

  // บันทึกลง Cookie (หมดอายุใน 0.5 วัน = 12 ชั่วโมง)
  Cookies.set("it_shop_cart", JSON.stringify(cart), { expires: 0.5 });

  updateCartCount();
  Swal.fire({
    toast: true,
    position: "top-end",
    icon: "success",
    title: "เพิ่มสินค้าลงตะกร้าแล้ว",
    showConfirmButton: false,
    timer: 1500,
  });
}

function updateCartCount() {
  let cart = Cookies.get("it_shop_cart");
  cart = cart ? JSON.parse(cart) : [];
  let total = cart.reduce((sum, item) => sum + item.quantity, 0);
  document.getElementById("cart-count").innerText = total;
}

// โหลดจำนวนตะกร้าเมื่อเปิดหน้าเว็บ
document.addEventListener("DOMContentLoaded", updateCartCount);
