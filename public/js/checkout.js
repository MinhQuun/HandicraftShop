document.addEventListener("DOMContentLoaded", () => {
    const payCards = document.querySelectorAll(".pay-card");
    const mattInput = document.getElementById("MATT");
    const payError = document.getElementById("payError");
    const form = document.getElementById("checkoutForm");

    function selectCard(card) {
        payCards.forEach((c) => c.classList.remove("active"));
        card.classList.add("active");
        mattInput.value = card.dataset.matt;
        payError.style.display = "none";
    }

    payCards.forEach((card) => {
        card.addEventListener("click", () => selectCard(card));
    });

    if (payCards.length === 1) selectCard(payCards[0]);

    form.addEventListener("submit", (e) => {
        if (!mattInput.value) {
            e.preventDefault();
            payError.style.display = "block";
            document
                .querySelector(".pay-grid")
                .scrollIntoView({ behavior: "smooth" });
        }
    });

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
                    location.reload(); // Reload to update total
                }
            })
            .catch((err) => {
                promoMsg.textContent = "Lỗi kết nối.";
                promoMsg.classList.add("text-danger");
            });
    });
});