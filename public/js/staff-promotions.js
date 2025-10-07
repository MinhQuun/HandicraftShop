document.addEventListener("DOMContentLoaded", () => {
    // ===== helper
    function blurFilterSelects() {
        document
            .querySelectorAll(
                ".promotions-filter select, .promotions-filter .form-select"
            )
            .forEach((el) => el.blur());
    }
    const hasSwal = !!window.Swal;

    const toggleByScope = (wrap, scope) => {
        wrap.querySelectorAll(".c-order-only,.e-order-only").forEach(
            (el) => (el.style.display = scope === "ORDER" ? "" : "none")
        );
        wrap.querySelectorAll(".c-product-only,.e-product-only").forEach(
            (el) => (el.style.display = scope === "PRODUCT" ? "" : "none")
        );
        // Voucher requires code; Product: code optional (nhưng server vẫn cần MAKHUYENMAI là PK)
    };

    // ===== Create Modal
    const modalCreate = document.getElementById("modalCreate");
    if (modalCreate) {
        modalCreate.addEventListener("show.bs.modal", () => {
            blurFilterSelects();
            document.activeElement && document.activeElement.blur();
            const scope = modalCreate.querySelector("#c_scope").value;
            toggleByScope(modalCreate, scope);
        });
        modalCreate.addEventListener("shown.bs.modal", () => {
            modalCreate
                .querySelector('input[name="MAKHUYENMAI"]')
                ?.focus({ preventScroll: true });
        });
        modalCreate
            .querySelector("#c_scope")
            ?.addEventListener("change", (e) => {
                toggleByScope(modalCreate, e.target.value);
            });
        // Auto uppercase code
        modalCreate.querySelector("#c_code")?.addEventListener("input", (e) => {
            e.target.value = e.target.value.toUpperCase().replace(/\s+/g, "");
        });
        // Quick client validate percent
        modalCreate.querySelector("#c_type")?.addEventListener("change", () => {
            const t = modalCreate.querySelector("#c_type").value;
            const v = modalCreate.querySelector("#c_value");
            v.min = 0;
            v.step = t === "Giảm %" ? 0.01 : 1000;
        });
    }

    // ===== Edit Modal
    const editModal = document.getElementById("modalEdit");
    if (editModal) {
        editModal.addEventListener("show.bs.modal", (evt) => {
            const btn = evt.relatedTarget;
            const id = btn?.getAttribute("data-id") || "";
            const name = btn?.getAttribute("data-name") || "";
            const type = btn?.getAttribute("data-type") || "";
            const discount = btn?.getAttribute("data-discount") || 0;
            const start = btn?.getAttribute("data-start") || "";
            const end = btn?.getAttribute("data-end") || "";
            const products =
                btn
                    ?.getAttribute("data-products")
                    ?.split(",")
                    .filter(Boolean) || [];
            const scope = btn?.getAttribute("data-scope") || "PRODUCT";
            const priority = btn?.getAttribute("data-priority") || 10;
            let rule = {};
            try {
                rule = JSON.parse(btn?.getAttribute("data-json") || "{}");
            } catch {}

            editModal.querySelector("#e_id").value = id;
            editModal.querySelector("#e_name").value = name;
            editModal.querySelector("#e_type").value = type;
            editModal.querySelector("#e_discount").value = discount;
            editModal.querySelector("#e_start").value = start;
            editModal.querySelector("#e_end").value = end;
            editModal.querySelector("#e_scope").value = scope;
            editModal.querySelector("#e_priority").value = priority;

            toggleByScope(editModal, scope);

            // voucher fields
            editModal.querySelector("#e_min").value =
                rule?.min_order_total || "";
            editModal.querySelector("#e_max").value = rule?.max_discount || "";
            editModal.querySelector("#e_non_stackable").checked =
                !!rule?.non_stackable;

            // product fields
            const selProducts = editModal.querySelector("#e_products");
            if (selProducts) {
                Array.from(selProducts.options).forEach((option) => {
                    option.selected = products.includes(option.value);
                });
            }
            // set action
            const form = editModal.querySelector("#formEdit");
            const tpl = form.getAttribute("data-action-template") || "";
            form.action = tpl.replace(":id", id);
        });
    }

    // ===== Confirm Delete
    document.querySelectorAll("form.form-delete").forEach((f) => {
        f.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!hasSwal) return f.submit();
            Swal.fire({
                title: "Xoá khuyến mãi?",
                text: "Thao tác này không thể hoàn tác.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Xoá",
                cancelButtonText: "Huỷ",
                reverseButtons: true,
                focusCancel: true,
            }).then((res) => {
                if (res.isConfirmed) f.submit();
            });
        });
    });

    // ===== Toast từ flash
    const flash = document.getElementById("flash");
    if (flash && hasSwal) {
        const msg =
            flash.dataset.success ||
            flash.dataset.error ||
            flash.dataset.info ||
            flash.dataset.warning;
        if (msg) {
            let icon = "success";
            if (flash.dataset.error) icon = "error";
            else if (flash.dataset.info) icon = "info";
            else if (flash.dataset.warning) icon = "warning";
            Swal.fire({
                toast: true,
                position: "top-end",
                icon,
                title: msg,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true,
            });
        }
    }
});