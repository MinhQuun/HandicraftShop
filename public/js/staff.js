document.addEventListener("DOMContentLoaded", () => {
    const btnSidebar = document.getElementById("btnSidebar");
    const aside = document.getElementById("staffSidebar");
    const overlay = document.getElementById("sidebarOverlay");

    function openSidebar() {
        aside?.classList.add("open");
        overlay?.classList.add("show");
        document.body.style.overflow = "hidden";
    }
    function closeSidebar() {
        aside?.classList.remove("open");
        overlay?.classList.remove("show");
        document.body.style.overflow = "";
    }

    btnSidebar?.addEventListener("click", () => {
        aside?.classList.contains("open") ? closeSidebar() : openSidebar();
    });

    overlay?.addEventListener("click", closeSidebar);

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeSidebar();
    });

    document.querySelectorAll("#staffSidebar .nav-link").forEach((a) => {
        a.addEventListener("click", () => {
            if (window.innerWidth < 992) closeSidebar();
        });
    });
});
