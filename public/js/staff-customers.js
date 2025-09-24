document.addEventListener("DOMContentLoaded", () => {
    // Đảm bảo dropdown filter đóng & không đè lên modal
    function blurFilterSelects() {
        document
            .querySelectorAll(
                ".customers-filter select, .customers-filter .form-select"
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
            blurFilterSelects();
            document.activeElement && document.activeElement.blur();
        });
        modalCreate.addEventListener("shown.bs.modal", () => {
            const nameInput = modalCreate.querySelector('input[name="HOTEN"]');
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
            const email = btn?.getAttribute("data-email") || "";
            const phone = btn?.getAttribute("data-phone") || "";
            // const hasUser = btn?.getAttribute("data-hasuser") === "1";

            editModal.querySelector("#e_name").value = name;
            editModal.querySelector("#e_email").value = email;
            editModal.querySelector("#e_phone").value = phone;

            // set form action
            const form = editModal.querySelector("#formEdit");
            const tpl = form.getAttribute("data-action-template") || "";
            form.action = tpl.replace(":id", id);

            // clear reset password inputs
            const pw = editModal.querySelector("#e_password");
            if (pw) pw.value = "";
            const pw2 = editModal.querySelector(
                'input[name="password_confirmation"]'
            );
            if (pw2) pw2.value = "";
        });
    }

    // ===== SweetAlert2 Confirm Delete =====
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
