document.addEventListener("DOMContentLoaded", () => {
    // Đảm bảo dropdown filter đóng & không đè lên modal
    function blurFilterSelects() {
        document
            .querySelectorAll(
                ".products-filter select, .products-filter .form-select"
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
            const nameInput = modalCreate.querySelector(
                'input[name="TENSANPHAM"]'
            );
            if (nameInput) nameInput.focus({ preventScroll: true });
        });
    }

    // ===== Modal Edit: đổ dữ liệu + set action =====
    const editModal = document.getElementById("modalEdit");
    if (editModal) {
        editModal.addEventListener("show.bs.modal", (evt) => {
            const btn = evt.relatedTarget;
            const id = btn?.getAttribute("data-id");
            const name = btn?.getAttribute("data-name") || "";
            const price = btn?.getAttribute("data-price") || 0;
            const stock = btn?.getAttribute("data-stock") || 0;
            const cat = btn?.getAttribute("data-category") || "";
            const sup = btn?.getAttribute("data-supplier") || "";
            const desc = btn?.getAttribute("data-desc") || "";
            const img = btn?.getAttribute("data-image") || "";

            editModal.querySelector("#e_name").value = name;
            editModal.querySelector("#e_price").value = price;
            editModal.querySelector("#e_stock").value = stock;

            const selCat = editModal.querySelector("#e_category");
            if (selCat && cat !== "") selCat.value = cat;

            const selSup = editModal.querySelector("#e_supplier");
            if (selSup) selSup.value = sup;

            const descEl = editModal.querySelector("#e_desc");
            if (descEl) descEl.value = desc;

            // Preview ảnh hiện tại (nếu có)
            const preview = editModal.querySelector("#e_preview");
            const label = editModal.querySelector("#e_imgname");

            if (img) {
                let src = img.trim();
                if (/^https?:\/\//i.test(src)) {
                    // full URL, giữ nguyên
                } else if (
                    src.startsWith("/assets/") ||
                    src.startsWith("assets/")
                ) {
                    src = "/" + src.replace(/^\/+/, "");
                } else {
                    // chỉ tên file => dùng public/assets/images
                    const BASE = window.APP_IMAGE_BASE || "/assets/images/";
                    src = BASE.replace(/\/+$/, "/") + src.replace(/^\/+/, "");
                }
                if (preview) {
                    preview.src = src;
                    preview.style.display = "inline-block";
                }
                if (label) label.textContent = src.split("/").pop();
            } else {
                if (preview) {
                    preview.style.display = "none";
                    preview.src = "";
                }
                if (label) label.textContent = "";
            }

            const form = editModal.querySelector("#formEdit");
            const tpl = form.getAttribute("data-action-template") || "";
            form.action = tpl.replace(":id", id);
        });

        // Thay ảnh -> update preview
        const inputImg = editModal.querySelector("#e_image");
        if (inputImg) {
            inputImg.addEventListener("change", (e) => {
                const file = e.target.files?.[0];
                const preview = editModal.querySelector("#e_preview");
                const label = editModal.querySelector("#e_imgname");
                if (file && preview) {
                    preview.src = URL.createObjectURL(file);
                    preview.style.display = "inline-block";
                    if (label) label.textContent = file.name;
                }
            });
        }
    }

    // ===== SweetAlert2 Confirm Delete =====
    document.querySelectorAll("form.form-delete").forEach((f) => {
        f.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!window.Swal) return f.submit();
            Swal.fire({
                title: "Xoá sản phẩm?",
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
