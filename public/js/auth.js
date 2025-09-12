// Toggle đăng nhập / đăng ký
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

// Event delegation: hiện/ẩn mật khẩu
document.addEventListener("click", (e) => {
    const btn = e.target.closest(".auth-toggle-pass");
    if (!btn) return;

    const wrap = btn.closest(".auth-input-wrap");
    const input =
        wrap &&
        wrap.querySelector('input[type="password"], input[type="text"]');
    if (!input) return;

    // Toggle type
    input.type = input.type === "password" ? "text" : "password";

    // Đổi icon (tương thích FA5/6)
    const icon = btn.querySelector("i");
    if (icon) {
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");

        // đảm bảo có 1 prefix
        if (
            !icon.classList.contains("far") &&
            !icon.classList.contains("fas")
        ) {
            icon.classList.add("far");
        }
    }
});

// Loader khi submit form
document.querySelectorAll(".auth-form").forEach((form) => {
    form.addEventListener("submit", () => {
        const btn = form.querySelector('.auth-btn[type="submit"]');
        if (btn) btn.classList.add("is-loading");
    });
});
