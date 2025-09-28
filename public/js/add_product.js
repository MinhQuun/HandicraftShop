(function () {
    // ---- Meta helpers ----
    function getMeta(name) {
        const el = document.querySelector(`meta[name="${name}"]`);
        return el ? el.getAttribute("content") : "";
    }

    // ---- CSRF ----
    function getCsrf() {
        return getMeta("csrf-token") || "";
    }

    // ---- Ensure cartAddUrl available (ưu tiên meta, fallback window) ----
    if (!window.cartAddUrl) {
        const fromMeta = getMeta("cart-add-url");
        if (fromMeta) window.cartAddUrl = fromMeta;
    }

    // ---- Add to cart (qty = 1) ----
    async function addToCart(btn, productId) {
        const original = btn.innerHTML;

        try {
            btn.disabled = true;
            btn.innerHTML = "Đang thêm...";

            const res = await fetch(window.cartAddUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrf(),
                    Accept: "application/json",
                },
                body: JSON.stringify({ product_id: productId, qty: 1 }),
            });

            // Chưa đăng nhập
            if (res.status === 401) {
                const modal = document.getElementById("authModal");
                if (modal && window.bootstrap?.Modal) {
                    new bootstrap.Modal(modal).show();
                } else {
                    alert("Vui lòng đăng nhập để sử dụng giỏ hàng!");
                }
                return;
            }

            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data?.message || "Yêu cầu thất bại");

            alert(data.message || "Đã thêm vào giỏ!");

            // Cập nhật badge giỏ (nếu backend trả về)
            if (typeof data.cart_count !== "undefined") {
                const badge = document.getElementById("cart-count");
                if (badge) badge.textContent = data.cart_count;
            }
        } catch (err) {
            console.error(err);
            alert(err.message || "Thêm vào giỏ thất bại. Vui lòng thử lại.");
        } finally {
            btn.disabled = false;
            btn.innerHTML = original;
        }
    }

    // ---- Price Filter (chips + Enter to submit) ----
    function initPriceFilter() {
        const form = document.getElementById("priceFilterForm");
        if (!form) return;

        // Chips nhanh
        form.querySelectorAll(".quick-chips .chip").forEach((btn) => {
            btn.addEventListener("click", () => {
                const minInput = form.querySelector('input[name="min_price"]');
                const maxInput = form.querySelector('input[name="max_price"]');
                if (minInput) minInput.value = btn.dataset.min ?? "";
                if (maxInput) {
                    maxInput.value = btn.dataset.max ?? "";
                    if (btn.dataset.max === "") maxInput.value = "";
                }
                form.requestSubmit ? form.requestSubmit() : form.submit();
            });
        });

        // Nhấn Enter trong input number để submit
        form.querySelectorAll('input[type="number"]').forEach((inp) => {
            inp.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }
            });
        });
    }

    // Expose các hàm cần gọi từ HTML
    window.getCsrf = getCsrf;
    window.addToCart = addToCart;

    document.addEventListener("DOMContentLoaded", initPriceFilter);
})();
