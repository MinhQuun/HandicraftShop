(function () {
    const toId = (value) => {
        if (value === undefined || value === null) return "";
        return String(value).trim();
    };

    const escapeAttr = (value) => {
        if (window.CSS?.escape) return window.CSS.escape(value);
        return String(value).replace(/["\\]/g, "\\$&");
    };

    const initial = Array.isArray(window.initialCartItems)
        ? window.initialCartItems.map(toId).filter(Boolean)
        : [];

    const cartState = new Set(initial);

    function ensureDefaults(btn) {
        if (!btn.dataset.defaultText) {
            btn.dataset.defaultText =
                (btn.textContent || "").trim() || "Chọn mua";
        }
        if (!btn.dataset.addedText) {
            btn.dataset.addedText = "Đã trong giỏ hàng";
        }
    }

    function applyState(btn, added) {
        ensureDefaults(btn);
        btn.dataset.inCart = added ? "1" : "0";
        btn.classList.toggle("is-added", !!added);
        const targetText = added
            ? btn.dataset.addedText
            : btn.dataset.defaultText;
        btn.textContent = targetText;
    }

    function mark(productId, added = true) {
        const id = toId(productId);
        if (!id) return;
        if (added) cartState.add(id);
        else cartState.delete(id);

        const selector = `[data-product-id="${escapeAttr(id)}"]`;
        document
            .querySelectorAll(selector)
            .forEach((btn) => applyState(btn, added));
    }

    function refresh() {
        document.querySelectorAll("[data-product-id]").forEach((btn) => {
            const id = toId(btn.dataset.productId);
            if (!id) return;
            const datasetState = btn.dataset.inCart === "1";
            const shouldMark = datasetState || cartState.has(id);
            if (shouldMark) cartState.add(id);
            applyState(btn, shouldMark);
        });
    }

    function register(btn) {
        if (!btn || !btn.dataset) return;
        const id = toId(btn.dataset.productId);
        if (!id) return;
        const inCart = cartState.has(id) || btn.dataset.inCart === "1";
        applyState(btn, inCart);
    }

    window.cartButtonHelper = {
        mark,
        refresh,
        register,
        isInCart: (id) => cartState.has(toId(id)),
    };

    document.addEventListener("DOMContentLoaded", refresh);
})();
