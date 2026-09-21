/**
 * Mini-X - Privacy Policy Scripts
 * Käsittelee mobiilivalikon (Drawer) ja sujuvan vierityksen (Smooth Scroll).
 */

document.addEventListener('DOMContentLoaded', () => {
    // Hamburger-valikon ohjaus
    const hamburgerBtn = document.getElementById('xPrivacyHamburger');
    const drawer = document.getElementById('xPrivacyDrawer');
    const overlay = document.getElementById('xPrivacyOverlay');
    const closeBtn = document.getElementById('xPrivacyClose');

    function toggleXDrawer(open) {
        if (!drawer) return;
        const isOpen = open !== undefined ? open : !drawer.classList.contains('open');
        drawer.classList.toggle('open', isOpen);
        if (overlay) overlay.classList.toggle('open', isOpen);
        if (hamburgerBtn) {
            hamburgerBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    if (hamburgerBtn) hamburgerBtn.addEventListener('click', () => toggleXDrawer(true));
    if (closeBtn) closeBtn.addEventListener('click', () => toggleXDrawer(false));
    if (overlay) overlay.addEventListener('click', () => toggleXDrawer(false));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('open')) {
            toggleXDrawer(false);
        }
    });

    // Drawer linkit: sulje valikko ja vieritä kohteeseen
    document.querySelectorAll('.x-drawer-link').forEach(link => {
        link.addEventListener('click', e => {
            const targetId = link.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                toggleXDrawer(false);
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    setTimeout(() => {
                        targetEl.scrollIntoView({ behavior: 'smooth' });
                    }, 150);
                }
            }
        });
    });

    // Sujuva vieritys sisällysluettelosta
    document.querySelectorAll('.x-chapter-item a').forEach(link => {
        link.addEventListener('click', e => {
            const targetId = link.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    targetEl.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
});
