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

// Sulje sivupalkki, modalit ja postauksen muokkauslomakkeet painettaessa Escape-näppäintä
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        const postModal = document.getElementById("post-image-modal");
        if (postModal && postModal.classList.contains("active")) {
            closePostImageModal();
            return;
        }
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

// Julkaisun kuvan katselu suurena (Post Image Lightbox Modal)
function openPostImageModal(imageSrc) {
    if (!imageSrc) return;

    let modal = document.getElementById('post-image-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'post-image-modal';
        modal.className = 'post-modal-overlay';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
            <button type="button" class="avatar-modal-close" id="btn-close-post-modal" aria-label="Sulje">&times;</button>
            <div class="post-modal-card">
                <img src="" alt="Julkaisun kuva" id="post-modal-img" class="post-modal-img">
            </div>
        `;
        document.body.appendChild(modal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('#btn-close-post-modal')) {
                closePostImageModal();
            }
        });
    }

    const img = modal.querySelector('#post-modal-img');
    if (img) img.src = imageSrc;

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closePostImageModal() {
    const modal = document.getElementById('post-image-modal');
    if (modal && modal.classList.contains('active')) {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
}

// Kuvan valinta ja esikatselu julkaisulomakkeessa
function handlePostImageSelect(input) {
    const previewContainer = document.getElementById('post-preview-container');
    const previewImg = document.getElementById('post-preview-img');
    if (input && input.files && input.files[0]) {
        const file = input.files[0];
        if (!file.type.startsWith('image/')) {
            if (typeof Toast !== 'undefined') {
                Toast.error('Valitse kuvatiedosto (JPG, PNG, WEBP tai GIF).');
            } else {
                alert('Valitse kuvatiedosto (JPG, PNG, WEBP tai GIF).');
            }
            input.value = '';
            if (previewContainer) previewContainer.style.display = 'none';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            if (previewImg) previewImg.src = e.target.result;
            if (previewContainer) previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        if (previewImg) previewImg.src = '';
        if (previewContainer) previewContainer.style.display = 'none';
    }
}

function handlePostImageRemove() {
    const input = document.getElementById('post-image-input');
    const previewContainer = document.getElementById('post-preview-container');
    const previewImg = document.getElementById('post-preview-img');
    const postTextarea = document.getElementById('create-post-textarea');
    if (input) input.value = '';
    if (previewImg) previewImg.src = '';
    if (previewContainer) previewContainer.style.display = 'none';
    if (postTextarea) postTextarea.focus();
}

function initComposerImageUpload() {
    const mediaBtn = document.getElementById('btn-trigger-media');
    const imageInput = document.getElementById('post-image-input');
    const removePreviewBtn = document.getElementById('btn-remove-preview');

    if (mediaBtn && imageInput && !mediaBtn.hasAttribute('onclick')) {
        mediaBtn.addEventListener('click', () => {
            imageInput.click();
        });
    }

    if (imageInput) {
        imageInput.addEventListener('change', () => {
            handlePostImageSelect(imageInput);
        });
    }

    if (removePreviewBtn) {
        removePreviewBtn.addEventListener('click', () => {
            handlePostImageRemove();
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initComposerImageUpload);
} else {
    initComposerImageUpload();
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
                        const targetAnchor = (notif.type === 'comment' || notif.type === 'comment_reply' || notif.type === 'best_answer') ? 'comments-' : 'post-';
                        item.href = `index.php?page=home#${targetAnchor}${encodeURIComponent(notif.post_id)}`;
                        item.className = "notification-item unread";
                        item.setAttribute("data-id", notif.id);

                        const iconCol = document.createElement("div");
                        iconCol.className = "notif-icon-col";
                        const iconSpan = document.createElement("span");
                        if (notif.type === "like") {
                            iconSpan.className = "notif-icon notif-like";
                            iconSpan.innerHTML = "&#10084;&#65039;";
                        } else if (notif.type === "best_answer") {
                            iconSpan.className = "notif-icon notif-best-answer";
                            iconSpan.innerHTML = "⭐";
                        } else if (notif.type === "comment_reply") {
                            iconSpan.className = "notif-icon notif-reply";
                            iconSpan.innerHTML = "↩️";
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
                        if (notif.type === "like") {
                            actionSpan.textContent = "tykkäsi julkaisustasi";
                        } else if (notif.type === "best_answer") {
                            actionSpan.textContent = "valitsi vastauksesi parhaaksi! ⭐";
                            actionSpan.style.color = "#ffd700";
                            actionSpan.style.fontWeight = "600";
                        } else if (notif.type === "comment_reply") {
                            actionSpan.textContent = "vastasi kommenttiisi";
                        } else {
                            actionSpan.textContent = "kommentoi julkaisuasi";
                        }
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

/**
 * Avaa tai sulkee alikommentin vastauslomakkeen (Reply Box)
 */
function toggleCommentReplyBox(boxId, mentionText) {
    const box = document.getElementById(boxId);
    if (!box) return;

    const isHidden = (window.getComputedStyle(box).display === 'none');
    if (isHidden) {
        box.style.display = 'block';
        const input = box.querySelector('input[name="comment_content"]');
        if (input) {
            if (mentionText && !input.value) {
                input.value = mentionText;
            }
            input.focus();
        }
    } else {
        box.style.display = 'none';
    }
}

// ==========================================
// AJAX EVENT DELEGATION FOR ALL POST & COMMENT ACTIONS
// ==========================================

document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!form || !(form instanceof HTMLFormElement)) return;

    // 1. Tykkäys (Like / Unlike)
    if (form.classList.contains('action-form') && (form.querySelector('input[name="post_id"]') || form.querySelector('button[name="like_post"]'))) {
        e.preventDefault();
        const btn = form.querySelector('.like-btn') || form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        formData.append('ajax', '1');
        if (!formData.has('like_post')) formData.append('like_post', '1');

        try {
            const res = await fetch(form.action || window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (btn) {
                    btn.classList.toggle('liked', data.is_liked);
                    btn.setAttribute('title', data.is_liked ? 'Peruuta tykkäys' : 'Tykkää');
                    const icon = btn.querySelector('.action-icon');
                    if (icon) icon.innerHTML = data.is_liked ? '&#9829;' : '&#9825;';
                    const count = btn.querySelector('.action-count');
                    if (count) count.textContent = data.like_count > 0 ? data.like_count : '';
                }
            } else if (data.redirect) {
                window.location.href = data.redirect;
            }
        } catch (err) {
            console.error('AJAX Like error:', err);
        }
        return;
    }

    // 2. Kommentin tai alikommentin lisäys (Add comment / Reply)
    if (form.classList.contains('add-comment-form') || form.classList.contains('inline-reply-form')) {
        e.preventDefault();
        const input = form.querySelector('input[name="comment_content"]');
        const content = input ? input.value.trim() : '';
        if (!content) {
            if (typeof Toast !== 'undefined') Toast.error('Kommentti ei voi olla tyhjä.');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.textContent : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Lähetetään...';
        }

        const formData = new FormData(form);
        formData.append('ajax', '1');
        if (!formData.has('add_comment')) formData.append('add_comment', '1');

        try {
            const res = await fetch(form.action || window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                const postId = data.post_id;
                const wrapper = document.getElementById(`comments-list-wrapper-${postId}`);
                if (wrapper) {
                    wrapper.innerHTML = data.comments_html || '';
                }
                const postArticle = document.getElementById(`post-${postId}`);
                if (postArticle) {
                    const countSpan = postArticle.querySelector('.comment-btn .action-count');
                    if (countSpan) {
                        countSpan.textContent = data.comment_count > 0 ? data.comment_count : '';
                    }
                }
                if (input) input.value = '';
                if (form.classList.contains('inline-reply-form')) {
                    const replyBox = form.closest('.comment-reply-box');
                    if (replyBox) replyBox.style.display = 'none';
                }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Kommentti lisätty onnistuneesti!');
                }
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.error || 'Kommentin lisääminen epäonnistui.');
                }
                if (data.redirect) window.location.href = data.redirect;
            }
        } catch (err) {
            console.error('AJAX Comment error:', err);
            if (typeof Toast !== 'undefined') Toast.error('Verkkovirhe kommentoidessa.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        }
        return;
    }

    // 3. Paras vastaus (Best Answer Toggle)
    if (form.classList.contains('best-answer-form')) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('ajax', '1');
        if (!formData.has('toggle_best_answer')) formData.append('toggle_best_answer', '1');

        try {
            const res = await fetch(form.action || window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                const postId = data.post_id;
                const wrapper = document.getElementById(`comments-list-wrapper-${postId}`);
                if (wrapper) {
                    wrapper.innerHTML = data.comments_html || '';
                }
                const postArticle = document.getElementById(`post-${postId}`);
                if (postArticle) {
                    const postUser = postArticle.querySelector('.post-user');
                    let resolvedBadge = postUser?.querySelector('.post-badge-resolved');
                    if (data.is_resolved) {
                        if (!resolvedBadge && postUser) {
                            resolvedBadge = document.createElement('span');
                            resolvedBadge.className = 'post-badge-resolved';
                            resolvedBadge.title = 'Ratkaistu (paras vastaus valittu)';
                            resolvedBadge.textContent = '✓ Ratkaistu';
                            postUser.appendChild(resolvedBadge);
                        }
                    } else if (resolvedBadge) {
                        resolvedBadge.remove();
                    }
                }
                if (typeof Toast !== 'undefined') {
                    if (data.action === 'marked') {
                        Toast.success(data.message || 'Valittu parhaaksi vastaukseksi! ⭐');
                    } else {
                        Toast.info(data.message || 'Parhaan vastauksen merkintä poistettu.');
                    }
                }
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.error || 'Toiminto epäonnistui.');
                }
            }
        } catch (err) {
            console.error('AJAX Best answer error:', err);
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
        return;
    }

    // 4. Kommentin poisto (Delete comment)
    if (form.classList.contains('delete-comment-form')) {
        e.preventDefault();
        if (!confirm('Poistetaanko kommentti?')) return;

        const formData = new FormData(form);
        formData.append('ajax', '1');
        if (!formData.has('delete_comment')) formData.append('delete_comment', '1');

        try {
            const res = await fetch(form.action || window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                const postId = data.post_id;
                const wrapper = document.getElementById(`comments-list-wrapper-${postId}`);
                if (wrapper) {
                    wrapper.innerHTML = data.comments_html || '';
                }
                const postArticle = document.getElementById(`post-${postId}`);
                if (postArticle) {
                    const countSpan = postArticle.querySelector('.comment-btn .action-count');
                    if (countSpan) {
                        countSpan.textContent = data.comment_count > 0 ? data.comment_count : '';
                    }
                }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Kommentti poistettu onnistuneesti!');
                }
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.error || 'Kommentin poisto epäonnistui.');
                }
            }
        } catch (err) {
            console.error('AJAX Delete comment error:', err);
        }
        return;
    }

    // 5. Julkaisun luonti (Create post)
    if (form.classList.contains('create-post')) {
        e.preventDefault();
        const textarea = form.querySelector('textarea[name="content"]');
        const imageInput = form.querySelector('input[type="file"][name="image"]');
        const content = textarea ? textarea.value.trim() : '';
        const hasImage = imageInput && imageInput.files && imageInput.files.length > 0;

        if (!content && !hasImage) {
            if (typeof Toast !== 'undefined') Toast.error('Julkaisussa on oltava tekstiä tai kuva.');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.textContent : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Julkaistaan...';
        }

        const formData = new FormData(form);
        formData.append('ajax', '1');
        if (!formData.has('create_post')) formData.append('create_post', '1');

        try {
            const res = await fetch(form.action || window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success && data.html) {
                const postsSection = document.querySelector('.posts');
                if (postsSection) {
                    const emptyMsg = postsSection.querySelector('.empty-feed, .empty-posts, .empty-search');
                    if (emptyMsg) emptyMsg.remove();

                    const temp = document.createElement('div');
                    temp.innerHTML = data.html.trim();
                    const newPostEl = temp.firstElementChild;
                    if (newPostEl) {
                        postsSection.prepend(newPostEl);
                    }
                }
                if (textarea) textarea.value = '';
                handlePostImageRemove();

                const generalBtn = document.getElementById('btn-type-general');
                if (generalBtn && typeof setPostType === 'function') {
                    setPostType('general', generalBtn);
                }

                const profileHeaderCount = document.querySelector('.profile-header .header-info span');
                if (profileHeaderCount) {
                    const curPosts = document.querySelectorAll('.posts .post').length;
                    profileHeaderCount.textContent = `${curPosts} ${curPosts === 1 ? 'julkaisu' : 'julkaisua'}`;
                }

                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Julkaisu luotu onnistuneesti!');
                }
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.error || 'Julkaisun luominen epäonnistui.');
                }
                if (data.redirect) window.location.href = data.redirect;
            }
        } catch (err) {
            console.error('AJAX Create post error:', err);
            if (typeof Toast !== 'undefined') Toast.error('Verkkovirhe julkaistaessa.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        }
        return;
    }

    // 6. Julkaisun muokkaus (Edit post)
    if (form.classList.contains('edit-form')) {
        e.preventDefault();
        const textarea = form.querySelector('textarea[name="content"]');
        const content = textarea ? textarea.value.trim() : '';
        if (!content) {
            if (typeof Toast !== 'undefined') Toast.error('Julkaisun teksti ei voi olla tyhjä.');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('ajax', '1');
        if (!formData.has('update_post')) formData.append('update_post', '1');

        try {
            const res = await fetch(form.action || window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                const postArticle = form.closest('.post');
                if (postArticle) {
                    const textEl = postArticle.querySelector('.post-view .post-text');
                    if (textEl) {
                        textEl.textContent = data.content;
                    } else {
                        const newTextEl = document.createElement('p');
                        newTextEl.className = 'post-text';
                        newTextEl.textContent = data.content;
                        const media = postArticle.querySelector('.post-media-container');
                        if (media) {
                            media.before(newTextEl);
                        } else {
                            const actions = postArticle.querySelector('.post-actions');
                            if (actions) actions.before(newTextEl);
                        }
                    }
                    cancelEditForm(form);
                }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Julkaisu päivitetty onnistuneesti!');
                }
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.error || 'Päivitys epäonnistui.');
                }
            }
        } catch (err) {
            console.error('AJAX Update post error:', err);
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
        return;
    }

    // 7. Julkaisun poisto (Delete post)
    const deletePostBtn = form.querySelector('button[name="delete_post"]');
    if (deletePostBtn) {
        e.preventDefault();
        if (!confirm('Haluatko varmasti poistaa tämän julkaisun?')) return;

        const postArticle = form.closest('.post');
        const formData = new FormData(form);
        formData.append('ajax', '1');
        if (!formData.has('delete_post')) formData.append('delete_post', '1');

        try {
            const res = await fetch(form.action || window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (postArticle) {
                    postArticle.style.transition = 'opacity 0.3s, transform 0.3s';
                    postArticle.style.opacity = '0';
                    postArticle.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        postArticle.remove();
                        const remainingPosts = document.querySelectorAll('.posts .post');
                        if (remainingPosts.length === 0) {
                            const postsSection = document.querySelector('.posts');
                            if (postsSection) {
                                postsSection.innerHTML = '<p class="empty-feed">Ei julkaisuja vielä.</p>';
                            }
                        }
                        const profileHeaderCount = document.querySelector('.profile-header .header-info span');
                        if (profileHeaderCount) {
                            const count = remainingPosts.length;
                            profileHeaderCount.textContent = `${count} ${count === 1 ? 'julkaisu' : 'julkaisua'}`;
                        }
                    }, 300);
                }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Julkaisu poistettu onnistuneesti!');
                }
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.error || 'Julkaisun poistaminen epäonnistui.');
                }
            }
        } catch (err) {
            console.error('AJAX Delete post error:', err);
        }
        return;
    }

    // 8. Salasanan palautus (Reset Password Form)
    if (form.querySelector('input[name="reset_password_post"]')) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('ajax', '1');

        try {
            const res = await fetch(form.action || window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Salasana päivitetty onnistuneesti!');
                }
                setTimeout(() => {
                    window.location.href = data.redirect || 'index.php?tab=login';
                }, 1200);
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.error || 'Salasanan palautus epäonnistui.');
                }
            }
        } catch (err) {
            console.error('AJAX Reset password error:', err);
            form.submit();
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
        return;
    }

    // 9. Palautteen lähetys (Feedback Form)
    if (form.querySelector('input[name="submit_feedback"]')) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const origText = submitBtn ? submitBtn.textContent : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Lähetetään...';
        }

        const formData = new FormData(form);
        formData.append('ajax', '1');

        try {
            const res = await fetch(form.action || 'index.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Kiitos palautteestasi!');
                }
                form.reset();
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.message || data.error || 'Palautteen lähetys epäonnistui.');
                }
            }
        } catch (err) {
            console.error('AJAX Feedback error:', err);
            form.submit();
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = origText;
            }
        }
        return;
    }

    // 10. Ilmiannon lähetys (Report Form)
    if (form.querySelector('input[name="submit_report"]')) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        const origText = submitBtn ? submitBtn.textContent : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Lähetetään...';
        }

        const formData = new FormData(form);
        formData.append('ajax', '1');

        try {
            const res = await fetch(form.action || 'index.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Kiitos ilmoituksesta!');
                }
                form.reset();
                if (typeof closeReportModal === 'function') {
                    closeReportModal();
                } else {
                    const overlay = document.getElementById('reportModalOverlay');
                    if (overlay) overlay.style.display = 'none';
                }
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.message || data.error || 'Ilmoituksen lähetys epäonnistui.');
                }
            }
        } catch (err) {
            console.error('AJAX Report error:', err);
            form.submit();
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = origText;
            }
        }
        return;
    }
});

