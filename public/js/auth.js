// ===================== Toggle đăng nhập / đăng ký =====================
(() => {
    const signUpButton = document.getElementById("signUp");
    const signInButton = document.getElementById("signIn");
    const container = document.getElementById("authContainer");
    if (signUpButton && signInButton && container) {
        signUpButton.addEventListener("click", () =>
            container.classList.add("right-panel-active")
        );
        signInButton.addEventListener("click", () =>
            container.classList.remove("right-panel-active")
        );
    }
})();

// ===================== Hiện/ẩn mật khẩu (event delegation) =====================
document.addEventListener("click", (e) => {
    const btn = e.target.closest(".auth-toggle-pass");
    if (!btn) return;

    const wrap = btn.closest(".auth-input-wrap");
    const input =
        wrap &&
        wrap.querySelector('input[type="password"], input[type="text"]');
    if (!input) return;

    input.type = input.type === "password" ? "text" : "password";

    const icon = btn.querySelector("i");
    if (icon) {
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
        if (
            !icon.classList.contains("far") &&
            !icon.classList.contains("fas")
        ) {
            icon.classList.add("far");
        }
    }
});

// ===================== Loader khi submit form =====================
document.querySelectorAll(".auth-form").forEach((form) => {
    form.addEventListener("submit", () => {
        const btn = form.querySelector('.auth-btn[type="submit"]');
        if (btn) btn.classList.add("is-loading");
    });
});

// ===================== Helpers =====================
const qs = (s, r) => (r || document).querySelector(s);
const qsa = (s, r) => Array.from((r || document).querySelectorAll(s));

function setPanel(panel) {
    const container = qs("#authContainer");
    if (!container) return;
    panel === "register"
        ? container.classList.add("right-panel-active")
        : container.classList.remove("right-panel-active");
}

// ====== redirect handling: chỉ gắn khi có giá trị được CHỈ ĐỊNH ======
let _redirectValue = ""; // lưu giá trị đã set (nếu có)

function setRedirectInputs(val) {
    if (!val) return; // ⟵ KHÔNG tự gắn current URL nữa
    _redirectValue = val;
    qsa("#authModal form.auth-form").forEach((form) => {
        let input = form.querySelector('input[name="redirect"]');
        if (!input) {
            input = document.createElement("input");
            input.type = "hidden";
            input.name = "redirect";
            form.appendChild(input);
        }
        input.value = val;
    });
}

function getRedirectFrom(url) {
    try {
        const u = new URL(url, window.location.origin);
        return u.searchParams.get("redirect");
    } catch {
        return null;
    }
}

// ===================== Mở modal đăng nhập =====================
function openLoginModal(preferredRedirect) {
    // Chỉ set nếu được truyền vào (từ href ?redirect=... hoặc data-redirect)
    if (preferredRedirect) setRedirectInputs(preferredRedirect);

    setPanel("login");

    const modalEl = qs("#authModal");
    if (modalEl && window.bootstrap?.Modal) {
        new bootstrap.Modal(modalEl).show();
        return true;
    }
    return false;
}
window._openLogin = openLoginModal; // dự phòng

// ===================== Tự xử lý theo query ?open=... (nếu có) =====================
(() => {
    function applyFromQuery() {
        const params = new URLSearchParams(window.location.search);
        const open = (params.get("open") || "").toLowerCase();
        const redirect = params.get("redirect");

        // Chỉ gắn redirect khi query thật sự có
        if (redirect) setRedirectInputs(redirect);

        if (!open) return;
        setPanel(open === "register" ? "register" : "login");

        const modalEl = qs("#authModal");
        if (modalEl && window.bootstrap?.Modal) {
            new bootstrap.Modal(modalEl).show();
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", applyFromQuery);
    } else {
        applyFromQuery();
    }

    // Khi modal mở lại, nếu đã có _redirectValue trước đó thì giữ nguyên
    const modalEl = qs("#authModal");
    if (modalEl) {
        modalEl.addEventListener("shown.bs.modal", () => {
            if (_redirectValue) setRedirectInputs(_redirectValue);
        });
    }
})();

// ===================== Click [data-action="open-login"] =====================
document.addEventListener("click", (e) => {
    const trigger = e.target.closest('[data-action="open-login"]');
    if (!trigger) return;

    // Lấy redirect từ href (?redirect=...) hoặc từ data-redirect
    const href = trigger.getAttribute("href");
    const fromHref = href ? getRedirectFrom(href) : null;
    const fromData = trigger.getAttribute("data-redirect");

    const opened = openLoginModal(fromHref || fromData || "");
    if (opened) {
        e.preventDefault(); // đã mở modal thì không điều hướng
    }
});
