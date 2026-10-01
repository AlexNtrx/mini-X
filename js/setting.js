/**
 * Mini-X - Setting Page Scripts
 * Käsittelee asetuslomakkeiden avauksen/sulkemisen, profiilikuvan esikatselun ja tilin poisto -modalin.
 */

// Avaa tai sulkee asetusosion
function toggleSettingForm(id, force) {
    const el = document.getElementById(id);
    if (!el) return;
    const isCurrentlyOpen = el.style.display !== 'none' && el.style.display !== '';
    const nextState = force !== undefined ? force : !isCurrentlyOpen;

    // Jos avataan jokin lomake, suljetaan ensin muut auki olevat lomakkeet
    if (nextState) {
        document.querySelectorAll('.setting-collapsible-wrapper').forEach(w => {
            if (w.id !== id && w.style.display !== 'none') {
                toggleSettingForm(w.id, false);
            }
        });
    }

    el.style.display = nextState ? 'block' : 'none';
    if (nextState) {
        const input = el.querySelector('input:not([type="hidden"])');
        if (input) setTimeout(() => input.focus(), 100);
    } else {
        const form = el.querySelector('form');
        if (form) form.reset();
    }
}

// Suljetaan asetuslomake, jos klikataan sen ulkopuolelle
document.addEventListener('click', (e) => {
    document.querySelectorAll('.setting-collapsible-wrapper').forEach(wrapper => {
        if (wrapper.style.display === 'block') {
            const formId = wrapper.id;
            const wasClickInside = wrapper.contains(e.target);
            const wasClickOnTrigger = e.target.closest(`[data-form-target="${formId}"]`);
            if (!wasClickInside && !wasClickOnTrigger) {
                toggleSettingForm(formId, false);
            }
        }
    });
});

let originalSettingAvatarHtml = '';

document.addEventListener('DOMContentLoaded', () => {
    const avatarContainer = document.getElementById('setting-avatar-container');
    if (avatarContainer) {
        originalSettingAvatarHtml = avatarContainer.innerHTML;
    }

    // Avaa automaattisesti se lomake, jossa tapahtui validointivirhe
    const settingContainer = document.querySelector('.setting-container');
    const autoOpenId = settingContainer?.getAttribute('data-auto-open');
    if (autoOpenId) {
        toggleSettingForm(autoOpenId, true);
    }

    // Tilin poisto -modalin ohjaus
    const deleteModal = document.getElementById('delete-account-modal');
    const openDeleteBtn = document.getElementById('btn-open-delete-modal');
    const cancelDeleteBtn = document.getElementById('btn-cancel-delete');
    const passwordInput = document.getElementById('delete-confirm-password');

    const openModal = () => {
        if (deleteModal) {
            deleteModal.classList.add('active');
            deleteModal.setAttribute('aria-hidden', 'false');
            if (passwordInput) {
                passwordInput.value = '';
                setTimeout(() => passwordInput.focus(), 150);
            }
        }
    };

    const closeModal = () => {
        if (deleteModal) {
            deleteModal.classList.remove('active');
            deleteModal.setAttribute('aria-hidden', 'true');
        }
    };

    openDeleteBtn?.addEventListener('click', openModal);
    cancelDeleteBtn?.addEventListener('click', closeModal);

    deleteModal?.addEventListener('click', (e) => {
        if (e.target === deleteModal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (deleteModal?.classList.contains('active')) {
                closeModal();
            } else {
                document.querySelectorAll('.setting-collapsible-wrapper').forEach(w => {
                    if (w.style.display === 'block') {
                        toggleSettingForm(w.id, false);
                    }
                });
            }
        }
    });
});

// Profiilikuvan esikatselu
function previewSettingAvatar(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 3 * 1024 * 1024) {
            if (typeof Toast !== 'undefined') {
                Toast.error('Kuvan koko saa olla enintään 3 MB.');
            } else {
                alert('Kuvan koko saa olla enintään 3 MB.');
            }
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.getElementById('setting-avatar-container');
            if (container) {
                container.classList.add('clickable-avatar');
                container.setAttribute('title', 'Näytä profiilikuva');
                container.onclick = function() {
                    if (typeof openAvatarModal === 'function') {
                        openAvatarModal(e.target.result);
                    }
                };
                container.innerHTML = `<img src="${e.target.result}" alt="Profiilikuva" class="avatar-preview-img">`;
            }
            
            const saveActions = document.getElementById('setting-avatar-save-actions');
            const changeBtn = document.getElementById('btn-setting-change-avatar');
            const deleteBtn = document.getElementById('btn-setting-delete-avatar');
            
            if (saveActions) saveActions.style.display = 'inline-flex';
            if (changeBtn) changeBtn.style.display = 'none';
            if (deleteBtn) deleteBtn.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}

