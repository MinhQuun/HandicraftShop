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
    if (btnCreate) {
        btnCreate.addEventListener("click", blurFilterSelects);
    }

    const modalCreate = document.getElementById("modalCreate");
    if (modalCreate) {
        modalCreate.addEventListener("show.bs.modal", () => {
            // Đóng bất kỳ select nào đang mở phía sau
            blurFilterSelects();
            // Tránh focus vào <select> khiến dropdown tự bật
            document.activeElement && document.activeElement.blur();
        });

        modalCreate.addEventListener("shown.bs.modal", () => {
            // Chủ động focus vào ô tên sản phẩm
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
            const img = btn?.getAttribute("data-image") || "";

            editModal.querySelector("#e_name").value = name;
            editModal.querySelector("#e_price").value = price;
            editModal.querySelector("#e_stock").value = stock;

            const sel = editModal.querySelector("#e_category");
            if (sel && cat !== "") sel.value = cat;

            // Preview ảnh hiện tại (nếu có)
            const preview = editModal.querySelector("#e_preview");
            const label = editModal.querySelector("#e_imgname");

            if (img) {
                let src = img.trim();

                if (/^https?:\/\//i.test(src)) {
                    // ảnh full URL
                } else if (
                    src.startsWith("/assets/") ||
                    src.startsWith("assets/")
                ) {
                    src = "/" + src.replace(/^\/+/, "");
                } else {
                    // chỉ là tên file => dùng thư mục public/assets/images
                    const BASE = window.APP_IMAGE_BASE || "/assets/images/";
                    src = BASE.replace(/\/+$/, "/") + src.replace(/^\/+/, "");
                }

                preview.src = src;
                preview.style.display = "inline-block";
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
