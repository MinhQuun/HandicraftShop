(() => {
    const DEFAULT_DURATION = 4200;
    let stack = document.querySelector(".toast-stack");
    if (!stack) {
        stack = document.createElement("div");
        stack.className = "toast-stack";
        stack.setAttribute("role", "status");
        stack.setAttribute("aria-live", "polite");
        document.body.appendChild(stack);
    }

    const icons = {
        success: "fa-solid fa-circle-check",
        error: "fa-solid fa-circle-exclamation",
        warning: "fa-solid fa-triangle-exclamation",
        info: "fa-solid fa-circle-info",
    };

    const getIcon = (type) => icons[type] || icons.info;

    const removeToast = (toast) => {
        toast.style.animation = "toast-slide-out 0.22s ease-in both";
        setTimeout(() => toast.remove(), 220);
    };

    const registerToast = (toast) => {
        const duration = Number(toast.dataset.autohide || DEFAULT_DURATION);
        toast.style.setProperty("--ms", `${duration}ms`);
        const timer = setTimeout(() => removeToast(toast), duration);
        const closeBtn = toast.querySelector(".toast-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", () => {
                clearTimeout(timer);
                removeToast(toast);
            });
        }
    };

    const createToast = ({
        type = "info",
        title = "Thông báo",
        message = "",
        duration,
    } = {}) => {
        const toast = document.createElement("div");
        toast.className = `toast-card is-${type}`;
        toast.dataset.autohide = duration ?? DEFAULT_DURATION;
        toast.innerHTML = `
            <div class="toast-icon"><i class="${getIcon(type)}"></i></div>
            <div class="toast-content">
                <strong>${title}</strong>
                <div class="toast-text">${message}</div>
            </div>
            <button class="toast-close" aria-label="Đóng"><i class="fa-solid fa-xmark"></i></button>
        `;
        stack.prepend(toast);
        registerToast(toast);
    };

    window.showToast = createToast;
    stack.querySelectorAll(".toast-card").forEach(registerToast);
})();
