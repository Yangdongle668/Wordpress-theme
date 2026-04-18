/**
 * partials-loader.js
 * -----------------------------------------------------------------------------
 * Lightweight HTML-include mechanism for static pages. Fetches shared partials
 * (header, footer, cookie bar) and injects them into slot elements so every
 * page renders the same chrome without a build step.
 *
 * Usage in page HTML:
 *
 *   <div data-include="/partials/header.html"></div>
 *   <div data-include="/partials/footer.html"></div>
 *
 * After injection:
 *   - Dispatches `partials:loaded` on document once every include resolves.
 *   - Sets aria-current="page" on the nav link whose [data-path] matches the
 *     current pathname (or any [href] ending with the pathname).
 *   - Adds the `js` class and removes `no-js` on <html> so CSS can light up
 *     JS-enhanced behavior.
 *
 * Loads with `defer`; runs independently of other modules.
 */

(() => {
  'use strict';

  const root = document.documentElement;
  root.classList.remove('no-js');
  root.classList.add('js');

  const INCLUDE_ATTR = 'data-include';
  const includes = Array.from(document.querySelectorAll(`[${INCLUDE_ATTR}]`));

  if (includes.length === 0) {
    markCurrentNav();
    fireReady();
    return;
  }

  // In-memory cache so repeated slots for the same partial only fetch once.
  const cache = new Map();

  Promise.all(includes.map(loadInto))
    .then(() => {
      markCurrentNav();
      fireReady();
    })
    .catch((err) => {
      console.error('[partials] load failed:', err);
      fireReady(); // still fire so dependent modules don't hang
    });

  /**
   * Fetch a partial and replace the slot element with its parsed children.
   * Keeping the slot itself out of the DOM avoids wrapper-div pollution.
   */
  async function loadInto(slot) {
    const url = slot.getAttribute(INCLUDE_ATTR);
    if (!url) return;

    try {
      let html;
      if (cache.has(url)) {
        html = await cache.get(url);
      } else {
        const fetchPromise = fetch(url, { credentials: 'same-origin' }).then(
          (res) => {
            if (!res.ok) throw new Error(`HTTP ${res.status} for ${url}`);
            return res.text();
          }
        );
        cache.set(url, fetchPromise);
        html = await fetchPromise;
      }

      // Parse into a fragment so inline <script> tags can be re-inserted
      // (a cloned <script> from innerHTML doesn't execute).
      const template = document.createElement('template');
      template.innerHTML = html.trim();
      const fragment = template.content;

      // Re-create any <script> tags so the browser actually runs them.
      fragment.querySelectorAll('script').forEach((oldScript) => {
        const newScript = document.createElement('script');
        for (const { name, value } of oldScript.attributes) {
          newScript.setAttribute(name, value);
        }
        newScript.textContent = oldScript.textContent;
        oldScript.replaceWith(newScript);
      });

      slot.replaceWith(fragment);
    } catch (err) {
      console.error(`[partials] failed to load ${url}:`, err);
      slot.remove();
    }
  }

  /**
   * Flag the nav link matching the current page so CSS can style the
   * persistent active underline and screen readers announce "current page".
   */
  function markCurrentNav() {
    const here = normalize(window.location.pathname);

    const links = document.querySelectorAll(
      '.site-nav__link, .mobile-nav__link, .site-footer__list a'
    );

    links.forEach((a) => {
      const href = a.getAttribute('href');
      if (!href) return;
      const target = normalize(new URL(href, window.location.origin).pathname);
      if (target && (target === here || (target !== '/' && here.startsWith(target)))) {
        a.setAttribute('aria-current', 'page');
      }
    });
  }

  function normalize(pathname) {
    if (!pathname) return '/';
    // Treat '/foo/index.html' and '/foo/' as equivalent.
    return pathname.replace(/index\.html?$/, '').replace(/\/+$/, '/') || '/';
  }

  function fireReady() {
    document.dispatchEvent(new CustomEvent('partials:loaded'));
  }
})();
