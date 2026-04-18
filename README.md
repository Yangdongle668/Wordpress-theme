# BatteryCo — Polymer Lithium Battery Corporate Website

Minimalist, full-width, Tesla-inspired marketing site for a polymer lithium
battery manufacturer. Static HTML + vanilla JS up front, a small Node.js
service for the forms, Nginx at the edge — everything containerized with a
one-click Docker deploy to **[07691688.xyz](https://www.07691688.xyz/)**.

No frameworks. No build step for the pages. 13 content pages, i18n-ready,
SEO-complete, self-hosting fonts and images.

---

## Table of contents

1. [Highlights](#-highlights)
2. [Architecture](#-architecture)
3. [Pages](#-pages-13)
4. [One-click deploy (Docker)](#-one-click-deploy-docker)
5. [Local development](#-local-development)
6. [Operations](#-operations)
7. [Configuration reference](#-configuration-reference)
8. [Security & privacy](#-security--privacy)
9. [Performance & SEO](#-performance--seo)
10. [Project layout](#-project-layout)
11. [Further docs](#-further-docs)

---

## ✨ Highlights

- **Performance-first** — static HTML, design-token CSS, lazy-loaded WebP/AVIF
  images, self-hosted variable-weight fonts, zero JS framework runtime
- **Cinematic UX** — full-viewport hero sections, IntersectionObserver-driven
  scroll reveals + parallax, sticky translucent navigation, reduced-motion
  aware
- **SEO-complete** — semantic landmarks, per-page titles + descriptions,
  canonical + hreflang, Open Graph + Twitter Card, JSON-LD
  (`Organization`, `Product`, `Article`, `BreadcrumbList` …),
  `sitemap.xml` + `robots.txt`
- **Accessible** — skip-link, `focus-visible` rings, 44×44 touch targets,
  `prefers-reduced-motion` + `forced-colors` support, ARIA wiring on nav /
  drawer / forms
- **i18n-ready** — `/en/` (primary) + `/zh/` (scaffolded), with hreflang
  pairs and `x-default`
- **Form backend** — Node.js + Express + Nodemailer with honeypot, rate
  limiting, optional reCAPTCHA v3, JSON-lines audit log, résumé uploads
- **One-file deploy** — `docker compose up -d` + a bootstrap script brings
  up Nginx + Node + Let's Encrypt on any Linux VM; `deploy.sh` wraps the
  whole pipeline

---

## 🏗️ Architecture

```
             ┌────────────────────────────────────────────────────┐
             │                  nginx (:80, :443)                 │
             │   TLS termination · HTTP/2 · security headers      │
             │   Static site from /public · /api/* reverse proxy  │
             └────────┬───────────────────────────┬───────────────┘
                      │                           │
          static HTML/CSS/JS/images       /api/contact  · /api/inquiry
                      │                   /api/careers · /api/health
                      ▼                           ▼
           public/ (volume, read-only)    ┌──────────────────┐
                                          │  form service    │
                                          │  Node 20 + Express
                                          │  Nodemailer SMTP │
                                          │  :3000 (backend) │
                                          └────────┬─────────┘
                                                   │
                                                   ▼
                                         SMTP provider (Hostinger /
                                         SES / Mailgun / Postmark / …)

          certbot sidecar ──renews certs every 12h─►  nginx reloads every 6h
```

Three containers, two named volumes (`batteryco_certbot_etc`,
`batteryco_certbot_www`), no database. All state lives in `server/logs/`,
`server/uploads/`, and the certbot volumes.

---

## 📄 Pages (13)

| # | Path                                    | Purpose                           | JSON-LD                        |
|--:|-----------------------------------------|-----------------------------------|--------------------------------|
|  1| `/en/`                                  | Homepage                          | Organization, WebPage          |
|  2| `/en/products/`                         | Product category index            | CollectionPage, Breadcrumb     |
|  3| `/en/products/polymer-cell-3000.html`   | Product detail                    | Product, Breadcrumb            |
|  4| `/en/applications/`                     | Applications (industries) index   | CollectionPage, Breadcrumb     |
|  5| `/en/applications/ev.html`              | Application detail                | Service, Breadcrumb            |
|  6| `/en/cases/`                            | Case studies list                 | CollectionPage, Breadcrumb     |
|  7| `/en/cases/ev-range-doubling.html`      | Case study detail                 | Article, Breadcrumb            |
|  8| `/en/blog/`                             | Blog list                         | Blog                           |
|  9| `/en/blog/silicon-anodes.html`          | Blog article                      | Article, Person, Breadcrumb    |
| 10| `/en/about.html`                        | About the company                 | AboutPage, Organization        |
| 11| `/en/contact.html`                      | Contact (with form)               | ContactPage + 3 ContactPoints  |
| 12| `/en/privacy.html`                      | Privacy policy                    | WebPage, Breadcrumb            |
| 13| `/en/careers.html`                      | Careers (with résumé upload form) | WebPage, Breadcrumb            |

`/zh/` mirrors the same structure as a scaffold; swap content in place.

---

## 🚀 One-click deploy (Docker)

The whole stack runs from one `docker-compose.yml`. On a fresh Linux VM
(Ubuntu 22.04 tested):

```bash
# 0. Prereqs on the VM
sudo apt update && sudo apt install -y docker.io docker-compose-plugin git
sudo usermod -aG docker "$USER"     # re-login for the group change

# 1. Point DNS A records for 07691688.xyz and www.07691688.xyz at this VM.

# 2. Clone
git clone https://github.com/yangdongle668/wordpress-theme.git /opt/batteryco
cd /opt/batteryco

# 3. Two env files
cp .env.docker.example .env
#   edit .env → DOMAIN=07691688.xyz, LETSENCRYPT_EMAIL=admin@07691688.xyz

cp server/.env.example server/.env
#   edit server/.env → fill SMTP_HOST / SMTP_USER / SMTP_PASS / MAIL_TO_*

# 4. Ship it
bash deploy.sh
```

`deploy.sh` runs the full pipeline end-to-end:

1. **preflight** — checks docker/compose, required files, `.env` vars, ports 80/443
2. **pull** — `git pull --ff-only` on the current branch (skip with `--skip-pull`)
3. **TLS** — on first run, `deploy/init-letsencrypt.sh` drops a dummy cert,
   boots nginx, gets a real Let's Encrypt cert via HTTP-01, reloads
4. **build + up** — `docker compose up -d --build --remove-orphans`
5. **health** — polls `/api/health` and probes `https://www.<DOMAIN>/en/`

Re-running is safe. Subsequent deploys skip the TLS bootstrap and just
rebuild + restart.

### Flags

```bash
bash deploy.sh --skip-pull     # don't git pull
bash deploy.sh --staging       # Let's Encrypt STAGING (cert won't be trusted)
bash deploy.sh --force-cert    # re-issue TLS cert even if one exists
bash deploy.sh --help
```

### What gets started

| Container           | Image                 | Role                                            |
|---------------------|-----------------------|-------------------------------------------------|
| `batteryco-nginx`   | `nginx:1.27-alpine`   | TLS, HTTP/2, static site, reverse proxy, 6h reload |
| `batteryco-form`    | built from `./server` | Node 20 / Express form API on :3000 (backend net) |
| `batteryco-certbot` | `certbot/certbot`     | Tries `certbot renew` every 12h                 |

Certificate auto-renewal is fully automatic: certbot requests a renewal
every 12 hours (no-op until T-30 days), nginx reloads every 6 hours so the
new cert is picked up without operator action.

Full runbook with troubleshooting: [`deploy/README-docker.md`](deploy/README-docker.md).

---

## 🧪 Local development

### Static site only

```bash
npx http-server public -p 8080
# visit http://127.0.0.1:8080/en/
```

### With the form service

```bash
cd server
cp .env.example .env
npm install
npm run dev             # listens on http://127.0.0.1:3000
```

Put Nginx in front (dev config in [`deploy/nginx.conf`](deploy/nginx.conf))
or run the form service through a local reverse proxy so `/api/*` maps to
`127.0.0.1:3000`.

### Run the whole stack locally with Docker

```bash
cp server/.env.example server/.env  # SMTP can be dummy for local dev
DOMAIN=localhost LETSENCRYPT_EMAIL=dev@localhost docker compose up --build
# note: TLS will be the dummy self-signed cert unless you point a real domain at this host
```

---

## 🛠️ Operations

### Daily

```bash
# update code + redeploy
cd /opt/batteryco && bash deploy.sh

# tail all logs
docker compose logs -f --tail=100

# restart just one service
docker compose restart form
docker compose restart nginx
```

### Where the data lives

| What                     | Where                                        |
|--------------------------|----------------------------------------------|
| Uploaded résumés         | `./server/uploads/` (bind mount)             |
| Submission audit log     | `./server/logs/submissions.jsonl`            |
| Nginx access / error log | `docker compose logs nginx`                  |
| Form service stdout/err  | `docker compose logs form`                   |
| TLS certs + renewal conf | volume `batteryco_certbot_etc`               |
| ACME webroot             | volume `batteryco_certbot_www`               |

### Backups (minimum)

- `server/.env`  — SMTP credentials (out-of-band, e.g. a password manager)
- `server/logs/submissions.jsonl` — business correspondence audit trail
- `server/uploads/`  — résumés

Nightly `tar` of these + `docker volume` of `batteryco_certbot_etc`,
encrypted with age or openssl, 90-day retention is enough for a marketing
site.

### Rollback

```bash
cd /opt/batteryco
git log --oneline -10          # pick the last good commit
git checkout <sha>
bash deploy.sh --skip-pull
```

No database = rollback is just git + rebuild.

### Uninstall

```bash
cd /opt/batteryco
docker compose down -v         # -v also drops the certbot volumes
```

---

## ⚙️ Configuration reference

### Root `.env` (docker orchestration)

From [`.env.docker.example`](.env.docker.example):

| Variable            | Example             | Purpose                                            |
|---------------------|---------------------|----------------------------------------------------|
| `DOMAIN`            | `07691688.xyz`      | Apex domain; nginx also serves `www.$DOMAIN`       |
| `LETSENCRYPT_EMAIL` | `admin@07691688.xyz`| Cert expiry warnings go here                       |
| `STAGING`           | `0`                 | `1` to use Let's Encrypt staging (untrusted) cert  |

### `server/.env` (form service)

Full list in [`server/.env.example`](server/.env.example). Key ones:

| Variable                     | Default               | Purpose                                  |
|------------------------------|-----------------------|------------------------------------------|
| `NODE_ENV`                   | `production`          |                                          |
| `PORT` / `HOST`              | `3000` / `0.0.0.0`    | Bound inside the container               |
| `TRUST_PROXY`                | `1`                   | Trust nginx's `X-Forwarded-For`          |
| `CORS_ORIGINS`               | *(empty)*             | Comma-separated allowlist                |
| `RATE_LIMIT_WINDOW_MS`       | `900000` (15m)        | Per-IP rate-limit window                 |
| `RATE_LIMIT_MAX`             | `12`                  | Submissions per window per IP            |
| `MAX_UPLOAD_BYTES`           | `10485760` (10 MB)    | Résumé size cap                          |
| `SMTP_HOST` / `PORT` / `SECURE` | Hostinger: `smtp.hostinger.com` / `465` / `true` | SMTP transport |
| `SMTP_USER` / `SMTP_PASS`    | —                     | SMTP auth                                |
| `MAIL_FROM`                  | —                     | Must match `SMTP_USER`'s domain          |
| `MAIL_TO_CONTACT`            | —                     | Recipient of contact form                |
| `MAIL_TO_INQUIRY`            | —                     | Recipient of product inquiry form        |
| `MAIL_TO_CAREERS`            | —                     | Recipient of careers form + résumés      |
| `RECAPTCHA_SECRET`           | *(empty = disabled)*  | reCAPTCHA v3 server-side key             |
| `RECAPTCHA_MIN_SCORE`        | `0.5`                 | Minimum acceptable score                 |

Any SMTP provider works — SES, Mailgun, Postmark, SendGrid, Hostinger,
self-hosted Postfix.

---

## 🔒 Security & privacy

Configured out of the box in [`deploy/nginx-docker.conf`](deploy/nginx-docker.conf):

- TLS 1.2 + 1.3 only, modern ECDHE cipher suite, OCSP stapling
- `Strict-Transport-Security: max-age=63072000; includeSubDomains; preload`
- `Content-Security-Policy` (tight, tuned for reCAPTCHA + inline styles)
- `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`,
  `Permissions-Policy`, `Cross-Origin-Opener-Policy`, `Cross-Origin-Resource-Policy`
- Engineering surfaces blocked: `/_template.html`, `/partials/*`, `/.git`
- Request-body limits on the form endpoints
- Node form service adds: honeypot field, Express rate limiter, input
  validation, file-type sniffing on uploads, JSON-lines audit log

Privacy policy lives at `/en/privacy.html`, with hooks for analytics
opt-in through a cookie bar partial.

---

## ⚡ Performance & SEO

Pre-launch and ongoing audit checklist:
[`deploy/README-seo.md`](deploy/README-seo.md).

Targets (Lighthouse, mobile profile):

| Category       | Target |
|----------------|--------|
| Performance    | ≥ 90   |
| Accessibility  | ≥ 95   |
| Best Practices | ≥ 95   |
| SEO            | 100    |

Already wired up:

- Critical CSS + preload for the LCP hero image on landing pages
- `loading="lazy"` below the fold everywhere
- WebP + AVIF via `scripts/img-to-webp.js` and `<picture>` sources
- Variable-weight woff2 fonts with `font-display: swap` + preload
- Aggressive `Cache-Control: immutable` for `/assets/*`, short
  revalidation for HTML
- gzip (brotli-ready if Nginx is compiled with it)
- Unique `<title>` (< 65 chars) + `<meta description>` (140–160 chars) per page
- Canonical URL + hreflang pairs + `x-default`
- Full JSON-LD per page type (see [pages table](#-pages-13))

Regenerate the sitemap after content changes:

```bash
SITE_ORIGIN=https://www.07691688.xyz node scripts/build-sitemap.js
```

---

## 📦 Project layout

```
.
├── public/                       Nginx document root
│   ├── assets/
│   │   ├── css/                  tokens · base · components · pages/*
│   │   ├── js/                   partials-loader · main · animations · form
│   │   ├── images/               organized by page (sources + webp/avif)
│   │   ├── fonts/                self-hosted Montserrat (variable woff2)
│   │   └── icons/                inline-able SVG set
│   ├── partials/                 header · footer · cookie-bar
│   ├── data/                     products · applications · cases · posts (JSON)
│   ├── en/                       English site — 13 pages
│   ├── zh/                       Chinese scaffold
│   ├── robots.txt
│   └── sitemap.xml               (generated)
├── server/                       Node 20 form service
│   ├── server.js                 Express entry
│   ├── routes/                   /api/{contact,inquiry,careers,health}
│   ├── middleware/               rate-limit · validation · security
│   ├── utils/                    mailer · logger · uploads
│   ├── Dockerfile                multi-stage, tini, non-root
│   └── .env.example
├── deploy/
│   ├── nginx.conf                host-based (PM2) vhost
│   ├── nginx-docker.conf         containerized vhost (used by compose)
│   ├── ecosystem.config.js       PM2 process config
│   ├── init-letsencrypt.sh       one-shot TLS bootstrap (wmnnd pattern)
│   ├── README-deploy.md          host-based runbook
│   ├── README-docker.md          Docker runbook
│   ├── README-images.md          image-pipeline notes
│   └── README-seo.md             SEO + Lighthouse audit
├── scripts/
│   ├── build-sitemap.js          regenerates public/sitemap.xml
│   └── img-to-webp.js            batch image optimization
├── docker-compose.yml            three-service stack
├── .env.docker.example           DOMAIN · LETSENCRYPT_EMAIL · STAGING
├── .dockerignore
├── deploy.sh                     one-click deploy wrapper
└── README.md                     you are here
```

---

## 📚 Further docs

- **Docker deploy runbook** — [`deploy/README-docker.md`](deploy/README-docker.md)
- **Host-based (non-docker) deploy runbook** — [`deploy/README-deploy.md`](deploy/README-deploy.md)
- **SEO + Lighthouse pre-launch audit** — [`deploy/README-seo.md`](deploy/README-seo.md)
- **Real-image pipeline** — [`deploy/README-images.md`](deploy/README-images.md)
- **Font self-hosting** — [`public/assets/fonts/README.md`](public/assets/fonts/README.md)
- **Form backend** — [`server/README.md`](server/README.md)

---

## 🌿 Active branch

`claude/battery-company-website-f3F3m`

## 📜 License

Proprietary. All rights reserved by BatteryCo. Third-party assets (fonts,
icons) are redistributed under their respective licenses — see the per-
directory READMEs.
