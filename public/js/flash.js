// public/js/flash.js
(() => {
    const stack = document.querySelector(".toast-stack");
    if (!stack) return;

    stack.querySelectorAll(".toast-card").forEach((t) => {
        // thời gian auto-hide, mặc định 4200ms (4.2s)
        const ms = Number(t.dataset.autohide || 4200);
        t.style.setProperty("--ms", `${ms}ms`);

        const remove = () => {
            t.style.animation = "toast-fade-out .22s ease-in both";
            setTimeout(() => t.remove(), 220);
        };
        const timer = setTimeout(remove, ms);

        const closeBtn = t.querySelector(".toast-close");
        if (closeBtn)
            closeBtn.addEventListener("click", () => {
                clearTimeout(timer);
                remove();
            });
    });
})();
