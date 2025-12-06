document.addEventListener("DOMContentLoaded", () => {
    const phoneRegex = /^0\d{9}$/;

    const setErr = (input, message) => {
        if (!input) return;
        input.classList.toggle("is-invalid", !!message);
        let fb = input.nextElementSibling;
        if (!fb || !fb.classList.contains("invalid-feedback")) {
            fb = document.createElement("div");
            fb.className = "invalid-feedback";
            input.insertAdjacentElement("afterend", fb);
        }
        fb.textContent = message || "";
    };

    function validateSupplierForm(form) {
        const name = form.querySelector('input[name="TENNHACUNGCAP"]');
        const phone = form.querySelector('input[name="DTHOAI"]');
        const addr = form.querySelector('textarea[name="DIACHI"]');
        let ok = true;

        if (name) {
            const v = name.value.trim();
            const msg = v.length < 2 ? "Tên tối thiểu 2 ký tự." : "";
            setErr(name, msg);
            if (msg) ok = false;
        }
        if (phone) {
            const v = phone.value.trim();
            const msg = v && !phoneRegex.test(v) ? "Số điện thoại 10 số, bắt đầu bằng 0." : "";
            setErr(phone, msg);
            if (msg) ok = false;
        }
        if (addr) {
            const v = addr.value.trim();
            const msg = v.length < 5 ? "Vui lòng nhập địa chỉ chi tiết (≥5 ký tự)." : "";
            setErr(addr, msg);
            if (msg) ok = false;
        }
        return ok;
    }

    // ================= Modal Edit: đổ dữ liệu + set action =================
    const editModal = document.getElementById("modalEdit");
    if (editModal) {
        editModal.addEventListener("show.bs.modal", (evt) => {
            const btn = evt.relatedTarget;
            const id = btn?.getAttribute("data-id");
            const name = btn?.getAttribute("data-name") || "";
            const phone = btn?.getAttribute("data-phone") || "";
            const addr = btn?.getAttribute("data-address") || "";

            editModal.querySelector("#e_name").value = name;
            editModal.querySelector("#e_phone").value = phone;
            editModal.querySelector("#e_address").value = addr;

            const form = editModal.querySelector("#formEdit");
            const updateTemplate =
                form.getAttribute("data-action-template") ||
                window.STAFF_SUPPLIER_UPDATE_ROUTE ||
                ""; // fallback nếu bạn muốn gắn global
            // Nếu không dùng global, dùng cách generate route từ Blade:
            // Đặt sẵn action mẫu bằng data-attr trong Blade:
            // form.setAttribute('data-action-template', '{{ route('staff.suppliers.update', ':id') }}');

            const actionTpl =
                updateTemplate ||
                form.getAttribute("action") ||
                '{{ route("staff.suppliers.update", ":id") }}'; // với Laravel Mix bạn có thể inline ở Blade

            form.action = actionTpl.replace(":id", id);
        });

        editModal.querySelector("form")?.addEventListener("submit", (e) => {
            if (!validateSupplierForm(e.target)) e.preventDefault();
        });
    }

    // Validate create form
    document
        .querySelector('#modalCreate form')
        ?.addEventListener("submit", (e) => {
            if (!validateSupplierForm(e.target)) e.preventDefault();
        });

    // ================= SweetAlert2 Confirm Delete =================
    document.querySelectorAll("form.form-delete").forEach((f) => {
        f.addEventListener("submit", function (e) {
            e.preventDefault();

            Swal.fire({
                title: "Xoá nhà cung cấp?",
                text: "Thao tác này không thể hoàn tác.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Xoá",
                cancelButtonText: "Huỷ",
                reverseButtons: true,
                focusCancel: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    f.submit();
                }
            });
        });
    });

    // ================= Toast từ flash session =================
    const flash = document.getElementById("flash");
    if (flash) {
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
                icon: icon,
                title: msg,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true,
            });
        }
    }
});
