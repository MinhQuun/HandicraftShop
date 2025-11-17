// ===================== Helpers =====================
const qs = (s, r) => (r || document).querySelector(s);
const qsa = (s, r) => Array.from((r || document).querySelectorAll(s));

function showToast(message, type = "success", duration = 4200, title) {
    // Tạo stack nếu chưa có
    let stack = document.querySelector(".toast-stack");
    if (!stack) {
        stack = document.createElement("div");
        stack.className = "toast-stack";
        document.body.appendChild(stack);
    }

    // Tạo thẻ toast-card theo markup của flash.js
    const card = document.createElement("div");
    card.className = `toast-card ${type}`;
    card.dataset.autohide = String(duration);

    // Icon theo type
    const iconMap = {
        success: '<i class="fas fa-check-circle" aria-hidden="true"></i>',
        error: '<i class="fas fa-times-circle" aria-hidden="true"></i>',
        info: '<i class="fas fa-info-circle" aria-hidden="true"></i>',
        warn: '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i>',
    };
    const heading =
        title ||
        (type === "success"
            ? "Thành công"
            : type === "error"
            ? "Lỗi"
            : type === "warn"
            ? "Chú ý"
            : "Thông báo");

    card.innerHTML = `
        <div class="toast-icon">${iconMap[type] || iconMap.info}</div>
        <div class="toast-content">
            <strong class="toast-title">${heading}</strong>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" aria-label="Đóng">&times;</button>
    `;

    stack.appendChild(card);

    // Tự ẩn (fallback nếu flash.js không bắt event cho toast mới)
    const remove = () => {
        card.style.animation = "toast-fade-out .22s ease-in both";
        setTimeout(() => card.remove(), 220);
    };
    const ms = Number(duration || card.dataset.autohide || 4200);
    const timer = setTimeout(remove, ms);

    // Đóng thủ công
    card.querySelector(".toast-close")?.addEventListener("click", () => {
        clearTimeout(timer);
        remove();
    });

    // Hiệu ứng xuất hiện
    card.style.animation = "toast-fade-in .22s ease-out both";
}

// ===================== Client-side validations =====================
const emailRegex =
    /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
