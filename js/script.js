// Näyttää / piilottaa postauksen valikon (dropdown)
function toggleMenu(button) {
    const menu = button.nextElementSibling;
    if (!menu) return;
    const isCurrentlyShown = menu.classList.contains("show");
    // Suljetaan muut auki olevat valikot
    document.querySelectorAll(".menu-dropdown.show").forEach(m => m.classList.remove("show"));
    // Avataan klikattu valikko jos se ei ollut auki
    if (!isCurrentlyShown) {
        menu.classList.add("show");
    }
}

// Suljetaan postauksen valikko, jos klikataan sen ulkopuolelle
document.addEventListener("click", (e) => {
    if (!e.target.closest(".post-menu")) {
        document.querySelectorAll(".menu-dropdown.show").forEach(m => m.classList.remove("show"));
    }
});

// Näyttää postauksen muokkauslomakkeen
function showEditForm(button) {
    // Suljetaan mahdolliset muut auki olevat postauksen muokkauslomakkeet
    document.querySelectorAll(".post .edit-form").forEach((form) => {
        if (form.style.display === "block") {
            cancelEditForm(form);
        }
    });

    const post = button.closest(".post");
    if (!post) return;
    const postView = post.querySelector(".post-view");
    const editForm = post.querySelector(".edit-form");
    if (postView) postView.style.display = "none";
    if (editForm) {
        editForm.style.display = "block";
        const textarea = editForm.querySelector("textarea, input[type='text']");
        if (textarea) {
            textarea.focus();
            const val = textarea.value;
            textarea.value = "";
            textarea.value = val;
        }
    }
    // Suljetaan auki oleva valikko
    document.querySelectorAll(".menu-dropdown.show").forEach((m) => m.classList.remove("show"));
}

// Peruuttaa muokkauksen ja palauttaa normaalin näkymän
function cancelEditForm(element) {
    const post = element.closest(".post");
    if (!post) return;
    const postView = post.querySelector(".post-view");
    const editForm = post.querySelector(".edit-form");
    if (editForm) {
        editForm.style.display = "none";
        if (typeof editForm.reset === "function") {
            editForm.reset();
        }
    }
    if (postView) postView.style.display = "block";
}

// Suljetaan postauksen muokkauslomake, jos klikataan sen ulkopuolelle
document.addEventListener("click", (e) => {
    if (e.target.closest(".menu-dropdown") || e.target.closest(".menu-button")) return;

    document.querySelectorAll(".post .edit-form").forEach((editForm) => {
        if (editForm.style.display === "block") {
            if (!editForm.contains(e.target)) {
                cancelEditForm(editForm);
            }
        }
    });
});

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

// Avaa kommenttiosion automaattisesti, jos URL-osoitteessa on ankkuri (#comments-ID)
document.addEventListener("DOMContentLoaded", () => {
    if (window.location.hash) {
        const hash = window.location.hash;
        if (hash.startsWith("#comments-")) {
            const section = document.querySelector(hash);
            if (section) {
                section.classList.add("show");
                section.scrollIntoView({ behavior: "smooth", block: "nearest" });
            }
        }
    }
});

// Mobiilisivupalkin (Hamburger) ohjaus
const hamburgerButton = document.getElementById("hamburgerButton");
const sidebar = document.querySelector(".sidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");
const sidebarCloseButton = document.getElementById("sidebarCloseButton");

function setSidebarOpen(isOpen) {
    if (sidebar) sidebar.classList.toggle("open", isOpen);
    if (sidebarOverlay) sidebarOverlay.classList.toggle("active", isOpen);
    if (hamburgerButton) {
        hamburgerButton.setAttribute("aria-expanded", isOpen ? "true" : "false");
    }
    document.body.classList.toggle("drawer-open", isOpen);
}

if (hamburgerButton) {
    hamburgerButton.addEventListener("click", (e) => {
        e.stopPropagation();
        const isOpen = sidebar && sidebar.classList.contains("open");
        setSidebarOpen(!isOpen);
    });
}

