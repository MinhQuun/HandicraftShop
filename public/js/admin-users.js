document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalEdit");
    if (!modal) return;

    modal.addEventListener("show.bs.modal", (evt) => {
        const btn = evt.relatedTarget;
        const id = btn?.getAttribute("data-id");
        const name = btn?.getAttribute("data-name");
        const email = btn?.getAttribute("data-email");
        const phone = btn?.getAttribute("data-phone");

        modal.querySelector("#e_name").value = name || "";
        modal.querySelector("#e_email").value = email || "";
        modal.querySelector("#e_phone").value = phone || "";

        const form = modal.querySelector("#formEdit");
        form.action = `/admin/users/${id}`;
    });
});
document.querySelectorAll(".role-cell form").forEach((f) => {
    const wrap = f.querySelector(".role-wrap");
    const sel = f.querySelector(".role-select");
    const init = sel.value;
    const lock = sel.dataset.lock; // 'admin' hoặc ''

    sel.addEventListener("change", () => {
        // Nếu là admin: không cho đổi, báo lỗi và reset lại
        if (lock === "admin" && sel.value !== init) {
            alert("Bạn không thể thay đổi quyền của tài khoản Admin.");
            sel.value = init; // trả về giá trị ban đầu
            wrap.classList.remove("show-save");
            return;
        }

        // Hợp lệ: hiện nút Lưu và nhắc người dùng
        if (sel.value !== init) {
            wrap.classList.add("show-save");
            alert('Bạn đã thay đổi quyền. Nhấn "Lưu" để cập nhật.');
        } else {
            wrap.classList.remove("show-save");
        }
    });
});
