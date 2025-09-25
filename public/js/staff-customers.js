document.addEventListener("DOMContentLoaded", () => {
    // ===== Utilities
    const $doc = document.documentElement;
    const hasBootstrap = typeof window.bootstrap !== "undefined";

    function blurFilterSelects() {
        document
            .querySelectorAll(
                ".customers-filter select, .customers-filter .form-select"
            )
            .forEach((el) => el.blur());
    }

    // ===== Nút "Thêm mới" → đóng dropdown filter trước khi mở modal
    const btnCreate = document.querySelector('[data-bs-target="#modalCreate"]');
    if (btnCreate) {
        btnCreate.addEventListener("click", blurFilterSelects);
    }

    // ===== Modal Create
    const modalCreate = document.getElementById("modalCreate");
    if (modalCreate) {
        modalCreate.addEventListener("show.bs.modal", () => {
            blurFilterSelects();
            document.activeElement && document.activeElement.blur();
        });
        modalCreate.addEventListener("shown.bs.modal", () => {
            const nameInput = modalCreate.querySelector('input[name="HOTEN"]');
            if (nameInput) nameInput.focus({ preventScroll: true });
        });
    }

    // ===== Modal Edit
    const editModal = document.getElementById("modalEdit");
    if (editModal) {
        editModal.addEventListener("show.bs.modal", (evt) => {
            const form = editModal.querySelector("#formEdit");
            const tpl = form?.getAttribute("data-action-template") || "";

            // Nếu mở từ nút "Sửa" trên bảng (có relatedTarget) → đổ dữ liệu từ data-*
            if (evt.relatedTarget) {
                const btn = evt.relatedTarget;
                const id = btn.getAttribute("data-id");
                const name = btn.getAttribute("data-name") || "";
                const email = btn.getAttribute("data-email") || "";
                const phone = btn.getAttribute("data-phone") || "";

                // Chỉ set value khi mở từ button; nếu mở vì lỗi validate,
                // các giá trị old() đã được Blade render sẵn, không đè lên.
                const iName = editModal.querySelector("#e_name");
                const iEmail = editModal.querySelector("#e_email");
                const iPhone = editModal.querySelector("#e_phone");
                if (iName) iName.value = name;
                if (iEmail) iEmail.value = email;
                if (iPhone) iPhone.value = phone;

                if (form && tpl) form.action = tpl.replace(":id", id);

                // Clear 2 ô mật khẩu
                const pw = editModal.querySelector("#e_password");
                const pw2 = editModal.querySelector(
                    'input[name="password_confirmation"]'
                );
                if (pw) pw.value = "";
                if (pw2) pw2.value = "";
            } else {
                // Nếu mở vì lỗi validate (không có relatedTarget),
                // đặt action theo editing_id mà Blade đã set vào dataset
                const editId = $doc.dataset.editId || "";
                if (form && tpl && editId)
                    form.action = tpl.replace(":id", editId);
            }
        });
    }

    // ===== SweetAlert2 Confirm Delete
    document.querySelectorAll("form.form-delete").forEach((f) => {
        f.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!window.Swal) return f.submit();

            Swal.fire({
                title: "Xoá khách hàng?",
                text: "Nếu khách đã có đơn hàng, thao tác sẽ bị từ chối.",
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

    // ===== Toast từ flash session
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

    // ===== Tự mở lại đúng modal khi có lỗi validate (server-side)
    if (hasBootstrap && document.querySelector(".modal")) {
        const errCount = parseInt($doc.dataset.laravelErrors || "0", 10);
        const whichForm = $doc.dataset.laravelForm || "";
        if (errCount > 0) {
            if (whichForm === "create") {
                const m = document.getElementById("modalCreate");
                if (m) new bootstrap.Modal(m).show();
            } else if (whichForm === "edit") {
                const m = document.getElementById("modalEdit");
                if (m) new bootstrap.Modal(m).show();
            }
        }
    }
});
