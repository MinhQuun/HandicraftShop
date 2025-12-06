document.addEventListener("DOMContentLoaded", () => {
    const hasSwal = typeof Swal !== "undefined";
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

    function validatePromotionForm(form) {
        let ok = true;
        const scope = form.querySelector('select[name="PHAMVI"]');
        const type = form.querySelector('select[name="LOAIKHUYENMAI"]');
        const code = form.querySelector('input[name="MAKHUYENMAI"]');
        const name = form.querySelector('input[name="TENKHUYENMAI"]');
        const discount = form.querySelector('input[name="GIAMGIA"]');
        const start = form.querySelector('input[name="NGAYBATDAU"]');
        const end = form.querySelector('input[name="NGAYKETTHUC"]');
        const min = form.querySelector('input[name="min_order_total"]');
        const max = form.querySelector('input[name="max_discount"]');

        if (code) {
            const v = code.value.trim();
            const msg = v.length < 3 ? "Mã tối thiểu 3 ký tự." : "";
            setErr(code, msg);
            if (msg) ok = false;
        }
        if (name) {
            const v = name.value.trim();
            const msg = v.length < 3 ? "Tên tối thiểu 3 ký tự." : "";
            setErr(name, msg);
            if (msg) ok = false;
        }
        if (scope) {
            const msg = scope.value ? "" : "Chọn phạm vi.";
            setErr(scope, msg);
            if (msg) ok = false;
        }
        if (type) {
            const msg = type.value ? "" : "Chọn loại.";
            setErr(type, msg);
            if (msg) ok = false;
        }
        if (discount) {
            const n = Number(discount.value);
            let msg = isNaN(n) || n <= 0 ? "Mức giảm phải > 0." : "";
            const typeVal = type?.value || "";
            if (!msg && typeVal === "Giảm %") {
                if (n > 100) msg = "Giảm % tối đa 100.";
            }
            setErr(discount, msg);
            if (msg) ok = false;
        }
        if (start && end && start.value && end.value) {
            const s = new Date(start.value);
            const e = new Date(end.value);
            const msg = s > e ? "Ngày kết thúc phải sau ngày bắt đầu." : "";
            setErr(end, msg);
            if (msg) ok = false;
        }
        if (min) {
            const n = Number(min.value || 0);
            const msg = n < 0 ? "Không nhỏ hơn 0." : "";
            setErr(min, msg);
            if (msg) ok = false;
        }
        if (max) {
            const n = Number(max.value || 0);
            const msg = n < 0 ? "Không nhỏ hơn 0." : "";
            setErr(max, msg);
            if (msg) ok = false;
        }

        return ok;
    }

    // ===== Helper =====
    const showGroup = (els, show) => {
        els.forEach((el) => el.classList.toggle("d-none", !show));
    };
    const toggleByScope = (wrap, scope) => {
        if (!wrap) return;
        const orderEls = wrap.querySelectorAll(".c-order-only, .e-order-only");
        const productEls = wrap.querySelectorAll(
            ".c-product-only, .e-product-only"
        );
        showGroup(orderEls, scope === "ORDER");
        showGroup(productEls, scope === "PRODUCT");
    };
    const moveOptions = (from, to) => {
        if (!from || !to) return;
        Array.from(from.selectedOptions).forEach((opt) => to.appendChild(opt));
        from.selectedIndex = -1;
    };

    // Preview selected products helper
    const fmtCurrency = (v) => {
        try { return Number(v || 0).toLocaleString('vi-VN'); } catch { return v; }
    };
    const buildPreviewItem = (opt) => {
        const id = opt.value;
        const name = opt.dataset?.name || opt.textContent?.trim() || id;
        const price = opt.dataset?.price || '';
        const image = opt.dataset?.image || '';
        const imgUrl = image
            ? (window.assetImgPrefix ? window.assetImgPrefix + encodeURIComponent(image) : (window.location.origin + '/assets/images/' + encodeURIComponent(image)))
            : (window.location.origin + '/HinhAnh/LOGO/Logo.jpg');
        return `
            <div class="sp-card">
                <div class="thumb" style="background-image:url('${imgUrl}')"></div>
                <div class="meta">
                    <div class="name" title="${name}">${name}</div>
                    <div class="price">${price ? fmtCurrency(price)+' VNĐ' : ''}</div>
                </div>
                <div class="id">${id}</div>
            </div>
        `;
    };
    const renderPreview = (selectEl, previewId) => {
        const wrap = document.getElementById(previewId);
        if (!wrap || !selectEl) return;
        const opts = Array.from(selectEl.querySelectorAll('option'));
        if (!opts.length) { wrap.innerHTML = '<div class="text-muted small">Chưa chọn sản phẩm nào.</div>'; return; }
        wrap.innerHTML = opts.map(buildPreviewItem).join('');
    };

    // ===== Select2 init =====
    if (window.$ && $(".select2").length) {
        $(".select2").select2({
            theme: "bootstrap-5",
            placeholder: "Chọn...",
            allowClear: true,
            width: "100%",
        });
    }

    // ===== Flash Alert2 =====
    const flash = document.getElementById("flash");
    if (flash && hasSwal) {
        const { success, error, info, warning } = flash.dataset;
        let icon, title, msg;
        if (success) {
            icon = "success";
            title = "Thành công";
            msg = success;
        } else if (error) {
            icon = "error";
            title = "Thất bại";
            msg = error;
        } else if (info) {
            icon = "info";
            title = "Thông báo";
            msg = info;
        } else if (warning) {
            icon = "warning";
            title = "Cảnh báo";
            msg = warning;
        }
        if (msg) Swal.fire({ icon, title, text: msg, confirmButtonText: "OK" });
    }

    // ===== Create Modal =====
    const modalCreate = document.getElementById("modalCreate");
    if (modalCreate) {
        const scopeSelect = modalCreate.querySelector("#c_scope");
        const codeInput = modalCreate.querySelector("#c_code");

        // Bật/tắt đúng ngay khi mở modal và khi đổi chọn
        modalCreate.addEventListener("show.bs.modal", () => {
            setTimeout(() => toggleByScope(modalCreate, scopeSelect.value), 0);
        });
        modalCreate.addEventListener("shown.bs.modal", () => {
            toggleByScope(modalCreate, scopeSelect.value);
        });
        scopeSelect.addEventListener("change", (e) => {
            toggleByScope(modalCreate, e.target.value);
        });

        // Chuẩn hoá mã KM
        codeInput?.addEventListener("input", (e) => {
            e.target.value = e.target.value.toUpperCase().replace(/\s+/g, "");
        });

        // Dual list (create)
        const cAvailable = document.getElementById("c_available_products");
        const cSelected = document.getElementById("c_selected_products");
        const cMoveRight = document.getElementById("c_move_right");
        const cMoveLeft = document.getElementById("c_move_left");

        const updateCreatePreview = () => renderPreview(cSelected, 'c_selected_preview');
        cMoveRight?.addEventListener('click', () => { moveOptions(cAvailable, cSelected); updateCreatePreview(); });
        cMoveLeft?.addEventListener('click', () => { moveOptions(cSelected, cAvailable); updateCreatePreview(); });
        cSelected?.addEventListener('change', updateCreatePreview);
        updateCreatePreview();

        // Filter realtime (create)
        const nameFilter = document.getElementById("filterName");
        const typeFilter = document.getElementById("filterType");
        const supplierFilter = document.getElementById("filterSupplier");

        function filterProducts() {
            if (!cAvailable) return;
            const name = (nameFilter?.value || "").toLowerCase();
            const type = typeFilter?.value || "";
            const supplier = supplierFilter?.value || "";
            Array.from(cAvailable.options).forEach((opt) => {
                const matchName = opt.textContent.toLowerCase().includes(name);
                const matchType = !type || opt.dataset.type === type;
                const matchSupplier =
                    !supplier || opt.dataset.supplier === supplier;
                opt.hidden = !(matchName && matchType && matchSupplier);
            });
        }
        nameFilter?.addEventListener("input", filterProducts);
        typeFilter?.addEventListener("change", filterProducts);
        supplierFilter?.addEventListener("change", filterProducts);
        filterProducts(); // chạy 1 lần đầu

        modalCreate
            .querySelector("form")
            ?.addEventListener("submit", (e) => {
                if (!validatePromotionForm(e.target)) e.preventDefault();
            });
    }

    // ===== Edit Modal =====
    const editModal = document.getElementById("modalEdit");
    if (editModal) {
        editModal.addEventListener("show.bs.modal", (evt) => {
            const btn = evt.relatedTarget;
            if (!btn) return;

            const id = btn.dataset.id || "";
            const name = btn.dataset.name || "";
            const type = btn.dataset.type || "";
            const discount = btn.dataset.discount || 0;
            const start = btn.dataset.start || "";
            const end = btn.dataset.end || "";
            const scope = btn.dataset.scope || "PRODUCT";
            const priority = btn.dataset.priority || 10;
            const products =
                btn.dataset.products?.split(",").filter(Boolean) || [];
            let rule = {};
            try {
                rule = JSON.parse(btn.dataset.json || "{}");
            } catch {}

            editModal.querySelector("#e_id").value = id;
            editModal.querySelector("#e_name").value = name;
            editModal.querySelector("#e_type").value = type;
            editModal.querySelector("#e_discount").value = discount;
            editModal.querySelector("#e_start").value = start;
            editModal.querySelector("#e_end").value = end;
            editModal.querySelector("#e_scope").value = scope;
            editModal.querySelector("#e_priority").value = priority;
            toggleByScope(editModal, scope);

            editModal.querySelector("#e_min").value =
                rule?.min_order_total || "";
            editModal.querySelector("#e_max").value = rule?.max_discount || "";
            editModal.querySelector("#e_non_stackable").checked =
                !!rule?.non_stackable;

            // Dual list (edit)
            const eAvailable = editModal.querySelector("#e_available_products");
            const eSelected = editModal.querySelector("#e_selected_products");
            const allOpts = Array.from(eAvailable.querySelectorAll("option"));
            eAvailable.innerHTML = "";
            eSelected.innerHTML = "";
            allOpts.forEach((opt) => {
                (products.includes(opt.value)
                    ? eSelected
                    : eAvailable
                ).appendChild(opt.cloneNode(true));
            });

            const moveRight = document.getElementById('e_move_right');
            const moveLeft = document.getElementById('e_move_left');
            // gỡ handler cũ trước khi gán mới (tránh trùng)
            moveRight.replaceWith(moveRight.cloneNode(true));
            moveLeft.replaceWith(moveLeft.cloneNode(true));
            const moveRightFresh = document.getElementById('e_move_right');
            const moveLeftFresh = document.getElementById('e_move_left');
            const updateEditPreview = () => renderPreview(eSelected, 'e_selected_preview');
            moveRightFresh.addEventListener('click', () => { moveOptions(eAvailable, eSelected); updateEditPreview(); });
            moveLeftFresh.addEventListener('click', () => { moveOptions(eSelected, eAvailable); updateEditPreview(); });
            eSelected.addEventListener('change', updateEditPreview);
            updateEditPreview();

            // Update form action
            const form = editModal.querySelector("#formEdit");
            const tpl = form.getAttribute("data-action-template") || "";
            form.action = tpl.replace(":id", id);
        });

        const eScope = editModal.querySelector("#e_scope");
        eScope?.addEventListener("change", (e) =>
            toggleByScope(editModal, e.target.value)
        );

        editModal
            .querySelector("form")
            ?.addEventListener("submit", (e) => {
                if (!validatePromotionForm(e.target)) e.preventDefault();
            });
    }

    // ===== Confirm Delete =====
    document.querySelectorAll("form.form-delete").forEach((f) => {
        f.addEventListener("submit", (e) => {
            e.preventDefault();
            if (!hasSwal) return f.submit();
            Swal.fire({
                icon: "warning",
                title: "Xoá khuyến mãi?",
                text: "Thao tác này không thể hoàn tác.",
                showCancelButton: true,
                confirmButtonText: "Xoá",
                cancelButtonText: "Huỷ",
                reverseButtons: true,
                focusCancel: true,
            }).then((res) => res.isConfirmed && f.submit());
        });
    });
});
