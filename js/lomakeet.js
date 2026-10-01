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

      const response = await fetch('handlers/send-password-reset.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        if (typeof Toast !== 'undefined') {
          Toast.success(data.message || 'Palautuslinkki lähetetty sähköpostiisi!');
        }
        if (modalAlertContainer) {
          modalAlertContainer.innerHTML = '';
        }
        if (forgotForm) forgotForm.reset();
      } else {
        if (typeof Toast !== 'undefined') {
          Toast.error(data.message || 'Sähköpostiosoitetta ei löytynyt.');
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

  // --- Sign Up Form AJAX ---
  const signupForm = document.getElementById('form-signup');
  const signUpBtn = document.getElementById('signUp');

  signupForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const usernameInput = document.getElementById('username-signup');
    const passwordInput = document.getElementById('password-signup');
    const emailInput = document.getElementById('email-signup');

    const username = usernameInput?.value.trim() || '';
    const password = passwordInput?.value || '';
    const email = emailInput?.value.trim() || '';

    if (!username || !password || !email) {
      if (typeof Toast !== 'undefined') Toast.error('Täytä kaikki kentät.');
      return;
    }

    const originalText = signUpBtn ? signUpBtn.textContent : '';
    if (signUpBtn) {
      signUpBtn.disabled = true;
      signUpBtn.textContent = 'Rekisteröidään...';
    }

    try {
      const formData = new FormData(signupForm);
      formData.append('ajax', '1');
      if (!formData.has('register_post')) formData.append('register_post', '1');

      const response = await fetch(signupForm.action || 'index.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        if (typeof Toast !== 'undefined') {
          Toast.success(data.message || 'Rekisteröinti onnistui! Voit nyt kirjautua sisään.');
        }
        // Switch to login tab and prefill username
        const loginUsernameInput = document.getElementById('username-login');
        if (loginUsernameInput) {
          loginUsernameInput.value = data.username || username;
        }
        if (passwordInput) passwordInput.value = '';
        switchToLogin();
      } else {
        if (typeof Toast !== 'undefined') {
          Toast.error(data.error || 'Rekisteröinti epäonnistui.');
        }
      }
    } catch (err) {
      console.error('AJAX Signup error:', err);
      signupForm.submit();
    } finally {
      if (signUpBtn) {
        signUpBtn.disabled = false;
        signUpBtn.textContent = originalText;
      }
    }
  });

  // --- Login Form AJAX ---
  const loginForm = document.getElementById('form-login');
  const logInBtn = document.getElementById('logIn');

  loginForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const usernameInput = document.getElementById('username-login');
    const passwordInput = document.getElementById('password-login');

    const username = usernameInput?.value.trim() || '';
    const password = passwordInput?.value || '';

    if (!username || !password) {
      if (typeof Toast !== 'undefined') Toast.error('Täytä käyttäjänimi ja salasana.');
      return;
    }

    const originalText = logInBtn ? logInBtn.textContent : '';
    if (logInBtn) {
      logInBtn.disabled = true;
      logInBtn.textContent = 'Kirjaudutaan...';
    }

    try {
      const formData = new FormData(loginForm);
      formData.append('ajax', '1');
      if (!formData.has('login_post')) formData.append('login_post', '1');

      const response = await fetch(loginForm.action || 'index.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        if (data.reactivation_needed) {
          const reactModal = document.getElementById('reactivate-modal');
          if (reactModal) {
            const subtitle = reactModal.querySelector('.modal-subtitle strong');
            if (subtitle) subtitle.textContent = `@${data.username}`;
            reactModal.classList.add('active');
            reactModal.setAttribute('aria-hidden', 'false');
          }
        } else if (data.redirect) {
          if (typeof Toast !== 'undefined') {
            Toast.success(data.message || 'Kirjautuminen onnistui!');
          }
          setTimeout(() => {
            window.location.href = data.redirect;
          }, 300);
        }
      } else {
        if (typeof Toast !== 'undefined') {
          Toast.error(data.error || 'Virheellinen käyttäjänimi tai salasana.');
        }
      }
    } catch (err) {
      console.error('AJAX Login error:', err);
      loginForm.submit();
    } finally {
      if (logInBtn) {
        logInBtn.disabled = false;
        logInBtn.textContent = originalText;
      }
    }
  });

  // --- Reactivate Modal AJAX ---
  const reactivateModal = document.getElementById('reactivate-modal');
  const reactivateForm = reactivateModal?.querySelector('form');
  if (reactivateForm) {
    reactivateForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitter = e.submitter;
      const isConfirm = submitter && submitter.name === 'confirm_reactivation';
      const isCancel = submitter && submitter.name === 'cancel_reactivation';

      const formData = new FormData(reactivateForm);
      formData.append('ajax', '1');
      if (isConfirm) formData.append('confirm_reactivation', '1');
      if (isCancel) formData.append('cancel_reactivation', '1');

      try {
        const response = await fetch(reactivateForm.action || 'index.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        });
        const data = await response.json();
        if (data.success) {
          if (isConfirm && data.redirect) {
            if (typeof Toast !== 'undefined') Toast.success(data.message || 'Tili aktivoitu!');
            setTimeout(() => { window.location.href = data.redirect; }, 300);
          } else {
            reactivateModal.classList.remove('active');
            reactivateModal.setAttribute('aria-hidden', 'true');
            if (typeof Toast !== 'undefined') Toast.info(data.message || 'Aktivointi peruutettu.');
          }
        } else {
          if (typeof Toast !== 'undefined') Toast.error(data.error || 'Toiminto epäonnistui.');
        }
      } catch (err) {
        reactivateForm.submit();
      }
    });

    reactivateModal.addEventListener('click', (e) => {
      if (e.target === reactivateModal) {
        reactivateModal.classList.remove('active');
        reactivateModal.setAttribute('aria-hidden', 'true');
      }
    });
  }

  // --- Forgot Password Page Form AJAX (pages/forgot-password.php) ---
  const standaloneForgotForm = document.querySelector('.forgot-form');
  if (standaloneForgotForm) {
    standaloneForgotForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const emailInput = standaloneForgotForm.querySelector('input[type="email"]');
      const email = emailInput ? emailInput.value.trim() : '';
      if (!email) return;

      const submitBtn = standaloneForgotForm.querySelector('.forgot-submit-btn');
      const origText = submitBtn ? submitBtn.textContent : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Lähetetään...';
      }

      const formData = new FormData(standaloneForgotForm);
      formData.append('ajax', '1');

      try {
        const response = await fetch(standaloneForgotForm.action || 'handlers/send-password-reset.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        });
        const data = await response.json();
        if (data.success) {
          if (typeof Toast !== 'undefined') Toast.success(data.message || 'Palautuslinkki lähetetty sähköpostiisi!');
          const desc = document.querySelector('.forgot-desc');
          if (desc) {
            desc.style.color = '#00ba7c';
            desc.textContent = data.message || 'Palautuslinkki lähetetty sähköpostiisi! Tarkista postilaatikkosi.';
          }
          standaloneForgotForm.reset();
        } else {
          if (typeof Toast !== 'undefined') Toast.error(data.message || data.error || 'Sähköpostiosoitetta ei löytynyt.');
        }
      } catch (err) {
        standaloneForgotForm.submit();
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = origText;
        }
      }
    });
  }
});