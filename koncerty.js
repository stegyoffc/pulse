document.addEventListener("DOMContentLoaded", () => {
    const overlay = document.getElementById("modalOverlay");
    const closeBtn = document.getElementById("closeModal");

    const titleEl = document.getElementById("modalTitle");
    const dateEl = document.getElementById("modalDate");
    const placeEl = document.getElementById("modalPlace");
    const descEl = document.getElementById("modalDescription");
    const imgEl = document.getElementById("modalImage");

    function openModal(data) {
        if (!overlay) return;

        if (titleEl) titleEl.textContent = data.title || "";
        if (dateEl) dateEl.textContent = data.date || "";
        if (placeEl) placeEl.textContent = data.place || "";
        if (descEl) descEl.textContent = data.description || "";

        if (imgEl) {
            imgEl.src = data.poster || "";
            imgEl.alt = data.title
                ? `Plakát koncertu ${data.title}`
                : "Plakát koncertu";
        }

        overlay.classList.add("is-visible");
        document.body.style.overflow = "hidden";
        overlay.setAttribute("aria-hidden", "false");
    }

    function closeModal() {
        if (!overlay) return;
        overlay.classList.remove("is-visible");
        document.body.style.overflow = "";
        overlay.setAttribute("aria-hidden", "true");
    }

    const detailButtons = document.querySelectorAll("button.show-detail");
    detailButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const dataset = btn.dataset;
            const concertEl = btn.closest(".concert");

            const day =
                concertEl?.querySelector(".date span:nth-child(1)")?.textContent.trim() ||
                "";
            const month =
                concertEl?.querySelector(".date span:nth-child(2)")?.textContent.trim() ||
                "";

            const fallbackTitle =
                concertEl?.querySelector(".info h3")?.textContent.trim() || "";
            const fallbackParagraph =
                concertEl?.querySelector(".info p")?.textContent.trim() || "";

            const title = dataset.title || fallbackTitle;
            const date =
                dataset.date || (day && month ? `${day}. ${month}` : "");
            const place = dataset.place || fallbackTitle;
            const description = dataset.description || fallbackParagraph;
            const poster = dataset.poster || "";

            openModal({
                title,
                date,
                place,
                description,
                poster,
            });
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            closeModal();
        });
    }

    if (overlay) {
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) {
                closeModal();
            }
        });
    }

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && overlay?.classList.contains("is-visible")) {
            closeModal();
        }
    });
});