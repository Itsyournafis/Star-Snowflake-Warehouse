document.addEventListener("DOMContentLoaded", function () {

    // ========================================
    // 1. SIDEBAR FUNCTIONALITY
    // ========================================

    const menuToggle = document.getElementById("menuToggle");
    const closeSidebar = document.getElementById("closeSidebar");
    const sidebar = document.getElementById("sidebar");
    const sidebarOverlay = document.getElementById("sidebarOverlay");
    const sidebarLinks = document.querySelectorAll(".sidebar-menu a");

    function openSidebar() {
        if (!sidebar || !sidebarOverlay || !menuToggle) return;

        sidebar.classList.add("active");
        sidebarOverlay.classList.add("active");
        menuToggle.setAttribute("aria-expanded", "true");
    }

    function closeSidebarMenu() {
        if (!sidebar || !sidebarOverlay || !menuToggle) return;

        sidebar.classList.remove("active");
        sidebarOverlay.classList.remove("active");
        menuToggle.setAttribute("aria-expanded", "false");
    }

    if (menuToggle && sidebar) {
        menuToggle.addEventListener("click", function () {

            if (sidebar.classList.contains("active")) {
                closeSidebarMenu();
            } else {
                openSidebar();
            }

        });
    }

    if (closeSidebar) {
        closeSidebar.addEventListener("click", closeSidebarMenu);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener("click", closeSidebarMenu);
    }

    sidebarLinks.forEach(function (link) {

        link.addEventListener("click", function () {

            sidebarLinks.forEach(function (item) {
                item.classList.remove("active");
            });

            link.classList.add("active");

            if (window.innerWidth <= 768) {
                closeSidebarMenu();
            }

        });

    });

    window.addEventListener("resize", function () {

        if (window.innerWidth > 768) {
            closeSidebarMenu();
        }

    });


    // ========================================
    // 2. TOAST NOTIFICATION SYSTEM
    // ========================================

    function createToast(type, title, message) {

        let container = document.querySelector(".toast-container");

        if (!container) {

            container = document.createElement("div");

            container.className = "toast-container";

            container.setAttribute("aria-live", "polite");

            container.setAttribute("aria-atomic", "true");

            document.body.appendChild(container);

        }

        const toast = document.createElement("div");

        toast.className = "toast " + type;

        const icon = type === "success" ? "✓" : "✕";

        const iconElement = document.createElement("div");
        iconElement.className = "toast-icon";
        iconElement.textContent = icon;

        const content = document.createElement("div");
        content.className = "toast-content";

        const heading = document.createElement("h4");
        heading.textContent = title;

        const paragraph = document.createElement("p");
        paragraph.textContent = message;

        content.appendChild(heading);
        content.appendChild(paragraph);

        const closeButton = document.createElement("button");

        closeButton.className = "toast-close";
        closeButton.type = "button";
        closeButton.textContent = "×";
        closeButton.setAttribute("aria-label", "Close notification");

        toast.appendChild(iconElement);
        toast.appendChild(content);
        toast.appendChild(closeButton);

        container.appendChild(toast);

        function removeToast() {

            if (!toast.isConnected) return;

            toast.classList.add("hide");

            setTimeout(function () {
                toast.remove();
            }, 300);

        }

        closeButton.addEventListener("click", removeToast);

        setTimeout(removeToast, 5000);

    }


    // ========================================
    // 3. CONVERT EXISTING MESSAGES INTO TOASTS
    // ========================================

    const successMessages = document.querySelectorAll(".success-message");

    successMessages.forEach(function (element) {

        const message = element.textContent.trim();

        if (message !== "") {

            createToast(
                "success",
                "Sale Saved Successfully!",
                message
            );

            element.style.display = "none";

        }

    });


    const errorMessages = document.querySelectorAll(
        ".error-message, .product-error"
    );

    errorMessages.forEach(function (element) {

        const message = element.textContent.trim();

        if (message !== "") {

            createToast(
                "error",
                "Something Went Wrong!",
                message
            );

            element.classList.add("error-shake");

        }

    });


});