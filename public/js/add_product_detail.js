/* =================== Helpers =================== */
function clamp(n, min, max) {
    n = Number(n);
    if (Number.isNaN(n)) n = 1;
    return Math.max(min, Math.min(max, n));
}

function getCsrf() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute("content") : "";
}

function getStockMax() {
    // Ưu tiên: window.STOCK_MAX (blade đã truyền) -> data-stock trên .product-detail -> input[max] -> parse từ text
    if (typeof window.STOCK_MAX === "number" && window.STOCK_MAX >= 0)
        return window.STOCK_MAX;

    const wrap = document.querySelector(".product-detail");
    const ds = wrap?.getAttribute("data-stock");
    if (ds && !Number.isNaN(Number(ds))) return Number(ds);

    const qtyInput = document.getElementById("quantity");
    const maxAttr = qtyInput ? Number(qtyInput.getAttribute("max")) : NaN;
    if (!Number.isNaN(maxAttr)) return maxAttr;

    const stockText =
        document.querySelector(".product-description")?.textContent || "";
    const m = stockText.match(/Số lượng còn:\s*(\d+)/);
    return m ? Number(m[1]) : 9999;
}

/* =================== Qty read/write (hỗ trợ 2 UI) =================== */
function readQty() {
    const span = document.getElementById("qtyNumber");
    if (span) {
        const v = parseInt(span.textContent || "1", 10);
        return Number.isNaN(v) ? 1 : v;
    }
    const input = document.getElementById("quantity");
    if (input) {
        const max = Number(input.getAttribute("max")) || getStockMax();
        return clamp(input.value, 1, max);
    }
    return 1;
}

function writeQty(val) {
    const max = getStockMax();
    val = clamp(val, 1, max);

    const span = document.getElementById("qtyNumber");
    if (span) {
        span.textContent = String(val);
        return;
    }
    const input = document.getElementById("quantity");
    if (input) {
        input.value = String(val);
    }
}

/* Tăng/giảm cho UI mới (qty-box) và dùng được ở inline onclick */
window.changeQty = function changeQty(delta) {
    writeQty(readQty() + Number(delta || 0));
};

/* Tăng/giảm cho UI cũ (nếu còn dùng nút riêng) */
window.increaseQty = function () {
    writeQty(readQty() + 1);
};
window.decreaseQty = function () {
    writeQty(readQty() - 1);
};

/* =================== Add to Cart =================== */
let addBusy = false;

async function postJson(url, payload) {
    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrf(),
            Accept: "application/json",
        },
        body: JSON.stringify(payload),
    });
    return res;
}

/**
 * Gọi từ nút "Chọn mua"
 * - masp: mã sản phẩm (string)
 * - payload gửi cả hai kiểu key để tương thích backend:
 *   { MASANPHAM, SOLUONG } và { product_id, qty }
 */
window.addToCart = async function addToCart(masp) {
    if (addBusy) return;
    const btn = document.getElementById("btnAddToCart");
    const qty = readQty();
    const stock = getStockMax();
    if (qty < 1) return;
    if (stock === 0) return;

    const url = window.cartAddUrl;
    if (!url) {
        alert("Thiếu cấu hình cartAddUrl.");
        return;
    }

    try {
        addBusy = true;
        if (btn) {
            btn.disabled = true;
            btn.dataset._text = btn.innerHTML;
            btn.setAttribute("aria-busy", "true");
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang thêm...';
        }

        const res = await postJson(url, {
            MASANPHAM: masp,
            SOLUONG: qty,
            product_id: masp, // fallback cho backend cũ
            qty: qty,
        });

        // 401 -> mở modal đăng nhập (nếu có)
        if (res.status === 401) {
            const modalEl = document.getElementById("authModal");
            if (modalEl && window.bootstrap?.Modal) {
                new bootstrap.Modal(modalEl).show();
            } else {
                alert("Vui lòng đăng nhập để thêm sản phẩm vào giỏ.");
            }
            return;
        }

        // Thử đọc JSON (có thể server trả không phải JSON)
        let data = {};
        try {
            data = await res.json();
        } catch {
            /* ignore */
        }

        if (!res.ok) {
            const msg =
                (data && data.message) || `Yêu cầu thất bại (${res.status})`;
            throw new Error(msg);
        }

        // OK
        alert(data.message || "Đã thêm vào giỏ!");
        if (typeof data.cart_count !== "undefined") {
            const badge = document.getElementById("cart-count");
            if (badge) badge.textContent = data.cart_count;
        }
        // Nếu server trả redirect, có thể chuyển trang:
        if (data.redirect) window.location.href = data.redirect;
    } catch (err) {
        console.error(err);
        alert(err?.message || "Thêm vào giỏ thất bại. Vui lòng thử lại.");
    } finally {
        addBusy = false;
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = btn.dataset._text || "Chọn mua";
            btn.removeAttribute("aria-busy");
        }
    }
};