// ==========================================
// AJAX ROOM FILTERING & EXPLORE SEARCH
// ==========================================

// Huonesuodatus etusivulla ilman sivun uudelleenlatausta
document.addEventListener('click', (e) => {
    const chip = e.target.closest('.room-chip');
    if (chip && chip.closest('.rooms-bar')) {
        e.preventDefault();
        const url = chip.href;
        const postsSection = document.querySelector('.posts');
        if (!postsSection) {
            window.location.href = url;
            return;
        }

        document.querySelectorAll('.room-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');

        history.pushState(null, '', url);

        const ajaxUrl = url + (url.includes('?') ? '&' : '?') + 'ajax=1';
        fetch(ajaxUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                postsSection.innerHTML = data.html;
            }
        })
        .catch(err => {
            console.error('Room filter error:', err);
            window.location.href = url;
        });
    }
});

// Selaussivun haku ja käyttäjävalinnat
function loadSearchResults(q, updateHistory = true) {
    const url = `index.php?page=selaa&q=${encodeURIComponent(q)}`;
    if (updateHistory) {
        history.pushState(null, '', url);
    }

    document.querySelectorAll('.user-chip').forEach(c => {
        const chipUrl = new URL(c.href, window.location.origin);
        c.classList.toggle('active', chipUrl.searchParams.get('q') === q);
    });

    const ajaxUrl = `${url}&ajax=1`;
    fetch(ajaxUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const postsSection = document.querySelector('.posts');
            if (postsSection) postsSection.innerHTML = data.posts_html;

            let userCard = document.querySelector('.selaa-user-card');
            if (data.user_card_html) {
                if (userCard) {
                    userCard.outerHTML = data.user_card_html;
                } else if (postsSection) {
                    postsSection.insertAdjacentHTML('beforebegin', data.user_card_html);
                }
            } else if (userCard) {
                userCard.remove();
            }
        }
    })
    .catch(err => {
        console.error('Search error:', err);
        window.location.href = url;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const input = searchForm.querySelector('.search-input');
            const q = input ? input.value.trim() : '';
            loadSearchResults(q);
        });
    }

    document.addEventListener('click', (e) => {
        const chip = e.target.closest('.user-chip');
        if (chip && chip.closest('.user-chips')) {
            e.preventDefault();
            const url = new URL(chip.href, window.location.origin);
            const q = url.searchParams.get('q') || '';
            const searchInput = document.querySelector('.search-input');
            if (searchInput) searchInput.value = q;
            loadSearchResults(q);
        }
    });
});

