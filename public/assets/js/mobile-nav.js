/**
 * mobile-nav.js
 * -----------------------------------------------------------------------------
 * Controls the full-screen mobile navigation drawer:
 *
 *   - Opens / closes when [data-menu-toggle] is activated
 *   - Locks body scroll while open (preserves scroll position, no jump)
 *   - Traps Tab focus inside the drawer while open (keyboard accessibility)
 *   - Closes on ESC, on link click, or on viewport resize above 992px
 *   - Emits `mobilenav:open` / `mobilenav:close` on document so other
 *     modules (header.js) can react
 *
 * Depends on:
 *   - .site-header [.is-menu-open]            (hamburger → × transform)
 *   - .mobile-nav [.is-open]                  (drawer reveal)
 *   - body.is-menu-open                       (scroll lock)
 */

(() => {
  'use strict';

  const BREAKPOINT_DESKTOP = 992;  // above this, drawer is irrelevant

  const state = {
    header: null,
    toggle: null,
    drawer: null,
    isOpen: false,
    scrollY: 0,
    lastFocus: null,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  document.addEventListener('partials:loaded', init);

  function init() {
    const header = document.querySelector('.site-header');
    const toggle = document.querySelector('[data-menu-toggle]');
    const drawer = document.querySelector('[data-mobile-nav], .mobile-nav');

    if (!header || !toggle || !drawer) return;
    if (drawer.dataset.mobileNavBound === '1') return;
    drawer.dataset.mobileNavBound = '1';

    state.header = header;
    state.toggle = toggle;
    state.drawer = drawer;

    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-controls', drawer.id || '');
    drawer.setAttribute('aria-hidden', 'true');

    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      state.isOpen ? close() : open();
    });

    // Click on any link inside the drawer → close (but let the navigation
    // happen).
    drawer.addEventListener('click', (e) => {
      const link = e.target.closest('a[href]');
      if (link) close();
    });

    // ESC closes the drawer.
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && state.isOpen) {
        e.preventDefault();
        close();
        state.toggle.focus();
      }
    });

    // Focus trap: constrain Tab to elements inside the drawer.
    drawer.addEventListener('keydown', handleTrap);

    // If the viewport grows past desktop breakpoint while the drawer is
    // open, force-close — the inline nav takes over.
    window.addEventListener('resize', () => {
      if (state.isOpen && window.innerWidth > BREAKPOINT_DESKTOP) close();
    });
  }

  function open() {
    const { header, toggle, drawer } = state;
    if (state.isOpen) return;

    state.lastFocus = document.activeElement;

    // Lock body scroll while preserving position.
    state.scrollY = window.scrollY;
    document.body.style.top = `-${state.scrollY}px`;
    document.body.style.position = 'fixed';
    document.body.style.width = '100%';
    document.body.classList.add('is-menu-open');

    header.classList.add('is-menu-open');
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    toggle.setAttribute('aria-expanded', 'true');

    state.isOpen = true;
    document.dispatchEvent(new CustomEvent('mobilenav:open'));

    // Move focus into the drawer (first focusable item).
    const first = getFocusable(drawer)[0];
    if (first) first.focus({ preventScroll: true });
  }

  function close() {
    const { header, toggle, drawer, scrollY, lastFocus } = state;
    if (!state.isOpen) return;

    header.classList.remove('is-menu-open');
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    toggle.setAttribute('aria-expanded', 'false');

    document.body.classList.remove('is-menu-open');
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    window.scrollTo({ top: scrollY, left: 0, behavior: 'instant' });

    state.isOpen = false;
    document.dispatchEvent(new CustomEvent('mobilenav:close'));

    if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus({ preventScroll: true });
    }
  }

  function handleTrap(e) {
    if (e.key !== 'Tab' || !state.isOpen) return;
    const focusables = getFocusable(state.drawer);
    if (focusables.length === 0) return;

    const first = focusables[0];
    const last  = focusables[focusables.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }

  function getFocusable(container) {
    return Array.from(
      container.querySelectorAll(
        'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])'
      )
    ).filter((el) => el.offsetParent !== null || el === document.activeElement);
  }
})();
