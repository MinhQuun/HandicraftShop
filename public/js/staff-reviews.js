document.addEventListener("DOMContentLoaded", () => {
    const hasBootstrap = typeof window.bootstrap !== "undefined";
    const viewModal = document.getElementById("modalView");

    // --- helper: fill modal from button dataset
    function fillFromBtn(btn) {
        if (!viewModal || !btn) return;
        const id    = btn.getAttribute("data-id");
        const name  = btn.getAttribute("data-name")  || "—";
        const email = btn.getAttribute("data-email") || "—";
        const msg   = btn.getAttribute("data-message") || "—";
        const time  = btn.getAttribute("data-time")  || "—";

        viewModal.querySelector("#v_name").textContent = name;
        viewModal.querySelector("#v_email").textContent = email;
        viewModal.querySelector("#v_time").textContent = time;
        viewModal.querySelector("#v_message").textContent = msg;

        const form = viewModal.querySelector("#formReply");
        const tpl  = form?.getAttribute("data-action-template") || "";
        if (form && tpl && id) form.action = tpl.replace(":id", id);
    }

    // 1) Fallback click -> show modal
    document.querySelectorAll(".btn-view").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            fillFromBtn(btn);
            if (hasBootstrap && viewModal) {
                e.preventDefault();
                const instance = bootstrap.Modal.getOrCreateInstance(viewModal);
                instance.show();
            }
        });
    });

    // 2) Fill khi mở theo chuẩn BS
    if (viewModal) {
        viewModal.addEventListener("show.bs.modal", (evt) => {
            const btn = evt.relatedTarget;
            if (btn) fillFromBtn(btn);
        });

        viewModal.addEventListener("shown.bs.modal", () => {
            const ta = viewModal.querySelector("#reply_message");
            if (ta) {
                autoResize(ta);
                ta.focus({ preventScroll: true });
                updateCounter(ta);
            }
        });
    }

    // --- Reply helpers
    function autoResize(el){
        el.style.height = "auto";
        const maxH = 320;
        el.style.height = Math.min(el.scrollHeight, maxH) + "px";
    }
    function updateCounter(el){
        const c = document.getElementById("replyCounter");
        if (c) c.textContent = (el.value?.length || 0) + "/5000";
    }

    document.addEventListener("input", (e) => {
        if (e.target && e.target.id === "reply_message") {
            autoResize(e.target);
            updateCounter(e.target);
        }
    });

    // Ctrl + Enter để gửi
    document.addEventListener("keydown", (e) => {
        if (e.target && e.target.id === "reply_message" && (e.ctrlKey || e.metaKey) && e.key === "Enter") {
            const form = document.getElementById("formReply");
            if (form) form.submit();
        }
    });

    // SweetAlert2: confirm delete
    document.querySelectorAll("form.form-delete").forEach((f) => {
        f.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!window.Swal) return f.submit();
            Swal.fire({
                title: "Xoá ý kiến này?",
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

    // Toast từ flash session
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

    // Auto-submit khi đổi trạng thái (lọc)
    const statusSelect = document.querySelector('.reviews-filter select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener("change", () => {
            const form = statusSelect.form;
            if (form) form.submit();
        });
    }

    // Tooltip
    if (hasBootstrap) {
        document.querySelectorAll(".reviews-table [title]").forEach((el) => {
            try {
                el.setAttribute("data-bs-toggle", "tooltip");
                new bootstrap.Tooltip(el);
            } catch (_) {}
        });
    }
});
