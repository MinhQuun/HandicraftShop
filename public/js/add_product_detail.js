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
function wrapEl() {
    return document.querySelector(".product-detail");
}
function getDataset(key, fallback = "") {
    const el = wrapEl();
    if (!el) return fallback;
    const v = el.dataset[key] ?? "";
    return v;
}
function getStockMax() {
    const ds = getDataset("stock", "");
    if (ds !== "" && !Number.isNaN(Number(ds))) return Number(ds);

    const qtyInput = document.getElementById("quantity");
    const maxAttr = qtyInput ? Number(qtyInput.getAttribute("max")) : NaN;
    if (!Number.isNaN(maxAttr)) return maxAttr;

    const stockText =
        document.querySelector(".product-description")?.textContent || "";
    const m = stockText.match(/Số lượng còn:\s*(\d+)/);
    return m ? Number(m[1]) : 9999;
}
function isLoggedIn() {
    return getDataset("isLoggedIn") === "1";
}
function cartAddUrl() {
    return getDataset("cartAddUrl", "");
}
function reviewCreateUrl() {
    return getDataset("reviewCreateUrl", "");
}

/* =================== Qty read/write =================== */
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
    if (input) input.value = String(val);
}

/* =================== Event wiring =================== */
document.addEventListener("click", (e) => {
    const t = e.target;

    // Qty
    if (t.closest('[data-action="qty-inc"]')) {
        writeQty(readQty() + 1);
        return;
    }
    if (t.closest('[data-action="qty-dec"]')) {
        writeQty(readQty() - 1);
        return;
    }

    // Add to cart
    if (t.closest('[data-action="add-to-cart"]')) {
        addToCart();
        return;
    }

    // Open login modal for reviews
    if (t.closest('[data-action="open-login"]')) {
        openLoginModal();
        return;
    }

    // Submit review
    if (t.closest('[data-action="submit-review"]')) {
        submitReview();
        return;
    }

    // Star click
    const star = t.closest("#starsInput i[data-star]");
    if (star) {
        const val = Number(star.getAttribute("data-star"));
        const score = document.getElementById("score");
        if (score) score.value = val;
        document.querySelectorAll("#starsInput i").forEach((i) => {
            const n = Number(i.getAttribute("data-star"));
            i.classList.toggle("active", n <= val);
            i.classList.toggle("fas", n <= val);
            i.classList.toggle("far", n > val);
        });
    }
});

/* =================== Auth modal =================== */
function openLoginModal() {
    const modalEl = document.getElementById("authModal");
    if (modalEl && window.bootstrap?.Modal) {
        new bootstrap.Modal(modalEl).show();
        return;
    }
    // Fallback: chuyển về trang đăng nhập, kèm redirect lại trang hiện tại
    const loginUrl = getDataset("loginUrl", "/login");
    const back = encodeURIComponent(window.location.href);
    window.location.href = `${loginUrl}?redirect=${back}`;
}
window._openLogin = openLoginModal; // dự phòng cho onclick inline

/* =================== HTTP =================== */
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

/* =================== Cart =================== */
let addBusy = false;
async function addToCart() {
    if (addBusy) return;
    const btn = document.getElementById("btnAddToCart");
    const qty = readQty();
    const stock = getStockMax();
    if (qty < 1 || stock === 0) return;

    const url = cartAddUrl();
    if (!url) {
        alert("Thiếu cấu hình cartAddUrl.");
        return;
    }

    // Mã SP lấy từ view form (an toàn: dùng dataset trên form review nếu có)
    // Nếu không có form review, suy luận từ URL cuối (chuẩn route /sp/{MASP})
    let masp = wrapEl()?.querySelector("#create-review-form")?.dataset?.masp;
    if (!masp) {
        const parts = window.location.pathname.split("/").filter(Boolean);
        masp = parts[parts.length - 1] || "";
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
            product_id: masp,
            qty: qty,
        });

        if (res.status === 401) {
            openLoginModal();
            return;
        }

        let data = {};
        try {
            data = await res.json();
        } catch {}

        if (!res.ok) {
            const msg =
                (data && data.message) || `Yêu cầu thất bại (${res.status})`;
            throw new Error(msg);
        }

        alert(data.message || "Đã thêm vào giỏ!");
        if (typeof data.cart_count !== "undefined") {
            const badge = document.getElementById("cart-count");
            if (badge) badge.textContent = data.cart_count;
        }
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
}

/* =================== Review =================== */
async function submitReview() {
    if (!isLoggedIn()) {
        openLoginModal();
        return;
    }

    const form = document.getElementById("create-review-form");
    if (!form) return;

    const masp = form.dataset.masp || "";
    const url = reviewCreateUrl();
    if (!url) {
        alert("Thiếu cấu hình reviewCreateUrl.");
        return;
    }

    const score = Number(document.getElementById("score")?.value || 5);
    const comment = document.getElementById("comment")?.value || "";

    try {
        const res = await postJson(url, { DIEMSO: score, NHANXET: comment });
        if (res.status === 401) {
            openLoginModal();
            return;
        }

        let data = {};
        try {
            data = await res.json();
        } catch {}
        if (!res.ok) throw new Error(data.message || `Lỗi ${res.status}`);

        alert(data.message || "Đã gửi đánh giá.");
        const href =
            window.location.pathname + window.location.search + "#reviews";
        window.location.replace(href);
        window.location.reload();
    } catch (e) {
        alert(e?.message || "Gửi đánh giá thất bại.");
    }
}
/* ================ STAR RATING  ================ */
(function () {
    const box = document.getElementById("starsInput"); // <div class="stars-input" id="starsInput">
    const scoreInput = document.getElementById("score"); // <input type="hidden" id="score">

    if (!box || !scoreInput) return;

    function applyStars(score) {
        const n = Math.max(1, Math.min(5, Number(score) || 0));
        box.querySelectorAll("i[data-star]").forEach((el) => {
            const s = Number(el.getAttribute("data-star"));
            // tô đầy <= điểm, để rỗng > điểm
            el.classList.toggle("fas", s <= n); // filled
            el.classList.toggle("far", s > n); // outline
            el.classList.toggle('active', s <= n);
        });
    }

    // init theo giá trị hiện tại (Blade đang set value="5")
    applyStars(scoreInput.value);

    // click để chọn điểm
    box.addEventListener("click", (e) => {
        const icon = e.target.closest("i[data-star]");
        if (!icon) return;
        const val = Number(icon.getAttribute("data-star")) || 0;
        if (!val) return;
        scoreInput.value = String(val);
        applyStars(val);
    });

    // (tuỳ chọn) hover preview
    box.addEventListener("mouseover", (e) => {
        const icon = e.target.closest("i[data-star]");
        if (!icon) return;
        applyStars(icon.getAttribute("data-star"));
    });
    box.addEventListener("mouseleave", () => {
        applyStars(scoreInput.value);
    });
})();
