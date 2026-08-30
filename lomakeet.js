document.addEventListener('DOMContentLoaded', () => {
  const goRight = document.getElementById('goRight');
  const goLeft = document.getElementById('goLeft');
  const slideBox = document.getElementById('slideBox');
  const topLayer = document.querySelector('.topLayer');

  // Switch views between Sign Up and Login
  const switchToSignUp = () => {
    if (slideBox) slideBox.style.marginLeft = '0';
    if (topLayer) topLayer.style.marginLeft = '0';
  };

  const switchToLogin = () => {
    const isDesktop = window.innerWidth > 768;
    if (slideBox) slideBox.style.marginLeft = isDesktop ? '50%' : '0';
    if (topLayer) topLayer.style.marginLeft = '-100%';
  };

  goRight?.addEventListener('click', switchToSignUp);
  goLeft?.addEventListener('click', switchToLogin);

  if (document.body.dataset.activeTab === 'login') {
    switchToLogin();
  }

  // Smooth slide transition
  setTimeout(() => {
    [slideBox, topLayer].forEach(el => {
      if (el) el.style.transition = 'margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
    });
  }, 50);

  // --- Forgot Password Modal Logic ---
  const forgotLink = document.getElementById('forgot-password-link');
  const forgotModal = document.getElementById('forgot-password-modal');
  const modalCloseBtn = document.getElementById('modal-close-btn');
  const modalCancelBtn = document.getElementById('modal-cancel-btn');
  const forgotForm = document.getElementById('form-forgot-modal');
  const modalAlertContainer = document.getElementById('modal-alert-container');
  const modalEmailInput = document.getElementById('modal-email');
  const modalSubmitBtn = document.getElementById('modal-submit-btn');

  const openForgotModal = (e) => {
    if (e) e.preventDefault();
    if (forgotModal) {
      forgotModal.classList.add('active');
      forgotModal.setAttribute('aria-hidden', 'false');
      if (modalAlertContainer) modalAlertContainer.innerHTML = '';
      if (modalEmailInput) {
        modalEmailInput.value = '';
        setTimeout(() => modalEmailInput.focus(), 150);
      }
    }
  };

  const closeForgotModal = () => {
    if (forgotModal) {
      forgotModal.classList.remove('active');
      forgotModal.setAttribute('aria-hidden', 'true');
    }
  };

  forgotLink?.addEventListener('click', openForgotModal);
  modalCloseBtn?.addEventListener('click', closeForgotModal);
  modalCancelBtn?.addEventListener('click', closeForgotModal);

  // Close when clicking modal backdrop
  forgotModal?.addEventListener('click', (e) => {
    if (e.target === forgotModal) {
      closeForgotModal();
    }
  });

  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && forgotModal?.classList.contains('active')) {
      closeForgotModal();
    }
  });

  // Submit forgot password request via AJAX
  forgotForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = modalEmailInput?.value.trim();
    if (!email) return;

    if (modalAlertContainer) modalAlertContainer.innerHTML = '';

    const btnText = modalSubmitBtn?.querySelector('.btn-text');
    const originalText = btnText ? btnText.textContent : 'Lähetä linkki';
    if (modalSubmitBtn) {
      modalSubmitBtn.disabled = true;
      if (btnText) btnText.textContent = 'Lähetetään...';
    }

    try {
      const formData = new FormData(forgotForm);
      formData.append('ajax', '1');

      const response = await fetch('send-password-reset.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        if (modalAlertContainer) {
          modalAlertContainer.innerHTML = `<div class="alert alert-success">${data.message || 'Palautuslinkki lähetetty sähköpostiisi!'}</div>`;
        }
        if (forgotForm) forgotForm.reset();
      } else {
        if (modalAlertContainer) {
          modalAlertContainer.innerHTML = `<div class="alert alert-error">${data.message || 'Sähköpostiosoitetta ei löytynyt.'}</div>`;
        }
      }
    } catch {
      // Fallback to standard form submission if fetch/JSON fails
      forgotForm.submit();
    } finally {
      if (modalSubmitBtn) {
        modalSubmitBtn.disabled = false;
        if (btnText) btnText.textContent = originalText;
      }
    }
  });

  // --- Reactivate Modal Logic ---
  const reactivateModal = document.getElementById('reactivate-modal');
  if (reactivateModal) {
    reactivateModal.addEventListener('click', (e) => {
      if (e.target === reactivateModal) {
        reactivateModal.classList.remove('active');
        reactivateModal.setAttribute('aria-hidden', 'true');
      }
    });
  }
});