// Sivupalkin uloskirjautuminen AJAXilla
document.addEventListener('click', (e) => {
    const logoutLink = e.target.closest('a[href*="page=logout"]');
    if (logoutLink) {
        e.preventDefault();
        if (!confirm('Haluatko varmasti kirjautua ulos?')) return;

        fetch('index.php?page=logout', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = 'index.php';
            }
        })
        .catch(() => {
            window.location.href = 'index.php?page=logout';
        });
    }
});

// Selaimen takaisin/eteenpäin -painikkeiden tuki (PopState)
window.addEventListener('popstate', () => {
    const params = new URLSearchParams(window.location.search);
    const page = params.get('page') || 'home';
    if (page === 'home') {
        const room = params.get('room') || 'all';
        const targetChip = document.querySelector(`.room-chip[href*="room=${room}"]`) || document.querySelector('.room-chip[href="index.php?page=home"]');
        if (targetChip) {
            document.querySelectorAll('.room-chip').forEach(c => c.classList.remove('active'));
            targetChip.classList.add('active');
            fetch(`index.php?page=home&room=${encodeURIComponent(room)}&ajax=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                const postsSection = document.querySelector('.posts');
                if (postsSection && data.success) postsSection.innerHTML = data.html;
            });
        }
    } else if (page === 'selaa') {
        const q = params.get('q') || '';
        const searchInput = document.querySelector('.search-input');
        if (searchInput) searchInput.value = q;
        loadSearchResults(q, false);
    }
});

