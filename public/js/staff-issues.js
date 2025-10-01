document.addEventListener("DOMContentLoaded", () => {
    /* ------------------------- Flash messages ------------------------- */
    (function flashToast() {
        const el = document.getElementById("flash");
        if (!el || typeof Swal === "undefined") return;
        const { success, error, info, warning } = el.dataset;
        const show = (icon, title, text) =>
            Swal.fire({
                icon,
                title,
                text: text || undefined,
                confirmButtonText: "OK",
            });
        if (error) return show("error", "Thất bại", error);
        if (success) return show("success", "Thành công", success);
        if (warning) return show("warning", "Chú ý", warning);
        if (info) return show("info", "Thông báo", info);
    })();

    /* ------------------------- Confirm dialogs ------------------------ */
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
                    focusCancel: true,
                }).then((r) => r.isConfirmed && form.submit());
            });
        });
    }
    bindConfirm(
        "form.form-confirm",
        "Xác nhận phiếu xuất?",
        "Bạn có chắc chắn muốn xác nhận phiếu xuất này không? Tồn kho sẽ được trừ."
    );
    bindConfirm(
        "form.form-cancel",
        "Hủy phiếu xuất?",
        "Bạn có chắc chắn muốn hủy phiếu xuất này không? Nếu phiếu đã xác nhận, hệ thống sẽ hoàn lại tồn kho."
    );

    /* ------------------------------ Helpers --------------------------- */
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? "—";
    };

    const fmtTime = (s) => {
        if (window.dayjs) return dayjs(s).format("DD/MM/YYYY HH:mm");
        const d = new Date(s);
        if (isNaN(d)) return s ?? "—";
        return d
            .toLocaleString("vi-VN", {
                hour12: false,
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
            })
            .replace(",", "");
    };

    // format tiền VND
    const fmtVND = (n) =>
        (n ?? 0).toLocaleString("vi-VN", {
            style: "currency",
            currency: "VND",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });

    const buildShowUrl = (id) =>
        (window.staff_issue_show_url || "/staff/issues/__ID__").replace(
            "__ID__",
            id
        );

    /* -------------------------- Detail modal -------------------------- */
    const detailModal = document.getElementById("modalDetail");
    const bsDetail = detailModal ? new bootstrap.Modal(detailModal) : null;

    async function openDetail(id) {
        try {
            const res = await fetch(buildShowUrl(id));
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            // Map đúng key theo IssueController@show():
            const h = data.header || {};
            const lines = Array.isArray(data.lines) ? data.lines : [];

            setText("md_id", `#${h.MAPX ?? id}`);
            setText("md_customer", h.KHACHHANG ?? "—");
            setText("md_address", h.DIACHI ?? "—");
            setText("md_time", fmtTime(h.NGAYXUAT));
            setText("md_tongsl", (h.TONGSL ?? 0).toString());
            setText("md_tongtien", fmtVND(data.TONGTIEN ?? 0));

            const tbody = detailModal.querySelector("#tblDetailLines tbody");
            tbody.innerHTML = "";
            lines.forEach((ln, i) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
          <td>${i + 1}</td>
          <td>${ln.MASANPHAM}</td>
          <td class="text-truncate" title="${ln.TENSANPHAM || ""}">
            ${ln.TENSANPHAM || "—"}
          </td>
          <td class="text-end">${ln.SOLUONG ?? 0}</td>
          <td class="text-end">${fmtVND(ln.DONGIA ?? 0)}</td>
          <td class="text-end">${fmtVND(ln.THANHTIEN ?? 0)}</td>
        `;
                tbody.appendChild(tr);
            });

            // Nút "Xác nhận phiếu" trong modal
            const formConfirm = document.getElementById("md_form_confirm");
            if (formConfirm) {
                const confirmUrl = buildShowUrl(id) + "/confirm";
                formConfirm.action = confirmUrl;
                formConfirm.classList.toggle("d-none", h.TRANGTHAI !== "NHAP");
            }

            bsDetail && bsDetail.show();
        } catch (e) {
            console.error(e);
            if (window.Swal) {
                Swal.fire({
                    icon: "error",
                    title: "Lỗi",
                    text: "Không thể tải chi tiết phiếu xuất.",
                });
            }
        }
    }

    /* ---------------- Row click: mở chi tiết phiếu xuất --------------- */
    document.querySelectorAll(".row-detail").forEach((row) => {
        row.addEventListener("click", (e) => {
            if (
                e.target.closest("form") ||
                e.target.closest("button") ||
                e.target.closest("a") ||
                e.target.closest("select") ||
                e.target.closest("input") ||
                e.target.closest("[data-no-row-open]")
            ) {
                return;
            }
            const id = row.dataset.id;
            if (!id) return;
            openDetail(id);
        });
    });
});
