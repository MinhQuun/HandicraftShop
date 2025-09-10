function getCsrf() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

// Thêm vào giỏ từ danh sách: mặc định qty = 1
async function addToCart(btn, productId) {
  const original = btn.innerHTML;

  try {
    btn.disabled = true;
    btn.innerHTML = 'Đang thêm...';

    const res = await fetch(window.cartAddUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ product_id: productId, qty: 1 })
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data?.message || 'Yêu cầu thất bại');

    alert(data.message || 'Đã thêm vào giỏ!');

    // Nếu backend trả số lượng giỏ hiện tại -> cập nhật badge (nếu có)
    if (typeof data.cart_count !== 'undefined') {
      const badge = document.getElementById('cart-count');
      if (badge) badge.textContent = data.cart_count;
    }
  } catch (err) {
    console.error(err);
    alert(err.message || 'Thêm vào giỏ thất bại. Vui lòng thử lại.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = original;
  }
}
