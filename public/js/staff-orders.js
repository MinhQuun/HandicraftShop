document.addEventListener("DOMContentLoaded", () => {
    // ==== FLASH MESSAGES ====
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

    // ==== HELPERS ====
    const fmtVND = (n) =>
        n != null
            ? n.toLocaleString("vi-VN", { style: "currency", currency: "VND" })
            : "—";
    const fmtNumber = (n) => (n != null ? n.toLocaleString("vi-VN") : "—");
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? "—";
    };
    const fmtTime = (s) => {
        if (!s) return "—";
        if (window.dayjs) return dayjs(s).format("DD/MM/YYYY HH:mm");
        const d = new Date(s);
        if (isNaN(d)) return s;
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
    const buildShowUrl = (id) =>
        (window.staff_order_show_url || "/staff/orders/__ID__").replace(
            "__ID__",
            id
        );
    const buildUpdateUrl = (id) =>
        (
            window.staff_order_update_url || "/staff/orders/__ID__/status"
        ).replace("__ID__", id);

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
            setText("md_payment", data.MAHTTHANHTOAN ?? data.MATT ?? "—");
            setText("md_note", data.GHICHU ?? "—");
            setText("md_tongtien", fmtVND(data.TONGTHANHTIEN ?? data.TONGTIEN));

            // ====== Chi tiết sản phẩm ======
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

            // ====== Khuyến mãi ======
            const promoEl = document.getElementById("md_promotion");
            if (promoEl) {
                if (data.khuyenMai) {
                    let discountAmount = data.TIEN_GIAM ?? 0; // Tiền giảm đã tính sẵn trong controller
                    promoEl.innerHTML = `
                        Mã: ${data.khuyenMai.MAKHUYENMAI} 
                        Tiền giảm: ${fmtVND(discountAmount)}
                    `;
                } else {
                    promoEl.textContent = "—";
                }
            }


            // ====== Nút "Hoàn thành & tạo PX" ======
            // Inject promotion display (product-level + voucher)
            try {
                const promoEl2 = document.getElementById("md_promotion");
                if (promoEl2) {
                    let productSaveSum = 0;
                    for (const ln of (data.chiTiets || [])) {
                        let orig = (typeof ln.GIABAN === 'number') ? ln.GIABAN : null;
                        if (orig == null) {
                            try {
                                const r = await fetch((window.apiProductPriceTmpl || '/api/products/__ID__/price').replace('__ID__', ln.MASANPHAM));
                                if (r.ok) {
                                    const j = await r.json();
                                    if (typeof j.GIABAN === 'number') orig = j.GIABAN;
                                }
                            } catch {}
                        }
                        const unit = Number(ln.DONGIA || 0);
                        const qty  = Number(ln.SOLUONG || 0);
                        if (typeof orig !== 'number' || isNaN(orig)) orig = unit;
                        const save = Math.max(0, orig - unit);
                        productSaveSum += save * qty;
                    }
                    const voucherTxt = data.khuyenMai ? ("Mã: " + data.khuyenMai.MAKHUYENMAI + " (−" + fmtVND(Number(data.TIEN_GIAM||0)) + ")") : '';
                    const prodTxt = productSaveSum > 0 ? ("KM sản phẩm: −" + fmtVND(productSaveSum)) : '';
                    const finalTxt = [prodTxt, voucherTxt].filter(Boolean).join(' | ');
                    if (finalTxt) promoEl2.textContent = finalTxt;
                }
            } catch {}

            const formConfirm = document.getElementById("md_form_confirm");
            if (formConfirm) {
                formConfirm.action = buildUpdateUrl(data.MADONHANG);
                formConfirm.classList.toggle(
                    "d-none",
                    ["Hoàn thành", "Hủy"].includes(data.TRANGTHAI)
                );
                formConfirm.addEventListener(
                    "submit",
                    (e) => {
                        if (!window.Swal) return;
                        e.preventDefault();
                        Swal.fire({
                            icon: "question",
                            title: "Đánh dấu Hoàn thành?",
                            text: "Hệ thống sẽ kiểm kho và tạo Phiếu Xuất.",
                            showCancelButton: true,
                            confirmButtonText: "Xác nhận",
                            cancelButtonText: "Hủy",
                            reverseButtons: true,
                            focusCancel: true,
                        }).then((r) => r.isConfirmed && formConfirm.submit());
                    },
                    { once: true }
                );
            }

            bsDetail && bsDetail.show();
        } catch (e) {
            console.error(e);
            window.Swal &&
                Swal.fire({
                    icon: "error",
                    title: "Lỗi",
                    text: "Không thể tải chi tiết đơn hàng.",
                });
        }
    }

    // ==== Nút xem chi tiết ====
    document.querySelectorAll(".btn-detail").forEach((btn) => {
        btn.addEventListener("click", () => openDetail(btn.dataset.id));
    });

    // ==== LƯU TRẠNG THÁI Ở DÒNG BẢNG ====
    // Với mỗi dòng, lấy giá trị từ combobox và gửi lên updateStatus
    // ==== LƯU TRẠNG THÁI Ở DÒNG BẢNG ====
    document.querySelectorAll("tr.row-detail").forEach((tr) => {
        const select = tr.querySelector("select.sel-status"); // combobox
        const form = tr.querySelector("form.form-update-status"); // form PUT /status
        const btn = tr.querySelector(".btn-save-status"); // nút Lưu
        if (!select || !form || !btn) return;

        // Lần cuối đã lưu (mặc định = giá trị lúc load trang)
        let lastSaved = select.dataset.current || select.value;

        // 1) Ẩn nút lưu lúc đầu
        btn.style.display = "none";

        // 2) Khi combobox đổi -> nếu khác lastSaved thì hiện nút, ngược lại ẩn
        select.addEventListener("change", () => {
            if (select.value !== lastSaved) {
                btn.style.display = "inline-block";
                btn.disabled = false;
            } else {
                btn.style.display = "none";
            }
        });

        // 3) Bấm Lưu -> set hidden input, submit, ẩn/disable nút (tránh double-click)
        btn.addEventListener("click", (e) => {
            e.preventDefault();

            // chỉ cho submit khi đã thay đổi
            if (select.value === lastSaved) {
                btn.style.display = "none";
                return;
            }

            // đổ value vào input hidden "status"
            const hidden = form.querySelector('input[name="status"]');
            if (hidden) hidden.value = select.value;

            // Xác nhận nhẹ (tuỳ thích)
            const doSubmit = () => {
                // Ẩn nút ngay khi submit
                btn.disabled = true;
                btn.style.display = "none";
                lastSaved = select.value; // cập nhật mốc đã lưu

                form.submit(); // form PUT -> server xử lý & reload trang
            };

            if (window.Swal) {
                Swal.fire({
                    icon: "question",
                    title: "Cập nhật trạng thái?",
                    text: `Chuyển đơn sang: "${select.value}".`,
                    showCancelButton: true,
                    confirmButtonText: "Lưu",
                    cancelButtonText: "Hủy",
                    reverseButtons: true,
                    focusCancel: true,
                }).then((r) => r.isConfirmed && doSubmit());
            } else {
                doSubmit();
            }
        });
    });
});
