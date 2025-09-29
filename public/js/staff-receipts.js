document.addEventListener("DOMContentLoaded", () => {
    // Flash messages
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

    // Confirm dialogs
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
                    cancelButtonText: "Huỷ",
                    reverseButtons: true,
                    focusCancel: true,
                }).then((r) => r.isConfirmed && form.submit());
            });
        });
    }
    bindConfirm(
        "form.form-cancel",
        "Huỷ phiếu nhập?",
        "Bạn có chắc chắn muốn hủy phiếu nhập này không? Nếu phiếu đã xác nhận, hệ thống sẽ trừ lại tồn kho."
    );
    bindConfirm(
        "form.form-confirm",
        "Xác nhận phiếu nhập?",
        "Bạn có chắc chắn muốn xác nhận phiếu nhập này không? Tồn kho sẽ được cộng thêm."
    );
    bindConfirm(
        "form.form-delete",
        "Xoá phiếu nhập?",
        "Chỉ xoá được phiếu ở trạng thái NHAP và chưa có chi tiết."
    );

    // Helpers
    const fmtVND = (n) => (n || 0).toLocaleString("vi-VN");
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val ?? "";
    };
    const fmtTime = (s) => {
        if (window.dayjs) return dayjs(s).format("DD/MM/YYYY HH:mm");
        const d = new Date(s);
        if (isNaN(d)) return s ?? "";
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
        (window.staff_receipt_show_url || "/staff/receipts/__ID__").replace(
            "__ID__",
            id
        );

    // Detail modal
    const detailModal = document.getElementById("modalDetail");
    const bsDetail = detailModal ? new bootstrap.Modal(detailModal) : null;

    async function openDetail(id) {
        try {
            const res = await fetch(buildShowUrl(id));
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const { header, lines, TONGTIEN } = await res.json();

            setText("md_id", `#${header.MAPN}`);
            setText("md_ncc", header.TENNHACUNGCAP);
            setText("md_nv", header.NHANVIEN);
            setText("md_time", fmtTime(header.NGAYNHAP));
            setText("md_ghichu", header.GHICHU || "—");

            const tbody = detailModal.querySelector("#tblDetailLines tbody");
            tbody.innerHTML = "";
            lines.forEach((ln, i) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
          <td>${i + 1}</td>
          <td>${ln.MASANPHAM}</td>
          <td class="text-truncate" title="${ln.TENSANPHAM}">${
                    ln.TENSANPHAM
                }</td>
          <td class="text-end">${fmtVND(ln.SOLUONG)}</td>
          <td class="text-end">${fmtVND(ln.DONGIA)}</td>
          <td class="text-end">${fmtVND(ln.THANHTIEN)}</td>`;
                tbody.appendChild(tr);
            });
            setText("md_tongtien", fmtVND(TONGTIEN));

            const formConfirm = document.getElementById("md_form_confirm");
            if (formConfirm) {
                formConfirm.action = buildShowUrl(
                    `${header.MAPN}/confirm`
                ).replace("/__ID__", "");
                formConfirm.classList.toggle(
                    "d-none",
                    header.TRANGTHAI !== "NHAP"
                );
            }

            bsDetail && bsDetail.show();
        } catch (e) {
            console.error(e);
            Swal.fire({
                icon: "error",
                title: "Lỗi",
                text: "Không thể tải chi tiết phiếu nhập.",
            });
        }
    }

    document.querySelectorAll(".row-detail").forEach((row) => {
        row.addEventListener("click", (e) => {
            if (e.target.closest(".actions")) return;
            const id = row.dataset.id;
            openDetail(id);
        });
    });

    // Create modal: dynamic product list + auto fill price from GIANHAP
    const selNCC = document.querySelector(
        '#modalCreate select[name="MANHACUNGCAP"]'
    );
    const tbl = document.getElementById("tblCreateLines");
    const btnAdd = document.getElementById("btnAddLine");

    const getProductsBySupplier = (ncc) =>
        (window.products || []).filter(
            (p) => !ncc || String(p.MANHACUNGCAP) === String(ncc)
        );
    const getProduct = (id) =>
        (window.products || []).find((p) => String(p.MASANPHAM) === String(id));

    const renderOptions = (list) => {
        const opts = [
            '<option value="" selected disabled>-- Chọn sản phẩm --</option>',
        ];
        list.forEach((p) =>
            opts.push(
                `<option value="${p.MASANPHAM}">${p.TENSANPHAM} (${p.MASANPHAM})</option>`
            )
        );
        return opts.join("");
    };

    const recalcRow = (tr) => {
        const qty = parseFloat(tr.querySelector(".line-qty")?.value || "0");
        const price = parseFloat(tr.querySelector(".line-price")?.value || "0");
        const amt = Math.max(0, qty) * Math.max(0, price);
        const cell = tr.querySelector(".line-amount");
        if (cell) cell.textContent = amt.toLocaleString("vi-VN");
    };

    const bindRowEvents = (tr) => {
        tr.querySelectorAll(".line-qty, .line-price").forEach((el) =>
            el.addEventListener("input", () => recalcRow(tr))
        );

        // Auto fill price according to product's GIANHAP
        const sel = tr.querySelector(".line-masp");
        sel?.addEventListener("change", (e) => {
            const prod = getProduct(e.target.value);
            const priceInput = tr.querySelector(".line-price");
            if (prod && priceInput) {
                priceInput.value = parseFloat(prod.GIANHAP ?? 0);
                recalcRow(tr);
            }
        });

        tr.querySelector(".btnDelLine")?.addEventListener("click", () => {
            const all = tbl.querySelectorAll("tbody tr");
            if (all.length <= 1) {
                tr.querySelector(".line-masp").value = "";
                tr.querySelector(".line-qty").value = 1;
                tr.querySelector(".line-price").value = 0;
                recalcRow(tr);
            } else {
                tr.remove();
            }
        });
    };

    const refillAllProductSelects = () => {
        const ncc = selNCC?.value || "";
        const list = getProductsBySupplier(ncc);
        const html = renderOptions(list);
        tbl.querySelectorAll("select.line-masp").forEach((s) => {
            const oldVal = s.value;
            s.innerHTML = html;
            if ([...s.options].some((o) => o.value == oldVal)) s.value = oldVal;
            // trigger change để auto fill giá
            s.dispatchEvent(new Event("change"));
        });
    };

    btnAdd?.addEventListener("click", () => {
        const ncc = selNCC?.value || "";
        const list = getProductsBySupplier(ncc);
        const html = renderOptions(list);
        const tbody = tbl.querySelector("tbody");
        const tr = document.createElement("tr");
        tr.innerHTML = `
      <td><select name="ITEM_MASP[]" class="form-select line-masp" required>${html}</select></td>
      <td><input type="number" name="ITEM_SOLUONG[]" class="form-control line-qty" min="1" step="1" value="1" required></td>
      <td><input type="number" name="ITEM_DONGIA[]" class="form-control line-price" min="0" step="100" value="0" required></td>
      <td class="line-amount text-end">0</td>
      <td class="text-center">
          <button type="button" class="btn btn-sm btn-danger-soft btnDelLine" title="Xoá dòng"><i class="bi bi-trash"></i></button>
      </td>`;
        tbody.appendChild(tr);
        bindRowEvents(tr);
        recalcRow(tr);
    });

    const firstRow = tbl.querySelector("tbody tr");
    if (firstRow) {
        bindRowEvents(firstRow);
        recalcRow(firstRow);
    }
    refillAllProductSelects();
    selNCC?.addEventListener("change", refillAllProductSelects);

    // Auto open create modal
    (function autoOpenCreate() {
        const mc = document.getElementById("modalCreate");
        if (!mc) return;
        const url = new URL(window.location.href);
        const qOpen = url.searchParams.get("open");
        const isCreatePath =
            window.location.pathname.endsWith("/receipts/create");
        if (qOpen === "create" || isCreatePath) new bootstrap.Modal(mc).show();
    })();
});