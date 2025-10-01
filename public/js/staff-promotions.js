document.addEventListener("DOMContentLoaded", () => {
    // Đảm bảo dropdown filter đóng & không đè lên modal
    function blurFilterSelects() {
        document
            .querySelectorAll(
                ".promotions-filter select, .promotions-filter .form-select"
            )
            .forEach((el) => el.blur());
    }

    const btnCreate = document.querySelector('[data-bs-target="#modalCreate"]');
    if (btnCreate) btnCreate.addEventListener("click", blurFilterSelects);

    const modalCreate = document.getElementById("modalCreate");
    if (modalCreate) {
        modalCreate.addEventListener("show.bs.modal", () => {
            blurFilterSelects();
            document.activeElement && document.activeElement.blur();
        });
        modalCreate.addEventListener("shown.bs.modal", () => {
            const idInput = modalCreate.querySelector(
                'input[name="MAKHUYENMAI"]'
            );
            if (idInput) idInput.focus({ preventScroll: true });
        });
    }

    // ===== Modal Edit: đổ dữ liệu + set action =====
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
                btn?.getAttribute("data-products")?.split(",") || [];

            editModal.querySelector("#e_id").value = id;
            editModal.querySelector("#e_name").value = name;
            editModal.querySelector("#e_type").value = type;
            editModal.querySelector("#e_discount").value = discount;
            editModal.querySelector("#e_start").value = start;
            editModal.querySelector("#e_end").value = end;

            const selProducts = editModal.querySelector("#e_products");
            if (selProducts) {
                Array.from(selProducts.options).forEach((option) => {
                    option.selected = products.includes(option.value);
                });
            }

            const form = editModal.querySelector("#formEdit");
            const tpl = form.getAttribute("data-action-template") || "";
            form.action = tpl.replace(":id", id);
        });
    }

    // ===== SweetAlert2 Confirm Delete =====
    document.querySelectorAll("form.form-delete").forEach((f) => {
        f.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!window.Swal) return f.submit();
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

    // ===== Toast từ flash session =====
    const flash = document.getElementById("flash");
    if (flash && window.Swal) {
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