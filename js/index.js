document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;
    const themeToggle = document.getElementById("themeToggle");
    const year = document.getElementById("year");

    // Rok ve footeru
    if (year) {
        year.textContent = new Date().getFullYear();
    }

    // Načtení uloženého motivu
    const savedTheme = localStorage.getItem("pulse-theme");
    if (savedTheme === "light") {
        body.classList.remove("theme-dark");
        body.classList.add("theme-light");
        if (themeToggle) {
            themeToggle.innerHTML = '<i class="fa-solid fa-sun"></i>';
        }
    } else {
        body.classList.remove("theme-light");
        body.classList.add("theme-dark");
        if (themeToggle) {
            themeToggle.innerHTML = '<i class="fa-solid fa-moon"></i>';
        }
    }

    // Přepínač dark / light
    if (themeToggle) {
        themeToggle.addEventListener("click", () => {
            const isDark = body.classList.contains("theme-dark");

            if (isDark) {
                body.classList.remove("theme-dark");
                body.classList.add("theme-light");
                themeToggle.innerHTML = '<i class="fa-solid fa-sun"></i>';
                localStorage.setItem("pulse-theme", "light");
            } else {
                body.classList.remove("theme-light");
                body.classList.add("theme-dark");
                themeToggle.innerHTML = '<i class="fa-solid fa-moon"></i>';
                localStorage.setItem("pulse-theme", "dark");
            }
        });
    }
});