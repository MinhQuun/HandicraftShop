document.addEventListener("DOMContentLoaded", () => {
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
    }

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
