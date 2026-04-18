# Polymer Lithium Battery — Corporate Website

A high-performance, full-width, minimalist corporate showcase site for a polymer
lithium battery manufacturer. Built as a static HTML site with a lightweight
Node.js form-handling service, served by Nginx.

---

## ✨ Goals

- **Performance-first**: static HTML, inlined critical CSS, lazy-loaded images
  (WebP/AVIF), zero frontend framework runtime.
- **Cinematic UX**: full-viewport hero sections, scroll-triggered fade/slide
  animations, smooth sticky navigation.
- **SEO-ready**: semantic HTML5, per-page meta + Open Graph + Twitter Card +
  JSON-LD (`Organization`, `Product`, `Article`, `BreadcrumbList`), `sitemap.xml`
  and `robots.txt`.
- **Responsive**: fluid full-width layout across 1440 / 1200 / 992 / 768 / 480
  breakpoints.
- **No login**: public-facing showcase only. Forms (contact / product inquiry /
  careers) are handled by a separate Node.js service.
- **i18n-ready**: `/en/` (primary) and `/zh/` (placeholder) URL structure.

---

## 🏗️ Architecture

```
.
├── public/                       # Static site root (Nginx document root)
│   ├── assets/
│   │   ├── css/
│   │   │   ├── tokens.css        # Design tokens (colors, type, spacing)
│   │   │   ├── base.css          # Reset + typography + utility
│   │   │   ├── components.css    # Buttons, cards, forms, nav, footer
│   │   │   └── pages/            # Per-page styles
│   │   ├── js/
│   │   │   ├── partials-loader.js
│   │   │   ├── main.js
│   │   │   ├── animations.js
│   │   │   └── form.js
│   │   ├── images/               # Organized by page
│   │   ├── fonts/                # Self-hosted fonts
│   │   └── icons/                # SVG icons
│   ├── partials/                 # Header / Footer / Cookie bar
│   ├── data/                     # JSON: products, applications, cases, posts
│   ├── en/                       # English pages (13 total)
│   │   ├── index.html
│   │   ├── products/
│   │   ├── applications/
│   │   ├── cases/
│   │   ├── blog/
│   │   ├── about.html
│   │   ├── contact.html
│   │   ├── privacy.html
│   │   └── careers.html
│   ├── zh/                       # Chinese (placeholder)
│   ├── robots.txt
│   ├── sitemap.xml               # Generated
│   └── favicon.ico
├── server/                       # Node.js form service
│   ├── server.js                 # Express entry
│   ├── routes/                   # /api/contact, /api/inquiry, /api/careers
│   ├── middleware/               # security, rate-limit, logger
│   ├── utils/                    # mailer (nodemailer)
│   ├── uploads/                  # Resume uploads (gitignored)
│   ├── logs/                     # Submission logs (gitignored)
│   ├── .env.example
│   └── package.json
├── deploy/
│   ├── nginx.conf                # Nginx reverse-proxy + static config
│   ├── ecosystem.config.js       # PM2 process config
│   └── README-deploy.md          # Deployment runbook
├── scripts/
│   ├── build-sitemap.js          # Generate sitemap.xml
│   └── img-to-webp.js            # Batch image optimization
├── .gitignore
└── README.md
```

---

## 📄 Pages (13)

| # | Path                              | Purpose                          |
|---|-----------------------------------|----------------------------------|
| 1 | `/en/`                            | Home                             |
| 2 | `/en/products/`                   | Product category index           |
| 3 | `/en/products/<slug>/`            | Product detail                   |
| 4 | `/en/applications/`               | Applications (industries) index  |
| 5 | `/en/applications/<slug>/`        | Application detail               |
| 6 | `/en/cases/`                      | Case studies list                |
| 7 | `/en/cases/<slug>/`               | Case study detail                |
| 8 | `/en/blog/`                       | Blog list                        |
| 9 | `/en/blog/<slug>/`                | Blog article                     |
| 10| `/en/about.html`                  | About the company                |
| 11| `/en/contact.html`                | Contact                          |
| 12| `/en/privacy.html`                | Privacy policy                   |
| 13| `/en/careers.html`                | Careers                          |

---

## 🚀 Local Development

```bash
# Serve the static site (any static server works)
npx http-server public -p 8080

# Run the form service
cd server
cp .env.example .env
npm install
npm run dev   # listens on http://127.0.0.1:3000
```

Nginx in development can proxy `/api/*` → `127.0.0.1:3000` and serve `public/`
as document root. See `deploy/nginx.conf`.

---

## 🧭 Build / Deploy

- Static assets are shipped as-is.
- `scripts/build-sitemap.js` regenerates `public/sitemap.xml`.
- `scripts/img-to-webp.js` produces WebP siblings for images under
  `public/assets/images/`.
- Node service is managed by PM2 via `deploy/ecosystem.config.js`.
- HTTPS, gzip, brotli, long-cache headers, and security headers are configured
  in `deploy/nginx.conf`.

---

## 🌿 Branch

Active development: `claude/battery-company-website-f3F3m`

## 📜 License

Proprietary. All rights reserved by the company.
