/**
 * Mini-X - Centralized Toast Notification Engine
 * Mahdollistaa kauniiden ja sulavien ilmoitusten näyttämisen koko sovelluksessa.
 */

(function () {
    'use strict';

    const ICONS = {
        success: `<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>`,
        error: `<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>`,
        info: `<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>`,
        warning: `<svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>`
    };

    const DEFAULT_TITLES = {
        success: 'Onnistui',
        error: 'Virhe',
        info: 'Ilmoitus',
        warning: 'Varoitus'
    };

    function getContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            container.setAttribute('aria-live', 'polite');
            document.body.appendChild(container);
        }
        return container;
    }

    const Toast = {
        /**
         * Näyttää uuden Toast-ilmoituksen
         * @param {Object} options
         * @param {string} options.type 'success' | 'error' | 'info' | 'warning'
         * @param {string} options.message Viestin sisältö
         * @param {string} [options.title] Valinnainen otsikko
         * @param {number} [options.duration] Kesto millisekunteina (oletus: success/info 4000ms, error/warning 5500ms)
         */
        show: function (options) {
            if (!options || !options.message) return;

            const type = (options.type && ICONS[options.type]) ? options.type : 'info';
            const message = String(options.message);
            const title = options.title !== undefined ? options.title : (DEFAULT_TITLES[type] || '');
            const duration = Number(options.duration) || (type === 'error' ? 5500 : 4000);

            const container = getContainer();

            // Luodaan elementti
            const toastEl = document.createElement('div');
            toastEl.className = `toast-item toast-${type}`;

            const iconHtml = `<div class="toast-icon">${ICONS[type]}</div>`;
            const titleHtml = title ? `<div class="toast-title">${escapeHtml(title)}</div>` : '';
            const messageHtml = `<div class="toast-message">${escapeHtml(message)}</div>`;
            const closeBtnHtml = `<button type="button" class="toast-close" aria-label="Sulje">&times;</button>`;
            const progressBarHtml = `<div class="toast-progress-bar"><div class="toast-progress-fill"></div></div>`;

            toastEl.innerHTML = `
                ${iconHtml}
                <div class="toast-content">
                    ${titleHtml}
                    ${messageHtml}
                </div>
                ${closeBtnHtml}
                ${progressBarHtml}
            `;

            container.appendChild(toastEl);

            // Animaation käynnistys
            requestAnimationFrame(() => {
                toastEl.classList.add('toast-show');
            });

            const progressFill = toastEl.querySelector('.toast-progress-fill');
            const closeBtn = toastEl.querySelector('.toast-close');

            let remainingTime = duration;
            let startTime = performance.now();
            let isPaused = false;
            let timerId = null;

            function dismiss() {
                if (toastEl.classList.contains('toast-hiding')) return;
                clearTimeout(timerId);
                toastEl.classList.remove('toast-show');
                toastEl.classList.add('toast-hiding');
                toastEl.addEventListener('transitionend', () => {
                    if (toastEl.parentNode) {
                        toastEl.parentNode.removeChild(toastEl);
                    }
                }, { once: true });
            }

            function startTimer() {
                startTime = performance.now();
                if (progressFill) {
                    progressFill.style.transition = `transform ${remainingTime}ms linear`;
                    progressFill.style.transform = 'scaleX(0)';
                }
                timerId = setTimeout(dismiss, remainingTime);
            }

            function pauseTimer() {
                if (isPaused) return;
                isPaused = true;
                clearTimeout(timerId);
                const elapsed = performance.now() - startTime;
                remainingTime = Math.max(0, remainingTime - elapsed);

                if (progressFill) {
                    const computedStyle = window.getComputedStyle(progressFill);
                    const currentTransform = computedStyle.transform;
                    progressFill.style.transition = 'none';
                    progressFill.style.transform = currentTransform;
                }
            }

            function resumeTimer() {
                if (!isPaused) return;
                isPaused = false;
                if (remainingTime > 0) {
                    startTimer();
                } else {
                    dismiss();
                }
            }

            toastEl.addEventListener('mouseenter', pauseTimer);
            toastEl.addEventListener('mouseleave', resumeTimer);

            if (closeBtn) {
                closeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dismiss();
                });
            }

            startTimer();
            return toastEl;
        },

        success: function (message, title, duration) {
            return this.show({ type: 'success', message, title, duration });
        },

        error: function (message, title, duration) {
            return this.show({ type: 'error', message, title, duration });
        },

        info: function (message, title, duration) {
            return this.show({ type: 'info', message, title, duration });
        },

        warning: function (message, title, duration) {
            return this.show({ type: 'warning', message, title, duration });
        }
    };

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Alustetaan palvelimelta (PHP) välitetyt alkutilan ilmoitukset
    function initServerToasts() {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const data = container.getAttribute('data-initial-toasts');
        if (!data) return;

        try {
            const toasts = JSON.parse(data);
            if (Array.isArray(toasts)) {
                toasts.forEach((item, index) => {
                    setTimeout(() => {
                        Toast.show(item);
                    }, index * 120);
                });
            }
        } catch (e) {
            console.error('Virhe alustettaessa Toast-ilmoituksia:', e);
        }

        container.removeAttribute('data-initial-toasts');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initServerToasts);
    } else {
        initServerToasts();
    }

    // Rekisteröidään globaalisti
    window.Toast = Toast;
    window.showToast = function (message, type = 'info', duration) {
        return Toast.show({ message, type, duration });
    };
})();
