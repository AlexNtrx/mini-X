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

// Ilmoitusten reaaliaikainen tarkistus ja automaattinen päivitys
let isCheckingNotifications = false;

function checkUnreadNotifications() {
    if (isCheckingNotifications) return;
    isCheckingNotifications = true;

    const path = window.location.pathname;
    const basePath = path.includes("/mini-X") ? "/mini-X/" : "./";
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

        const getNewNotifsUrl = basePath.replace(/\/+$/, "") + "/api/get-new-notifications.php?since_id=" + encodeURIComponent(latestNotifId);

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
                        item.href = `index.php?page=home#post-${encodeURIComponent(notif.post_id)}`;
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
                        timeSpan.textContent = notif.created_at;
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
    const apiUrl = basePath.replace(/\/+$/, "") + "/api/notifications-count.php";

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