const nameRegex = /^[\p{L}\s'.-]{2,}$/u;

const getErrorAnchor = (input) => input.closest(".auth-input-wrap") || input;

const setFieldError = (input, message) => {
    if (!input) return;
    const anchor = getErrorAnchor(input);
    let error = anchor.nextElementSibling;
    while (
        error &&
        !(
            error.classList?.contains("auth-error") &&
            error.dataset.client === "true"
        )
    ) {
        error = error.nextElementSibling;
    }
    if (!error) {
        error = document.createElement("div");
        error.className = "auth-error";
        error.dataset.client = "true";
        let reference = anchor;
        let sibling = anchor.nextElementSibling;
        while (sibling && sibling.classList?.contains("auth-error")) {
            reference = sibling;
            sibling = sibling.nextElementSibling;
        }
        reference.parentElement?.insertBefore(error, reference.nextSibling);
    }
    error.textContent = message;
    input.classList.add("is-invalid");
};

const clearFieldError = (input) => {
    if (!input) return;
    input.classList.remove("is-invalid");
    const anchor = getErrorAnchor(input);
    let error = anchor.nextElementSibling;
    while (error) {
        if (
            error.classList?.contains("auth-error") &&
            error.dataset.client === "true"
        ) {
            error.remove();
            break;
        }
        if (error.classList?.contains("auth-input-wrap")) break;
        error = error.nextElementSibling;
    }
};

const AUTH_VALIDATORS = {
    login(form) {
        let valid = true;
        const emailInput = form.querySelector('input[name="email"]');
        const passwordInput = form.querySelector('input[name="password"]');

        if (emailInput) {
            clearFieldError(emailInput);
            const value = emailInput.value.trim();
            if (!value || !emailRegex.test(value)) {
                setFieldError(emailInput, "Vui lòng nhập email hợp lệ.");
                valid = false;
            }
        }

        if (passwordInput) {
            clearFieldError(passwordInput);
            if (passwordInput.value.trim().length < 6) {
                setFieldError(
                    passwordInput,
                    "Mật khẩu phải có ít nhất 6 ký tự."
                );
                valid = false;
            }
        }

        if (!valid) {
            showToast("Vui lòng kiểm tra lại thông tin đăng nhập.", "warn");
        }
        return valid;
    },
    register(form) {
        let valid = true;
        const nameInput = form.querySelector('input[name="name"]');
        const emailInput = form.querySelector('input[name="email"]');
        const phoneInput = form.querySelector('input[name="phone"]');
        const passwordInput = form.querySelector('input[name="password"]');
        const confirmInput = form.querySelector(
            'input[name="password_confirmation"]'
        );

        if (nameInput) {
            clearFieldError(nameInput);
            const value = nameInput.value.trim();
            if (!value || !nameRegex.test(value)) {
                setFieldError(
                    nameInput,
                    "Họ và tên phải từ 2 ký tự và không chứa ký tự đặc biệt."
                );
                valid = false;
            }
        }

        if (emailInput) {
            clearFieldError(emailInput);
            const value = emailInput.value.trim();
            if (!value || !emailRegex.test(value)) {
                setFieldError(emailInput, "Email không hợp lệ.");
                valid = false;
            }
        }

        if (phoneInput) {
            clearFieldError(phoneInput);
            if (!/^0\d{9}$/.test(phoneInput.value.trim())) {
                setFieldError(
                    phoneInput,
                    "Số điện thoại phải gồm 10 số và bắt đầu bằng 0."
                );
                valid = false;
            }
        }

        if (passwordInput) {
            clearFieldError(passwordInput);
            if (!strongPasswordRegex.test(passwordInput.value.trim())) {
                setFieldError(
                    passwordInput,
                    "Mật khẩu cần tối thiểu 8 ký tự gồm chữ hoa, chữ thường và số."
                );
                valid = false;
            }
        }

        if (confirmInput) {
            clearFieldError(confirmInput);
            if (confirmInput.value !== passwordInput?.value) {
                setFieldError(confirmInput, "Xác nhận mật khẩu không khớp.");
                valid = false;
            }
        }

        if (!valid) {
            showToast("Vui lòng kiểm tra lại thông tin đăng ký.", "warn");
        }
        return valid;
    },
};

document.addEventListener("submit", (event) => {
    const form = event.target.closest("[data-auth-form]");
    if (!form) return;
    const type = form.getAttribute("data-auth-form");
    const validator = AUTH_VALIDATORS[type];
    if (typeof validator === "function" && !validator(form)) {
        event.preventDefault();
    }
});

document.addEventListener("input", (event) => {
    const target = event.target.closest(".auth-input");
    if (target) {
        clearFieldError(target);
    }
});

// ===================== Toggle đăng nhập / đăng ký =====================
(() => {
    const signUpButton = qs("#signUp");
    const signInButton = qs("#signIn");
    const container = qs("#authContainer");
    if (!signUpButton || !signInButton || !container) return;

    signUpButton.addEventListener("click", () =>
        container.classList.add("right-panel-active")
    );
    signInButton.addEventListener("click", () =>
        container.classList.remove("right-panel-active")
    );
})();

function setPanel(panel) {
    const container = qs("#authContainer");
    if (!container) return;
    panel === "register"
        ? container.classList.add("right-panel-active")
        : container.classList.remove("right-panel-active");
}

// ===================== Show/hide password =====================
document.addEventListener("click", (e) => {
    const btn = e.target.closest(".auth-toggle-pass");
    if (!btn) return;
    const wrap = btn.closest(".auth-input-wrap");
    const input = wrap?.querySelector(
        'input[type="password"], input[type="text"]'
    );
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

// ===================== Redirect handling =====================
let _redirectValue = "";
function setRedirectInputs(val) {
    if (!val) return;
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
        if (modalEl && window.bootstrap?.Modal)
            new bootstrap.Modal(modalEl).show();
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
document.addEventListener("click", (e) => {
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
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    try {
        const formData = new FormData(form);
        const res = await fetch(form.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN":
                    document.querySelector('meta[name="csrf-token"]')
                        ?.content || "",
            },
            body: formData,
        });

        let data = {};
        try {
            data = await res.json();
        } catch {}

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
    const resetTokenInput = qs("#resetToken");

    const backToLogin = qs("#backToLogin");
    const backToEmail = qs("#backToEmail");
    const backToOtp = qs("#backToOtp");
    const toForgotPassword = qs("#toForgotPassword");

    // --- Step 1: send OTP ---
    if (forgotForm) {
        forgotForm.addEventListener("submit", (e) => {
            e.preventDefault();
            handleFormSubmit(forgotForm, async (res, data, formData) => {
                if (!res.ok || !data.status) {
                    showToast(
                        data.message ||
                            data.errors?.email?.[0] ||
                            "Có lỗi xảy ra",
                        "error"
                    );
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
        verifyOtpForm.addEventListener("submit", (e) => {
            e.preventDefault();
            handleFormSubmit(verifyOtpForm, async (res, data, formData) => {
                if (!res.ok || !data.status) {
                    showToast(data.message || "OTP không hợp lệ", "error");
                    return;
                }
                verifyOtpForm.classList.add("d-none");
                resetForm.classList.remove("d-none");
                resetEmailInput.value = formData.get("email");
                if (resetTokenInput) {
                    resetTokenInput.value = formData.get("token");
                }
                showToast("OTP hợp lệ, nhập mật khẩu mới!", "success");
            });
        });
    }

    // --- Step 3: reset password ---
    if (resetForm) {
        resetForm.addEventListener("submit", (e) => {
            e.preventDefault();
            handleFormSubmit(resetForm, async (res, data) => {
                if (!res.ok || data.errors) {
                    showToast(
                        data.message ||
                            data.errors?.password?.[0] ||
                            "Có lỗi xảy ra",
                        "error"
                    );
                    return;
                }
                showToast(
                    data.status || "Đặt lại mật khẩu thành công!",
                    "success"
                );
                resetForm.classList.add("d-none");
                container.classList.remove("forgot-password-mode");
                resetForm.reset();
                forgotForm.reset();
                if (resetTokenInput) {
                    resetTokenInput.value = "";
                }
            });
        });
    }

    // --- Navigation buttons ---
    backToLogin?.addEventListener("click", () => {
        container.classList.remove("forgot-password-mode");
        forgotForm?.reset();
        if (resetTokenInput) {
            resetTokenInput.value = "";
        }
    });
    backToEmail?.addEventListener("click", () => {
        verifyOtpForm?.classList.add("d-none");
        forgotForm?.classList.remove("d-none");
        if (resetTokenInput) {
            resetTokenInput.value = "";
        }
    });
    backToOtp?.addEventListener("click", () => {
        resetForm?.classList.add("d-none");
        verifyOtpForm?.classList.remove("d-none");
    });
    toForgotPassword?.addEventListener("click", (e) => {
        e.preventDefault();
        container.classList.add("forgot-password-mode");
        forgotForm?.classList.remove("d-none");
        verifyOtpForm?.classList.add("d-none");
        resetForm?.classList.add("d-none");
        if (resetTokenInput) {
            resetTokenInput.value = "";
        }
    });
});