// Peruuta profiilikuvan esikatselu ja palauta alkuperäinen kuva
function cancelSettingAvatar() {
    const input = document.getElementById('setting-avatar-input');
    if (input) input.value = '';
    
    const container = document.getElementById('setting-avatar-container');
    if (container) {
        container.innerHTML = originalSettingAvatarHtml;
        const initialAvatar = container.getAttribute('data-initial-avatar');
        if (initialAvatar) {
            container.classList.add('clickable-avatar');
            container.setAttribute('title', 'Näytä profiilikuva');
            container.onclick = function() {
                if (typeof openAvatarModal === 'function') {
                    openAvatarModal(initialAvatar);
                }
            };
        } else {
            container.classList.remove('clickable-avatar');
            container.removeAttribute('title');
            container.onclick = null;
        }
    }
    
    const saveActions = document.getElementById('setting-avatar-save-actions');
    const changeBtn = document.getElementById('btn-setting-change-avatar');
    const deleteBtn = document.getElementById('btn-setting-delete-avatar');
    
    if (saveActions) saveActions.style.display = 'none';
    if (changeBtn) changeBtn.style.display = 'inline-flex';
    if (deleteBtn) deleteBtn.style.display = 'inline-flex';
}

// ==========================================
// AJAX SETTING FORMS HANDLER
// ==========================================

