// Näyttää / piilottaa postauksen valikon (dropdown)
function toggleMenu(button) {
    const menu = button.nextElementSibling;
    if (menu) menu.classList.toggle("show");
}

// Näyttää postauksen muokkauslomakkeen
function showEditForm(button) {
    const post = button.closest(".post");
    if (!post) return;
    const postView = post.querySelector(".post-view");
    const editForm = post.querySelector(".edit-form");
    if (postView) postView.style.display = "none";
    if (editForm) editForm.style.display = "block";
}

// Peruuttaa muokkauksen ja palauttaa normaalin näkymän
function cancelEditForm(button) {
    const post = button.closest(".post");
    if (!post) return;
    const postView = post.querySelector(".post-view");
    const editForm = post.querySelector(".edit-form");
    if (editForm) editForm.style.display = "none";
    if (postView) postView.style.display = "block";
}

// Näyttää / piilottaa kommenttiosion
function toggleCommentSection(postId) {
    const section = document.getElementById("comments-" + postId);
    if (section) {
        section.classList.toggle("show");
        if (section.classList.contains("show")) {
            const input = section.querySelector(".comment-input");
            if (input) input.focus();
        }
    }
}

// Mobiilisivupalkin (Hamburger) ohjaus
const hamburgerButton = document.getElementById("hamburgerButton");
const sidebar = document.querySelector(".sidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");

function setSidebarOpen(isOpen) {
    if (sidebar) sidebar.classList.toggle("open", isOpen);
    if (hamburgerButton) {
        hamburgerButton.setAttribute("aria-expanded", isOpen);
        hamburgerButton.innerHTML = isOpen ? "&times;" : "&#9776;";
    }
    if (sidebarOverlay) sidebarOverlay.classList.toggle("active", isOpen);
}

if (hamburgerButton) {
    hamburgerButton.addEventListener("click", () => {
        const isOpen = sidebar && sidebar.classList.contains("open");
        setSidebarOpen(!isOpen);
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", () => setSidebarOpen(false));
}