document.addEventListener("DOMContentLoaded", () => {
    const payCards = document.querySelectorAll(".pay-card");
    const mattInput = document.getElementById("MATT");
    const payError = document.getElementById("payError");
    const form = document.getElementById("checkoutForm");

    // Thẻ chứa QR code
    const qrCard = document.getElementById("payQRCode");
    const qrImage = document.getElementById("payQRImage");

    function selectCard(card) {
        // Bỏ active ở tất cả card
        payCards.forEach((c) => c.classList.remove("active"));
        // Gán active cho card được chọn
        card.classList.add("active");
        // Set giá trị MATT để submit form
        mattInput.value = card.dataset.matt;
        payError.style.display = "none";

        // Nếu phương thức là COD thì không hiện QR
        if (card.dataset.matt.toLowerCase() === "cod") {
            qrCard.style.display = "none";
        } else if (card.dataset.qr) {
            qrImage.src = card.dataset.qr;
            qrCard.style.display = "block";
        } else {
            qrCard.style.display = "none";
        }
    }

    // Gán sự kiện click cho từng card
    payCards.forEach((card) => {
        card.addEventListener("click", () => selectCard(card));
    });

    // Nếu chỉ có 1 phương thức thì auto chọn
    if (payCards.length === 1) selectCard(payCards[0]);

    // Kiểm tra trước khi submit
    form.addEventListener("submit", (e) => {
        if (!mattInput.value) {
            e.preventDefault();
            payError.style.display = "block";
            document
                .querySelector(".pay-grid")
                .scrollIntoView({ behavior: "smooth" });
        }
    });

    // Xử lý mã giảm giá
    const applyBtn = document.getElementById("apply_promo");
    const promoInput = document.getElementById("promo_code");
    const promoMsg = document.getElementById("promo_message");
    const totalEl = document.getElementById("total_price");

    applyBtn.addEventListener("click", () => {
        const code = promoInput.value.trim();
        if (!code) return;

        fetch("{{ route('orders.applyPromo') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
            },
            body: JSON.stringify({ promo_code: code }),
        })
            .then((res) => res.json())
            .then((data) => {
                promoMsg.textContent = data.message;
                promoMsg.classList.toggle("text-success", data.success);
                promoMsg.classList.toggle("text-danger", !data.success);

                if (data.success) {
                    location.reload(); // Reload để cập nhật tổng tiền
                }
            })
            .catch(() => {
                promoMsg.textContent = "Lỗi kết nối.";
                promoMsg.classList.add("text-danger");
            });
    });
});
