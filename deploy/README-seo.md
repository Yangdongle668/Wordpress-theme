# SEO & performance audit — BatteryCo site

A living checklist for pre-launch and every major content change. Runs
through what's already wired up, what needs verification on the live
origin, and the Lighthouse / PageSpeed scoring targets.

---

## 1. What's already shipped

Every page of the site renders with these SEO primitives in place:

- Semantic HTML5 landmarks: `<header>`, `<main id="main">`, `<nav>`,
  `<article>`, `<footer>`
- Per-page `<title>` (unique, under 65 chars) and `<meta name=description>`
  (140–160 chars, natural keywords)
- `<meta name=robots content="index, follow, max-image-preview:large">`
- `<link rel=canonical>` pointing at the `https://www.07691688.xyz/en/…`
  canonical URL
- `<link rel=alternate hreflang>` pairs for `en` + `zh` + `x-default`
- Full Open Graph + Twitter Card (`summary_large_image`) with 1200×630
  image references
- Theme colors for light + dark color schemes
- `<link rel=icon type=image/svg+xml>` + `site.webmanifest` for PWA
- Per-page JSON-LD (Organization, WebPage, Product, Article,
  CollectionPage, AboutPage, ContactPage, Blog, Service — pick the
  right one per page) plus a BreadcrumbList on every detail page

Global site-level assets:

- `public/robots.txt` allows crawl, disallows `/api/`, `/partials/`,
  `/_template.html`, references sitemap
- `public/sitemap.xml` (generated via `scripts/build-sitemap.js`): 13
  URLs with lastmod, changefreq, priority, + hreflang siblings
- `deploy/nginx.conf` sets HSTS, X-Content-Type, Referrer-Policy,
  Permissions-Policy, CSP, and aggressive caching

---

## 2. Pre-launch checklist (do each item on the live origin)

### 2.1 · Domain & TLS

- [ ] HTTPS reachable at `https://www.07691688.xyz/`
- [ ] HTTP redirects to HTTPS (`curl -I http://www.07691688.xyz/`)
- [ ] Apex redirects to www (`curl -I https://example.com/`)
- [ ] `www` canonical across every page (`grep -r canonical public/en/`)
- [ ] SSL Labs grade A or A+ → https://www.ssllabs.com/ssltest/
- [ ] HSTS header present (`curl -sI https://www.07691688.xyz/ | grep -i strict`)
- [ ] HSTS preload submission after 30 days of stable headers
      → https://hstspreload.org/
- [ ] cert auto-renewal timer active (`systemctl list-timers | grep certbot`)

### 2.2 · Crawling & indexing

- [ ] `robots.txt` fetchable and references sitemap
      (`curl https://www.07691688.xyz/robots.txt`)
- [ ] `sitemap.xml` fetchable and contains 13 valid URLs
      (`curl https://www.07691688.xyz/sitemap.xml | head -30`)
- [ ] No accidental `noindex` on production pages
      (`grep -l 'noindex' public/en/*.html public/en/**/*.html` should
      return only `privacy.html`? — actually it returns **nothing** for
      our pages, `noindex` is only on `404.html` and `50x.html`)
- [ ] Google Search Console verified, sitemap submitted
- [ ] Bing Webmaster Tools verified, sitemap submitted
- [ ] Baidu Zhanzhang verified (for the Chinese market)
- [ ] Production origin in Search Console URL Inspection returns
      "URL is available to Google" for at least the homepage, a
      product page, an application page, and an article

### 2.3 · Structured data

Validate every published page with Google's Rich Results Test and
Schema.org Validator:

- https://search.google.com/test/rich-results
- https://validator.schema.org/

- [ ] `/en/` — Organization, WebPage
- [ ] `/en/products/` — CollectionPage, BreadcrumbList
- [ ] `/en/products/polymer-cell-3000.html` — Product, BreadcrumbList
- [ ] `/en/applications/` — CollectionPage, BreadcrumbList
- [ ] `/en/applications/ev.html` — Service, BreadcrumbList
- [ ] `/en/cases/` — CollectionPage, BreadcrumbList
- [ ] `/en/cases/ev-range-doubling.html` — Article, BreadcrumbList
- [ ] `/en/blog/` — Blog
- [ ] `/en/blog/silicon-anodes.html` — Article (with Person author),
      BreadcrumbList
- [ ] `/en/about.html` — AboutPage, Organization, BreadcrumbList
- [ ] `/en/contact.html` — ContactPage, Organization with 3 ContactPoints,
      BreadcrumbList
- [ ] `/en/privacy.html` — WebPage, BreadcrumbList
- [ ] `/en/careers.html` — WebPage, BreadcrumbList

### 2.4 · Meta + social preview