if (sidebarCloseButton) {
    sidebarCloseButton.addEventListener("click", () => setSidebarOpen(false));
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", () => setSidebarOpen(false));
}

// Sulje sivupalkki, avatar-modal ja postauksen muokkauslomakkeet painettaessa Escape-näppäintä
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        const avatarModal = document.getElementById("avatar-modal");
        if (avatarModal && avatarModal.classList.contains("active")) {
            closeAvatarModal();
            return;
        }
        if (sidebar && sidebar.classList.contains("open")) {
            setSidebarOpen(false);
        }
        document.querySelectorAll(".post .edit-form").forEach((editForm) => {
            if (editForm.style.display === "block") {
                cancelEditForm(editForm);
            }
        });
    }
});

// Profiilikuvan katselu suurena (Avatar Lightbox Modal - vain pyöreä kuva)
function openAvatarModal(imageSrc) {
    if (!imageSrc) return;

    let modal = document.getElementById('avatar-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'avatar-modal';
        modal.className = 'avatar-modal-overlay';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
            <button type="button" class="avatar-modal-close" id="btn-close-avatar-modal" aria-label="Sulje">&times;</button>
            <div class="avatar-modal-card">
                <div class="avatar-modal-image-wrapper">
                    <img src="" alt="Profiilikuva" id="avatar-modal-img" class="avatar-modal-img">
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('#btn-close-avatar-modal')) {
                closeAvatarModal();
            }
        });
    }

    const img = modal.querySelector('#avatar-modal-img');
    if (img) img.src = imageSrc;

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeAvatarModal() {
    const modal = document.getElementById('avatar-modal');
    if (modal && modal.classList.contains('active')) {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
}

// Sulje sivupalkki mobiilissa, kun siirrytään sivulle navigaatiolinkistä
document.querySelectorAll(".sidebar nav a").forEach((link) => {
    link.addEventListener("click", () => {
        if (window.innerWidth <= 900) {
            setSidebarOpen(false);
        }
    });
});

// Ilmoitusten reaaliaikainen tarkistus ja automaattinen päivitys
let isCheckingNotifications = false;

function getAppBasePath() {
    let path = window.location.pathname;
    if (!path.endsWith('/') && !/\.[a-zA-Z0-9]+$/.test(path)) {
        path += '/';
    }
    let dir = path.substring(0, path.lastIndexOf('/') + 1);
    if (!dir) dir = '/';
    dir = dir.replace(/\/(pages|api|handlers)\/$/, '/');
    return dir;
}

function checkUnreadNotifications() {
    if (isCheckingNotifications) return;
    isCheckingNotifications = true;

    const basePath = getAppBasePath();
    const notifList = document.querySelector(".notifications-list");

    // Jos ollaan ilmoitussivulla (.notifications-list löytyy DOMista)
    if (notifList) {
        let latestNotifId = parseInt(notifList.getAttribute("data-latest-id") || 0, 10);
        if (!latestNotifId) {
            const firstItem = notifList.querySelector(".notification-item[data-id]");
            if (firstItem) {
                latestNotifId = parseInt(firstItem.getAttribute("data-id") || 0, 10);
            }
        }

        const getNewNotifsUrl = basePath + "api/get-new-notifications.php?since_id=" + encodeURIComponent(latestNotifId);

        fetch(getNewNotifsUrl)
            .then((res) => (res.ok ? res.json() : null))
            .then((data) => {
                if (!data || !data.logged_in) return;

                if (Array.isArray(data.notifications) && data.notifications.length > 0) {
                    const emptyState = notifList.querySelector(".empty-notifs");
                    if (emptyState) {
                        emptyState.remove();
                    }

                    // data.notifications on ID-järjestyksessä (ASC),
                    // joten prependaamalla järjestyksessä uusin tulee listan ylimmäksi
                    data.notifications.forEach((notif) => {
                        const item = document.createElement("a");
                        item.href = `index.php?page=home#${notif.type === 'comment' ? 'comments-' : 'post-'}${encodeURIComponent(notif.post_id)}`;
                        item.className = "notification-item unread";
                        item.setAttribute("data-id", notif.id);

                        const iconCol = document.createElement("div");
                        iconCol.className = "notif-icon-col";
                        const iconSpan = document.createElement("span");
                        if (notif.type === "like") {
                            iconSpan.className = "notif-icon notif-like";
                            iconSpan.innerHTML = "&#10084;&#65039;";
                        } else {
                            iconSpan.className = "notif-icon notif-comment";
                            iconSpan.innerHTML = "&#128172;";
                        }
                        iconCol.appendChild(iconSpan);

                        const contentCol = document.createElement("div");
                        contentCol.className = "notif-content-col";

                        const textDiv = document.createElement("div");
                        textDiv.className = "notif-text";

                        const strong = document.createElement("strong");
                        strong.textContent = notif.actor_name;
                        textDiv.appendChild(strong);
                        textDiv.appendChild(document.createTextNode(" "));

                        const actionSpan = document.createElement("span");
                        actionSpan.textContent = notif.type === "like" ? "tykkäsi julkaisustasi" : "kommentoi julkaisuasi";
                        textDiv.appendChild(actionSpan);
                        contentCol.appendChild(textDiv);

                        if (notif.content_preview && String(notif.content_preview).trim() !== "") {
                            const prevDiv = document.createElement("div");
                            prevDiv.className = "notif-preview";
                            prevDiv.textContent = `"${notif.content_preview}"`;
                            contentCol.appendChild(prevDiv);
                        }

                        const timeSpan = document.createElement("span");
                        timeSpan.className = "notif-time";
                        timeSpan.textContent = notif.time_ago || notif.created_at;
                        if (notif.created_at) timeSpan.title = notif.created_at;
                        contentCol.appendChild(timeSpan);

                        item.appendChild(iconCol);
                        item.appendChild(contentCol);

                        // Lisätään uusi ilmoitus ylimmäksi
                        notifList.prepend(item);

                        if (notif.id > latestNotifId) {
                            latestNotifId = notif.id;
                        }
                    });

                    notifList.setAttribute("data-latest-id", latestNotifId);
                }

                // Käyttäjä on ilmoitussivulla, joten sivupalkin badge ei näy
                const badge = document.querySelector(".nav-item-notif .nav-badge");
                if (badge) badge.remove();
            })
            .catch((err) => {
                console.debug("Uusien ilmoitusten taustahaku:", err);
            })
            .finally(() => {
                isCheckingNotifications = false;
            });

        return;
    }

    // Muilla sivuilla päivitetään sivupalkin lukemattomien ilmoitusten määrä
    const apiUrl = basePath + "api/notifications-count.php";

    fetch(apiUrl)
        .then((res) => {
            if (!res.ok) return null;
            return res.json();
        })
        .then((data) => {
            if (!data || !data.logged_in) return;
            const notifLink = document.querySelector(".nav-item-notif");
            if (!notifLink) return;

            let badge = notifLink.querySelector(".nav-badge");
            const count = parseInt(data.unread_count, 10) || 0;

            if (count > 0) {
                if (!badge) {
                    badge = document.createElement("span");
                    badge.className = "nav-badge";
                    notifLink.appendChild(badge);
                }
                if (badge.textContent !== String(count)) {
                    badge.textContent = count;
                }
            } else if (badge) {
                badge.remove();
            }
        })
        .catch((err) => {
            console.debug("Ilmoitusten taustahaku:", err);
        })
        .finally(() => {
            isCheckingNotifications = false;
        });
}

// Käynnistetään ilmoitusten taustakysely sivun latautuessa
document.addEventListener("DOMContentLoaded", () => {
    checkUnreadNotifications();
    setInterval(checkUnreadNotifications, 2000); 
});
