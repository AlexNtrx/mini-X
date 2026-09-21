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
