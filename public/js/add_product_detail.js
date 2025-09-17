// =================== Tiện ích ===================
function clamp(n, min, max) {
  n = Number(n);
  if (Number.isNaN(n)) n = 1;
  return Math.max(min, Math.min(max, n));
}

function getCsrf() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

// =================== Thêm vào giỏ ===================
async function addToCart(productId) {
  const btn = document.getElementById('btnAddToCart');
  const qtyInput = document.getElementById('quantity');
  const maxQty = Number(qtyInput.getAttribute('max')) || 9999;
  const qty = clamp(qtyInput.value, 1, maxQty);

  try {
    btn.disabled = true;
    btn.dataset._text = btn.innerHTML;
    btn.innerHTML = 'Đang thêm...';

    const res = await fetch(window.cartAddUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ product_id: productId, qty })
    });

    // Nếu chưa đăng nhập → mở modal auth
    if (res.status === 401) {
      const modal = document.getElementById('authModal');
      if (modal) {
        const authModal = new bootstrap.Modal(modal);
        authModal.show();
      } else {
        alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ.');
      }
      return;
    }

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      const msg = (data && data.message) || 'Yêu cầu thất bại';
      throw new Error(msg);
    }

    alert(data.message || 'Đã thêm vào giỏ!');
    if (typeof data.cart_count !== 'undefined') {
      const badge = document.getElementById('cart-count');
      if (badge) badge.textContent = data.cart_count;
    }
  } catch (err) {
    console.error(err);
    alert(err.message || 'Thêm vào giỏ thất bại. Vui lòng thử lại.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = btn.dataset._text || 'Chọn mua';
  }
}

// =================== Tăng/giảm số lượng ===================
function increaseQty() {
  const qtyInput = document.getElementById('quantity');
  const maxQty = Number(qtyInput.getAttribute('max')) || 9999;
  qtyInput.value = clamp(Number(qtyInput.value) + 1, 1, maxQty);
}

function decreaseQty() {
  const qtyInput = document.getElementById('quantity');
  qtyInput.value = clamp(Number(qtyInput.value) - 1, 1, Number(qtyInput.getAttribute('max')) || 9999);
}
