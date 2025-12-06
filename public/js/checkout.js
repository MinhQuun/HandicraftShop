document.addEventListener("DOMContentLoaded", () => {
    /* ================= Payment method & QR ================= */

    const payCards = document.querySelectorAll(".pay-card");
    const mattInput = document.getElementById("MATT");
    const payError = document.getElementById("payError");
    const form = document.getElementById("checkoutForm");

    const qrCard = document.getElementById("payQRCode");
    const qrImage = document.getElementById("payQRImage");

    function selectCard(card) {
        payCards.forEach((c) => c.classList.remove("active"));
        card.classList.add("active");
        if (mattInput) mattInput.value = card.dataset.matt || "";
        if (payError) payError.style.display = "none";

        const code = (card.dataset.matt || "").toLowerCase();
        if (code === "cod") {
            if (qrCard) qrCard.style.display = "none";
        } else if (card.dataset.qr && qrImage && qrCard) {
            qrImage.src = card.dataset.qr;
            qrCard.style.display = "block";
        } else if (qrCard) {
            qrCard.style.display = "none";
        }
    }

    payCards.forEach((card) => {
        card.addEventListener("click", () => selectCard(card));
    });

    if (payCards.length === 1) selectCard(payCards[0]);

    if (form) {
        form.addEventListener("submit", (e) => {
            if (!mattInput || !mattInput.value) {
                e.preventDefault();
                if (payError) payError.style.display = "block";
                document
                    .querySelector(".pay-grid")
                    ?.scrollIntoView({ behavior: "smooth" });
            }
        });
    }

    /* ================= Voucher / Promo ================= */

    const applyBtn = document.getElementById("apply_promo");
    const promoInput = document.getElementById("promo_code");
    const promoMsg = document.getElementById("promo_message");

    const subtotalEl = document.getElementById("subtotal_value");
    const discountRow = document.getElementById("discount_row");
    const discountEl = document.getElementById("discount_value");
    const discountCodeText = document.getElementById("discount_code_text");
    const totalEl = document.getElementById("total_value");

    const totalPriceSingleEl = document.getElementById("total_price");

    const voucherWrap = document.getElementById("voucher_applied");
    const voucherCode = document.getElementById("voucher_code");
    const voucherDesc = document.getElementById("voucher_desc");

    const fmtVND = (n) => (Number(n) || 0).toLocaleString("vi-VN") + " VNĐ";
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";

    let isApplying = false;

    async function applyPromo() {
        if (isApplying) return;
        const code = (promoInput?.value || "").trim().toUpperCase();
        const url = applyBtn?.dataset?.url || "";
        if (!code || !url) {
            if (promoMsg)
                promoMsg.textContent = !code
                    ? "Vui lòng nhập mã khuyến mãi."
                    : "Thiếu URL áp mã.";
            return;
        }

        isApplying = true;
        if (applyBtn) {
            applyBtn.disabled = true;
            applyBtn.dataset.originalText = applyBtn.innerHTML;
            applyBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Đang áp dụng';
        }

        if (promoMsg) {
            promoMsg.textContent = "Đang kiểm tra mã...";
            promoMsg.classList.remove("text-success", "text-danger");
        }

        try {
            const res = await fetch(url, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({ promo_code: code }),
            });

            const data = await res.json();

            if (promoMsg) {
                promoMsg.textContent =
                    data?.message ||
                    (res.ok ? "Áp dụng mã thành công!" : "Mã không hợp lệ.");
                promoMsg.classList.toggle("text-success", !!data?.success);
                promoMsg.classList.toggle("text-danger", !data?.success);
            }

            if (!res.ok || !data?.success) {
                if (voucherWrap) voucherWrap.style.display = "none";
                if (discountRow) discountRow.classList.add("d-none");

                if (typeof data?.subtotal === "number" && subtotalEl)
                    subtotalEl.textContent = fmtVND(data.subtotal);
                if (typeof data?.total === "number") {
                    if (totalEl) totalEl.textContent = fmtVND(data.total);
                    else if (totalPriceSingleEl)
                        totalPriceSingleEl.textContent =
                            "Tổng thành tiền: " + fmtVND(data.total);
                }
                isApplying = false;
                if (applyBtn && applyBtn.dataset.originalText) {
                    applyBtn.innerHTML = applyBtn.dataset.originalText;
                    applyBtn.disabled = false;
                }
                return;
            }

            if (voucherWrap) voucherWrap.style.display = "";
            if (voucherCode) voucherCode.textContent = data.code || code;
            if (discountCodeText) discountCodeText.textContent = data.code || code;

            if (voucherDesc) {
                const typeText =
                    data.type === "percent"
                        ? data.value + "%"
                        : (Number(data.value) || 0).toLocaleString("vi-VN") +
                          "đ";
                const minText =
                    Number(data.min_total) > 0
                        ? " • Tối thiểu " +
                          Number(data.min_total).toLocaleString("vi-VN") +
                          "đ"
                        : "";
                const capText =
                    Number(data.max_discount) > 0
                        ? " • Tối đa " +
                          Number(data.max_discount).toLocaleString("vi-VN") +
                          "đ"
                        : "";
                voucherDesc.textContent = `(${typeText}${minText}${capText})`;
            }

            if (typeof data.subtotal === "number" && subtotalEl)
                subtotalEl.textContent = fmtVND(data.subtotal);
            if (typeof data.discount === "number") {
                if (discountEl)
                    discountEl.textContent = "- " + fmtVND(data.discount);
                if (discountRow) discountRow.classList.remove("d-none");
            }
            if (typeof data.total === "number") {
                if (totalEl) totalEl.textContent = fmtVND(data.total);
                else if (totalPriceSingleEl)
                    totalPriceSingleEl.textContent =
                        "Tổng thành tiền: " + fmtVND(data.total);
            }

            document
                .getElementById("total_value")
                ?.scrollIntoView({ behavior: "smooth", block: "center" });

            isApplying = false;
            if (applyBtn && applyBtn.dataset.originalText) {
                applyBtn.innerHTML = applyBtn.dataset.originalText;
                applyBtn.disabled = false;
            }
        } catch (e) {
            console.error(e);
            if (promoMsg) {
                promoMsg.textContent = "Lỗi kết nối.";
                promoMsg.classList.add("text-danger");
            }
            isApplying = false;
            if (applyBtn && applyBtn.dataset.originalText) {
                applyBtn.innerHTML = applyBtn.dataset.originalText;
                applyBtn.disabled = false;
            }
        }
    }

    if (applyBtn) {
        applyBtn.addEventListener("click", applyPromo);
        promoInput?.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                applyPromo();
            }
        });
    }
});
