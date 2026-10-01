/**
 * MINI X — CASE STUDY & LANDING PAGE JAVASCRIPT
 * Pure Vanilla JavaScript (ES6+)
 * Performance: IntersectionObserver, Lightbox, Scroll Progress, Mobile Drawer
 */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // ========================================================================
  // 1. SCROLL PROGRESS & NAVBAR SCROLLED STATE
  // ========================================================================
  const siteNav = document.getElementById('siteNav');
  const scrollProgress = document.getElementById('scrollProgress');

  function updateScrollState() {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    
    // Progress calculation
    if (scrollProgress && docHeight > 0) {
      const progressPercent = Math.min(100, Math.max(0, (scrollTop / docHeight) * 100));
      scrollProgress.style.width = `${progressPercent}%`;
    }

    // Nav shadow on scroll
    if (siteNav) {
      if (scrollTop > 30) {
        siteNav.classList.add('scrolled');
      } else {
        siteNav.classList.remove('scrolled');
      }
    }
  }

  window.addEventListener('scroll', updateScrollState, { passive: true });
  updateScrollState();

  // ========================================================================
  // 2. MOBILE NAVIGATION MENU
  // ========================================================================
  const mobileToggle = document.getElementById('mobileToggle');
  const navMenu = document.getElementById('navMenu');

  function toggleMobileMenu(forceState) {
    if (!navMenu || !mobileToggle) return;
    const isOpen = typeof forceState === 'boolean' ? forceState : !navMenu.classList.contains('open');
    navMenu.classList.toggle('open', isOpen);
    mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }

  if (mobileToggle) {
    mobileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      toggleMobileMenu();
    });
  }

  // Close mobile menu when clicking nav link
  if (navMenu) {
    navMenu.querySelectorAll('.nav-link').forEach((link) => {
      link.addEventListener('click', () => {
        toggleMobileMenu(false);
      });
    });
  }

  // Close mobile menu if clicked outside
  document.addEventListener('click', (e) => {
    if (navMenu && navMenu.classList.contains('open')) {
      if (!navMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
        toggleMobileMenu(false);
      }
    }
  });

  // ========================================================================
  // 3. SCROLL REVEAL ANIMATIONS (INTERSECTION OBSERVER)
  // ========================================================================
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const revealElements = document.querySelectorAll('.reveal-item');

  if (prefersReducedMotion) {
    // If reduced motion is requested, immediately show all elements
    revealElements.forEach((el) => {
      el.classList.add('is-revealed');
    });
  } else if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-revealed');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.12,
      rootMargin: '0px 0px -40px 0px'
    });

    revealElements.forEach((el) => {
      revealObserver.observe(el);
    });
  } else {
    // Fallback for older browsers
    revealElements.forEach((el) => {
      el.classList.add('is-revealed');
    });
  }

  // ========================================================================
  // 4. INTERACTIVE IMAGE LIGHTBOX MODAL
  // ========================================================================
  const lightboxModal = document.getElementById('lightboxModal');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const lightboxCloseBtn = document.getElementById('lightboxCloseBtn');

  function openLightbox(src, captionText, altText) {
    if (!lightboxModal || !lightboxImg || !src) return;

    lightboxImg.src = src;
    lightboxImg.alt = altText || captionText || 'Mini X kuvakaappaus';
    
    if (lightboxCaption) {
      lightboxCaption.textContent = captionText || '';
      lightboxCaption.style.display = captionText ? 'block' : 'none';
    }

    lightboxModal.classList.add('active');
    lightboxModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightboxModal) return;
    lightboxModal.classList.remove('active');
    lightboxModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    
    // Clear image src after transition to save memory
    setTimeout(() => {
      if (lightboxImg && !lightboxModal.classList.contains('active')) {
        lightboxImg.src = '';
      }
    }, 280);
  }

  // Attach click listeners to all lightbox trigger elements
  document.querySelectorAll('[data-lightbox]').forEach((item) => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      const src = item.getAttribute('data-lightbox');
      const caption = item.getAttribute('data-caption') || '';
      const img = item.querySelector('img');
      const alt = img ? img.alt : '';
      openLightbox(src, caption, alt);
    });
  });

  if (lightboxCloseBtn) {
    lightboxCloseBtn.addEventListener('click', closeLightbox);
  }

  if (lightboxModal) {
    lightboxModal.addEventListener('click', (e) => {
      if (e.target === lightboxModal || e.target.classList.contains('lightbox-content-wrap')) {
        closeLightbox();
      }
    });
  }

  // Keyboard navigation: Escape key closes modal & mobile menu
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' || e.key === 'Esc') {
      if (lightboxModal && lightboxModal.classList.contains('active')) {
        closeLightbox();
      }
      if (navMenu && navMenu.classList.contains('open')) {
        toggleMobileMenu(false);
      }
    }
  });

  // ========================================================================
  // 5. SMOOTH HEADER OFFSET SCROLLING
  // ========================================================================
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#' || !targetId.startsWith('#')) return;

      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        e.preventDefault();
        const headerOffset = 80;
        const elementPosition = targetElement.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: prefersReducedMotion ? 'auto' : 'smooth'
        });
      }
    });
  });

  console.log('Mini X Case Study loaded successfully.');
});
