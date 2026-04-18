# Images

Source imagery for the marketing site. This directory is the canonical
location — every `<picture>` element and CSS `background-image` URL on
the site references a path under `/assets/images/`.

## Structure

```
assets/images/
├── og/                 # 1200×630 Open Graph / Twitter share cards
├── home/               # Homepage imagery
├── products/           # Product catalog + product detail
├── applications/       # Industry landing pages
├── cases/              # Case study imagery
├── blog/               # Blog card covers + article heroes
├── about/              # Factories, team portraits
├── careers/            # Careers page scenes
└── logo.png            # Company logo (used by JSON-LD + footer)
```

## What to commit

- **Source files** (JPG/PNG, ≥ 2400 px wide): **commit these**
- **Derived files** (WebP, AVIF, resized variants): **gitignored** — they
  regenerate on each deploy via `scripts/img-to-webp.js`

The `.gitignore` rules at the project root already implement this:

```
public/assets/images/**/*.webp
public/assets/images/**/*.avif
```

## Pipeline

```bash
# Generate WebP + AVIF variants at 640, 1024, 1600 widths
node scripts/img-to-webp.js

# Or for a subset
node scripts/img-to-webp.js --dir=public/assets/images/home
```

See `scripts/img-to-webp.js` for all flags and `deploy/README-images.md`
for a page-by-page manifest of every slot on the site.