- [ ] Open Graph debugger clean → https://www.opengraph.xyz/
- [ ] Twitter Card validator clean → https://cards-dev.twitter.com/validator
- [ ] LinkedIn Post Inspector clean → https://www.linkedin.com/post-inspector/
- [ ] Every OG image reachable and 1200×630

---

## 3. Lighthouse / PageSpeed targets

Run on the live origin, mobile + desktop profiles. Targets:

| Category       | Target (mobile) | Target (desktop) |
|----------------|-----------------|------------------|
| Performance    | ≥ 90            | ≥ 95             |
| Accessibility  | ≥ 95            | ≥ 95             |
| Best Practices | ≥ 95            | ≥ 95             |
| SEO            | 100             | 100              |

### Core Web Vitals (mobile, p75)

- **LCP**: < 2.5 s   — optimize the hero image (WebP/AVIF + preload on
  landing pages where the hero is the LCP element)
- **INP**: < 200 ms  — should be easy: no framework, minimal JS
- **CLS**:  < 0.1    — every `<img>`/`<figure>` must carry `width` +
  `height` or `aspect-ratio`; hero uses CSS gradients (no layout shift)

### Where to measure

```bash
# 1. Lighthouse CLI, mobile profile
npx lighthouse https://www.07691688.xyz/en/ --preset=desktop --view
npx lighthouse https://www.07691688.xyz/en/ \
    --output=html --output-path=./lh-home.html

# 2. PageSpeed Insights (runs Lighthouse + real-user data)
https://pagespeed.web.dev/analysis?url=https://www.07691688.xyz/en/

# 3. WebPageTest for the full waterfall and filmstrip
https://www.webpagetest.org/
```

---

## 4. Performance levers already baked in

If a page scores under target, check these knobs *before* refactoring:

- **Critical CSS**: move the hero-specific rules above the fold into a
  `<style>` block in `<head>` to eliminate render-block (easy win on
  the homepage)
- **Preload the LCP image**: `<link rel=preload as=image
  href="/assets/images/home/hero-1600.webp" imagesrcset="…" imagesizes="…">`
  in the hero page only
- **`loading="lazy"`** on every below-the-fold `<img>` — already the
  default pattern; verify with `grep -c 'loading="lazy"' public/en/**/*.html`
- **Font stack**: uses system fonts, so no web-font request. If a
  branded font ever lands, subset aggressively and preload it
- **Compression**: gzip (and brotli when compiled in) already on in Nginx
- **Cache policy**: `/assets/*` immutable 1y, HTML 5m must-revalidate
  — configured in `deploy/nginx.conf`
- **Image format**: run `node scripts/img-to-webp.js` before every
  deploy; swap `<img>` → `<picture>` with WebP + AVIF sources

---

## 5. Accessibility review

Our CSS already covers:

- `focus-visible` ring on every interactive element
- `skip-link` at the top of every page (first focusable element)
- Reduced motion via `prefers-reduced-motion`
- Touch-target minimums 44×44 via `@media (hover: none) and (pointer: coarse)`
- High-contrast mode via `@media (forced-colors: active)`
- `aria-label`, `aria-current`, `aria-expanded`, `aria-hidden` wired
  on the header, nav, drawer, filter chips, accordion, forms

Manual checks:

- [ ] Run axe DevTools on every page — 0 Critical, 0 Serious
- [ ] Tab through every page with a keyboard only; every interactive
      element reachable; focus visible at every stop
- [ ] Screen-reader spot-check with VoiceOver / NVDA on homepage,
      contact form, product detail, a case study
- [ ] Forms: error messages announced via the `aria-live="polite"`
      `.form-status` region; field errors tied to inputs via the
      `.field__error` adjacency (visible)
- [ ] Color contrast 4.5:1 for body text, 3:1 for large text → eye
      the blue-on-dark CTA buttons with a contrast checker

---

## 6. Content QA

For every page, verify:

- [ ] Unique `<title>` (< 65 chars)
- [ ] Unique `<meta name=description>` (140–160 chars)
- [ ] Exactly one `<h1>` per page
- [ ] Logical heading order (no h2 → h4 skips)
- [ ] Every `<img>` has a non-empty `alt` (or `alt=""` if purely
      decorative)
- [ ] All internal links resolve (run a link-checker)
- [ ] No placeholder copy left on production (grep for "Lorem",
      "TODO", "placeholder")

Quick link check:

```bash
# From the project root
npx linkinator https://www.07691688.xyz/ --recurse --skip '(mailto|tel):'
```

---

## 7. Monitoring after launch

- Google Search Console → Coverage (weekly), Core Web Vitals (weekly)
- PageSpeed monthly snapshot
- UptimeRobot on `/en/` and `/api/health`
- Server Logs: scan `/var/log/nginx/batteryco.access.log` weekly for
  `5xx` spikes and unexpected `4xx` patterns

The lowest-fidelity signal is the inbox — if contact / inquiry / careers
forms are arriving, the end-to-end path is healthy.
