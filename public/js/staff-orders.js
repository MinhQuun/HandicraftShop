document.addEventListener("DOMContentLoaded", () => {
    // ==== FLASH MESSAGES ====
    (function flashToast() {
        const el = document.getElementById("flash");
        if (!el || typeof Swal === "undefined") return;
        const { success, error, info, warning } = el.dataset;
        const show = (icon, title, text) => Swal.fire({ icon, title, text: text || undefined, confirmButtonText: "OK" });
        if (error) return show("error", "Thất bại", error);
        if (success) return show("success", "Thành công", success);
        if (warning) return show("warning", "Chú ý", warning);
        if (info) return show("info", "Thông báo", info);
    })();

    // ==== CONFIRM DIALOGS ====
    function bindConfirm(selector, title, text) {
        document.querySelectorAll(selector).forEach((form) => {
            form.addEventListener("submit", function (e) {
                e.preventDefault();
                if (!window.Swal) return form.submit();
                Swal.fire({
                    icon: "warning",
                    title,
                    text,
                    showCancelButton: true,
                    confirmButtonText: "Đồng ý",
                    cancelButtonText: "Hủy",
                    reverseButtons: true,
                    focusCancel: true
                }).then((r) => r.isConfirmed && form.submit());
            });
        });
    }
    bindConfirm("form.form-confirm", "Xác nhận đơn hàng?", "Bạn có chắc chắn muốn xác nhận đơn hàng này không? Hệ thống sẽ tạo phiếu xuất.");
    bindConfirm("form.form-cancel", "Hủy đơn hàng?", "Bạn có chắc chắn muốn hủy đơn hàng này không?");

    // ==== HELPER FUNCTIONS ====
    const fmtVND = (n) => (n != null ? n.toLocaleString("vi-VN", { style: "currency", currency: "VND" }) : "—");
    const fmtNumber = (n) => (n != null ? n.toLocaleString("vi-VN") : "—"); // số nguyên, số lượng
    const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val ?? "—"; };
    const fmtTime = (s) => {
        if (!s) return "—";
        if (window.dayjs) return dayjs(s).format("DD/MM/YYYY HH:mm");
        const d = new Date(s);
        if (isNaN(d)) return s;
        return d.toLocaleString("vi-VN", { hour12: false, year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit" }).replace(",", "");
    };
    const buildShowUrl = (id) => (window.staff_order_show_url || "/staff/orders/__ID__").replace("__ID__", id);

    // ==== MODAL DETAIL ====
    const detailModal = document.getElementById("modalDetail");
    const bsDetail = detailModal ? new bootstrap.Modal(detailModal) : null;

    async function openDetail(id) {
        try {
            const res = await fetch(buildShowUrl(id));
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            setText("md_id", `#${data.MADONHANG}`);
            setText("md_customer", data.khachHang?.HOTEN);
            setText("md_address", data.diaChi?.DIACHI);
            setText("md_time", fmtTime(data.NGAYDAT));
            setText("md_payment", data.MAHTTHANHTOAN ?? "—");
            setText("md_note", data.GHICHU ?? "—");
            setText("md_tongtien", fmtVND(data.TONGTIEN));

            // Bảng chi tiết sản phẩm
            const tbody = detailModal.querySelector("#tblDetailLines tbody");
            tbody.innerHTML = "";
            data.chiTiets.forEach((ln, i) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${i + 1}</td>
                    <td>${ln.MASANPHAM}</td>
                    <td class="text-truncate" title="${ln.TENSP}">${ln.TENSP}</td>
                    <td class="text-end">${fmtNumber(ln.SOLUONG)}</td>
                    <td class="text-end">${fmtVND(ln.DONGIA)}</td>
                    <td class="text-end">${fmtVND(ln.THANHTIEN)}</td>
                `;
                tbody.appendChild(tr);
            });

            // Form confirm trong modal
            const formConfirm = document.getElementById("md_form_confirm");
            if (formConfirm) {
                formConfirm.action = buildShowUrl(`${data.MADONHANG}/confirm`);
                formConfirm.classList.toggle("d-none", !["Chờ xử lý", "Chờ thanh toán"].includes(data.TRANGTHAI));
            }

            bsDetail && bsDetail.show();
        } catch (e) {
            console.error(e);
            Swal.fire({ icon: "error", title: "Lỗi", text: "Không thể tải chi tiết đơn hàng." });
        }
    }

    // ==== CLICK ROW ĐỂ XEM CHI TIẾT ====
    // ==== CLICK BUTTON XEM CHI TIẾT ====
    document.querySelectorAll(".btn-detail").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            const id = btn.dataset.id;
            openDetail(id);
        });
    });

});