document.addEventListener('DOMContentLoaded', () => {
    // 1. Profiilikuvan päivitys ja poisto AJAXilla
    const avatarForm = document.getElementById('setting-avatar-form');
    if (avatarForm) {
        avatarForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitter = e.submitter;
            const isDelete = submitter && submitter.name === 'delete_avatar';
            const isUpdate = submitter && submitter.name === 'update_avatar';

            if (isDelete && !confirm('Haluatko varmasti poistaa profiilikuvasi?')) {
                return;
            }

            const formData = new FormData(avatarForm);
            formData.append('ajax', '1');
            if (isDelete) formData.append('delete_avatar', '1');
            if (isUpdate) formData.append('update_avatar', '1');

            if (submitter) submitter.disabled = true;

            try {
                const res = await fetch('index.php?page=setting', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    if (isDelete) {
                        const container = document.getElementById('setting-avatar-container');
                        if (container) {
                            container.removeAttribute('data-initial-avatar');
                            container.classList.remove('clickable-avatar');
                            container.removeAttribute('title');
                            container.onclick = null;
                            container.innerHTML = `<div class="avatar-fallback" id="setting-avatar-fallback">${data.initials || ''}</div>`;
                        }
                        const delBtn = document.getElementById('btn-setting-delete-avatar');
                        if (delBtn) delBtn.remove();
                        // Update sidebar avatar
                        const sbAvatar = document.querySelector('.sidebar-avatar');
                        if (sbAvatar) sbAvatar.innerHTML = data.initials || '';
                        cancelSettingAvatar();
                    } else if (isUpdate) {
                        const container = document.getElementById('setting-avatar-container');
                        if (container && data.avatar_url) {
                            container.setAttribute('data-initial-avatar', data.avatar_url);
                            container.classList.add('clickable-avatar');
                            container.setAttribute('title', 'Näytä profiilikuva');
                            container.onclick = () => {
                                if (typeof openAvatarModal === 'function') openAvatarModal(data.avatar_url);
                            };
                            container.innerHTML = `<img src="${data.avatar_url}" alt="Profiilikuva" class="avatar-preview-img" id="setting-avatar-img">`;
                        }
                        // Update sidebar avatar
                        const sbAvatar = document.querySelector('.sidebar-avatar');
                        if (sbAvatar && data.avatar_url) {
                            sbAvatar.innerHTML = `<img src="${data.avatar_url}" alt="Avatar" class="sidebar-avatar-img">`;
                        }
                        const saveActions = document.getElementById('setting-avatar-save-actions');
                        const changeBtn = document.getElementById('btn-setting-change-avatar');
                        let delBtn = document.getElementById('btn-setting-delete-avatar');
                        if (saveActions) saveActions.style.display = 'none';
                        if (changeBtn) changeBtn.style.display = 'inline-flex';
                        if (!delBtn && container && data.avatar_url) {
                            delBtn = document.createElement('button');
                            delBtn.type = 'submit';
                            delBtn.name = 'delete_avatar';
                            delBtn.className = 'setting-btn-danger';
                            delBtn.id = 'btn-setting-delete-avatar';
                            delBtn.textContent = 'Poista kuva';
                            delBtn.onclick = () => confirm('Haluatko varmasti poistaa profiilikuvasi?');
                            document.querySelector('.setting-avatar-actions')?.appendChild(delBtn);
                        } else if (delBtn) {
                            delBtn.style.display = 'inline-flex';
                        }
                    }
                    if (typeof Toast !== 'undefined') Toast.success(data.message || 'Päivitetty!');
                } else {
                    if (typeof Toast !== 'undefined') Toast.error(data.error || 'Päivitys epäonnistui.');
                }
            } catch (err) {
                console.error('Avatar AJAX error:', err);
                avatarForm.submit();
            } finally {
                if (submitter) submitter.disabled = false;
            }
        });
    }

    // 2. Asetusten tekstilomakkeet (Nimi, Käyttäjänimi, Sähköposti, Salasana)
    document.querySelectorAll('.setting-collapsible-wrapper form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const origText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Tallennetaan...';
            }

            const formData = new FormData(form);
            formData.append('ajax', '1');

            try {
                const res = await fetch('index.php?page=setting', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    const wrapper = form.closest('.setting-collapsible-wrapper');
                    const settingItem = wrapper?.previousElementSibling;

                    // Display Name
                    if (data.display_name !== undefined) {
                        const valEl = settingItem?.querySelector('.setting-value');
                        if (valEl) valEl.textContent = data.display_name;
                        const headerName = document.querySelector('.setting-username-title');
                        if (headerName) headerName.textContent = data.display_name;
                        const sbName = document.querySelector('.sidebar-username');
                        if (sbName) sbName.textContent = data.display_name;
                    }

                    // Username
                    if (data.username !== undefined) {
                        const valEl = settingItem?.querySelector('.setting-value');
                        if (valEl) valEl.textContent = `@${data.username}`;
                        const headerHandle = document.querySelector('.setting-handle');
                        if (headerHandle) headerHandle.textContent = `@${data.username}`;
                        const sbHandle = document.querySelector('.sidebar-handle');
                        if (sbHandle) sbHandle.textContent = `@${data.username}`;
                    }

                    // Email
                    if (data.email !== undefined) {
                        const valEl = settingItem?.querySelector('.setting-value');
                        if (valEl) valEl.textContent = data.email;
                    }

                    // Password
                    if (form.querySelector('input[type="password"]')) {
                        form.reset();
                    }

                    if (wrapper) toggleSettingForm(wrapper.id, false);
                    if (typeof Toast !== 'undefined') Toast.success(data.message || 'Tallennettu!');
                } else {
                    if (typeof Toast !== 'undefined') Toast.error(data.error || 'Päivitys epäonnistui.');
                }
            } catch (err) {
                console.error('Setting save error:', err);
                form.submit();
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = origText;
                }
            }
        });
    });

    // 3. Tilin poisto AJAXilla
    const deleteAccountForm = document.getElementById('form-delete-account');
    if (deleteAccountForm) {
        deleteAccountForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = deleteAccountForm.querySelector('button[type="submit"]');
            const origText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Poistetaan...';
            }

            const formData = new FormData(deleteAccountForm);
            formData.append('ajax', '1');
            if (!formData.has('delete_account')) formData.append('delete_account', '1');

            try {
                const res = await fetch('index.php?page=setting', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const data = await res.json();

                if (data.success && data.redirect) {
                    if (typeof Toast !== 'undefined') Toast.success(data.message || 'Tili poistettu.');
                    window.location.href = data.redirect;
                } else {
                    if (typeof Toast !== 'undefined') Toast.error(data.error || 'Tilin poistaminen epäonnistui.');
                }
            } catch (err) {
                console.error('Delete account error:', err);
                deleteAccountForm.submit();
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = origText;
                }
            }
        });
    }
});
