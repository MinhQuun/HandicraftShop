(function () {
    function getMeta(name) {
        const el = document.querySelector(`meta[name="${name}"]`);
        return el ? el.getAttribute("content") : "";
    }

    function getCsrf() {
        return getMeta("csrf-token") || "";
    }

    if (!window.cartAddUrl) {
        const fromMeta = getMeta("cart-add-url");
        if (fromMeta) window.cartAddUrl = fromMeta;
    }

    const DEFAULT_DURATION = 4200;

    function notify(options = {}) {
        const payload = {
            type: options.type || "info",
            title: options.title || "Thông báo",
            message: options.message || "",
            duration: options.duration || DEFAULT_DURATION,
        };
        if (window.showToast) {
            window.showToast(payload);
        } else {
            alert(payload.message || payload.title);
        }
    }

    function updateButtonState(btn, added, defaultText, addedText) {
        if (!btn) return;
        btn.classList.toggle("is-added", added);
        btn.dataset.inCart = added ? "1" : "0";
        btn.textContent = added ? addedText : defaultText;
    }

    function markProductButton(productId, added, btn, defaultText, addedText) {
        const helper = window.cartButtonHelper;
        if (helper?.mark) {
            helper.mark(productId, added);
            return;
        }
        updateButtonState(btn, added, defaultText, addedText);
    }

    async function addToCart(btn, productId) {
        if (!btn) return;
        const defaultText =
            btn.dataset.defaultText || btn.textContent.trim() || "Chọn mua";
        const addedText = btn.dataset.addedText || "Đã trong giỏ hàng";
        const helper = window.cartButtonHelper;
        const previouslyAdded =
            helper?.isInCart?.(productId) || btn.dataset.inCart === "1";

        try {
            btn.disabled = true;
            btn.textContent = "Đang thêm...";

            const res = await fetch(window.cartAddUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrf(),
                    Accept: "application/json",
                },
                body: JSON.stringify({ product_id: productId, qty: 1 }),
            });

            if (res.status === 401) {
                notify({
                    type: "warning",
                    title: "Cần đăng nhập",
                    message: "Vui lòng đăng nhập để tiếp tục mua hàng.",
                });
                const modal = document.getElementById("authModal");
                if (modal && window.bootstrap?.Modal) {
                    new bootstrap.Modal(modal).show();
                }
                return;
            }

            const data = await res.json().catch(() => ({}));
            if (!res.ok)
                throw new Error(
                    data?.message || "Có lỗi xảy ra khi thêm sản phẩm."
                );

            markProductButton(productId, true, btn, defaultText, addedText);

            notify({
                type: "success",
                title: "Đã thêm vào giỏ hàng",
                message:
                    data.message ||
                    "Sản phẩm vừa được thêm vào giỏ hàng của bạn.",
            });

            if (typeof data.cart_count !== "undefined") {
                const badge = document.getElementById("cart-count");
                if (badge) badge.textContent = data.cart_count;
            }
        } catch (err) {
            console.error(err);
            notify({
                type: "error",
                title: "Thất bại",
                message:
                    err.message || "Không thể thêm sản phẩm, vui lòng thử lại.",
            });
            if (!previouslyAdded) {
                markProductButton(
                    productId,
                    false,
                    btn,
                    defaultText,
                    addedText
                );
            }
        } finally {
            btn.disabled = false;
            const helper = window.cartButtonHelper;
            const inCartNow =
                helper?.isInCart?.(productId) || btn.dataset.inCart === "1";
            btn.textContent = inCartNow ? addedText : defaultText;
        }
    }

    function initPriceFilter() {
        const form = document.getElementById("priceFilterForm");
        if (!form) return;

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

        form.querySelectorAll('input[type="number"]').forEach((inp) => {
            inp.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }
            });
        });
    }

    window.getCsrf = getCsrf;
    window.addToCart = addToCart;

    document.addEventListener("DOMContentLoaded", () => {
        initPriceFilter();
        window.cartButtonHelper?.refresh?.();
    });
})();
