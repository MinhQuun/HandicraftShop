// Toggle đăng nhập / đăng ký (ID đúng: authContainer)
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

// Tự động focus nếu có lỗi validation
document.addEventListener("DOMContentLoaded", function () {
    var errorFields = document.querySelectorAll(
        ".field-validation-error input, .field-validation-error textarea"
    );
    if (errorFields.length > 0) {
        errorFields[0].focus();
    }
});

// Hiện / Ẩn mật khẩu (Font Awesome 5)
(() => {
    document.querySelectorAll(".auth-toggle-pass").forEach((btn) => {
        btn.addEventListener("click", () => {
            const input = btn
                .closest(".auth-input-wrap")
                ?.querySelector("input");
            const icon = btn.querySelector("i");
            if (!input || !icon) return;

            const show = input.type === "password";
            input.type = show ? "text" : "password";

            // Toggle class cho FA5: fa-eye <-> fa-eye-slash
            icon.classList.toggle("fa-eye", !show);
            icon.classList.toggle("fa-eye-slash", show);

            // Đảm bảo có prefix 'far' hoặc 'fas'
            if (
                !icon.classList.contains("far") &&
                !icon.classList.contains("fas")
            ) {
                icon.classList.add("far");
            }
        });
    });
})();

// Loader khi submit form
document.querySelectorAll(".auth-form").forEach((form) => {
    form.addEventListener("submit", () => {
        const btn = form.querySelector('.auth-btn[type="submit"]');
        if (btn) btn.classList.add("is-loading");
    });
});
