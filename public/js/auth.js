// ===================== Helpers =====================
const qs = (s, r) => (r || document).querySelector(s);
const qsa = (s, r) => Array.from((r || document).querySelectorAll(s));

function showToast(message, type = "error", duration = 3000) {
    const toast = document.createElement("div");
    toast.className = `auth-toast ${type}`;
    toast.innerText = message;
    document.body.appendChild(toast);

    Object.assign(toast.style, {
        position: "fixed",
        left: "50%",
        bottom: "50px",
        transform: "translateX(-50%)",
        padding: "10px 20px",
        borderRadius: "5px",
        color: "#fff",
        fontWeight: "500",
        zIndex: 1055,
        backgroundColor: type === "error" ? "#e74c3c" : "#27ae60",
        boxShadow: "0 2px 6px rgba(0,0,0,0.3)",
        opacity: 0,
        transition: "opacity 0.3s"
    });

    setTimeout(() => (toast.style.opacity = "1"), 50);
    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ===================== Toggle đăng nhập / đăng ký =====================
(() => {
    const signUpButton = qs("#signUp");
    const signInButton = qs("#signIn");
    const container = qs("#authContainer");
    if (!signUpButton || !signInButton || !container) return;

    signUpButton.addEventListener("click", () => container.classList.add("right-panel-active"));
    signInButton.addEventListener("click", () => container.classList.remove("right-panel-active"));
})();

function setPanel(panel) {
    const container = qs("#authContainer");
    if (!container) return;
    panel === "register"
        ? container.classList.add("right-panel-active")
        : container.classList.remove("right-panel-active");
}

// ===================== Show/hide password =====================
document.addEventListener("click", e => {
    const btn = e.target.closest(".auth-toggle-pass");
    if (!btn) return;
    const wrap = btn.closest(".auth-input-wrap");
    const input = wrap?.querySelector('input[type="password"], input[type="text"]');
    if (!input) return;

    input.type = input.type === "password" ? "text" : "password";
    const icon = btn.querySelector("i");
    if (icon) {
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
        if (!icon.classList.contains("far") && !icon.classList.contains("fas")) {
            icon.classList.add("far");
        }
    }
});

// ===================== Redirect handling =====================
let _redirectValue = "";
function setRedirectInputs(val) {
    if (!val) return;
    _redirectValue = val;
    qsa("#authModal form.auth-form").forEach(form => {
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

// ===================== Open login modal =====================
function openLoginModal(preferredRedirect) {
    if (preferredRedirect) setRedirectInputs(preferredRedirect);
    setPanel("login");
    const modalEl = qs("#authModal");
    if (modalEl && window.bootstrap?.Modal) {
        new bootstrap.Modal(modalEl).show();
        return true;
    }
    return false;
}
window._openLogin = openLoginModal;

// ===================== Auto open modal from query ?open=... =====================
(() => {
    function applyFromQuery() {
        const params = new URLSearchParams(window.location.search);
        const open = (params.get("open") || "").toLowerCase();
        const redirect = params.get("redirect");
        if (redirect) setRedirectInputs(redirect);
        if (!open) return;
        setPanel(open === "register" ? "register" : "login");
        const modalEl = qs("#authModal");
        if (modalEl && window.bootstrap?.Modal) new bootstrap.Modal(modalEl).show();
    }
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", applyFromQuery);
    } else applyFromQuery();

    const modalEl = qs("#authModal");
    if (modalEl) {
        modalEl.addEventListener("shown.bs.modal", () => {
            if (_redirectValue) setRedirectInputs(_redirectValue);
        });
    }
})();

// ===================== Click [data-action="open-login"] =====================
document.addEventListener("click", e => {
    const trigger = e.target.closest('[data-action="open-login"]');
    if (!trigger) return;
    const href = trigger.getAttribute("href");
    const fromHref = href ? getRedirectFrom(href) : null;
    const fromData = trigger.getAttribute("data-redirect");
    const opened = openLoginModal(fromHref || fromData || "");
    if (opened) e.preventDefault();
});

// ===================== Form submit helper =====================
async function handleFormSubmit(form, callback) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (!submitBtn) return;
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    try {
        const formData = new FormData(form);
        const res = await fetch(form.action, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "" },
            body: formData,
        });

        let data = {};
        try { data = await res.json(); } catch {}

        await callback(res, data, formData);
    } catch (err) {
        console.error(err);
        showToast("Có lỗi xảy ra, vui lòng thử lại.", "error");
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// ===================== Forgot password / OTP / Reset =====================
document.addEventListener("DOMContentLoaded", function () {
    const container = qs("#authContainer");
    const forgotForm = qs("#forgotPasswordForm");
    const verifyOtpForm = qs("#verifyOtpForm");
    const resetForm = qs("#resetPasswordForm");
    const otpEmailInput = qs("#otpEmail");
    const resetEmailInput = qs("#resetEmail");

    const backToLogin = qs("#backToLogin");
    const backToEmail = qs("#backToEmail");
    const backToOtp = qs("#backToOtp");
    const toForgotPassword = qs("#toForgotPassword");

    // --- Step 1: send OTP ---
    if (forgotForm) {
        forgotForm.addEventListener("submit", e => {
            e.preventDefault();
            handleFormSubmit(forgotForm, async (res, data, formData) => {
                if (!res.ok || !data.status) {
                    showToast(data.message || data.errors?.email?.[0] || "Có lỗi xảy ra", "error");
                    return;
                }
                showToast(data.message || "OTP đã gửi thành công!", "success");
                forgotForm.classList.add("d-none");
                verifyOtpForm.classList.remove("d-none");
                otpEmailInput.value = formData.get("email");
            });
        });
    }

    // --- Step 2: verify OTP ---
    if (verifyOtpForm) {
        verifyOtpForm.addEventListener("submit", e => {
            e.preventDefault();
            handleFormSubmit(verifyOtpForm, async (res, data, formData) => {
                if (!res.ok || !data.status) {
                    showToast(data.message || "OTP không hợp lệ", "error");
                    return;
                }
                verifyOtpForm.classList.add("d-none");
                resetForm.classList.remove("d-none");
                resetEmailInput.value = formData.get("email");
                showToast("OTP hợp lệ, nhập mật khẩu mới!", "success");
            });
        });
    }

    // --- Step 3: reset password ---
    if (resetForm) {
        resetForm.addEventListener("submit", e => {
            e.preventDefault();
            handleFormSubmit(resetForm, async (res, data) => {
                if (!res.ok || data.errors) {
                    showToast(
                        data.message || data.errors?.password?.[0] || "Có lỗi xảy ra",
                        "error"
                    );
                    return;
                }
                showToast(data.status || "Đặt lại mật khẩu thành công!", "success");
                resetForm.classList.add("d-none");
                container.classList.remove("forgot-password-mode");
                resetForm.reset();
                forgotForm.reset();
            });
        });
    }

    // --- Navigation buttons ---
    backToLogin?.addEventListener("click", () => {
        container.classList.remove("forgot-password-mode");
        forgotForm?.reset();
    });
    backToEmail?.addEventListener("click", () => {
        verifyOtpForm?.classList.add("d-none");
        forgotForm?.classList.remove("d-none");
    });
    backToOtp?.addEventListener("click", () => {
        resetForm?.classList.add("d-none");
        verifyOtpForm?.classList.remove("d-none");
    });
    toForgotPassword?.addEventListener("click", e => {
        e.preventDefault();
        container.classList.add("forgot-password-mode");
        forgotForm?.classList.remove("d-none");
        verifyOtpForm?.classList.add("d-none");
        resetForm?.classList.add("d-none");
    });
});
