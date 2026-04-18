# Image manifest — BatteryCo marketing site

Complete catalog of every image slot on the site. Use this as the brief
for a photographer, a stock-image purchase, or an AI-generated image set.
Once the files land in `public/assets/images/`, the existing CSS gradient
placeholders can be replaced in-place with `<picture>` elements (pattern
at the bottom of this file).

---

## Directory structure

```
public/assets/images/
├── og/                       # Open Graph / Twitter share cards (1200×630)
├── home/                     # Homepage
├── products/                 # Product catalog + product detail
├── applications/             # Applications index + detail pages
├── cases/                    # Case studies index + detail
├── blog/                     # Blog cards + article covers
├── about/                    # About page (factory, team, milestones)
└── logo.png                  # Company logo (used in schema.org JSON-LD)
```

## Naming convention

`<page>-<slot>-<width>.{webp,avif,jpg}`

Examples:
- `home/hero-1600.webp`
- `home/hero-1024.webp`
- `home/hero-640.webp`
- `products/pc-3000-main-1600.webp`
- `about/factory-shenzhen-1600.webp`

Widths: generate 640 / 1024 / 1600 variants (via `scripts/img-to-webp.js`).
Original JPG/PNG source can stay beside the WebP for fallback.

---

## Format checklist per source image

Each source image needs, after running `node scripts/img-to-webp.js`:

1. Original `foo.jpg` (source of truth, ≥ 2400 px wide)
2. `foo-640.webp`  + `foo-640.avif`
3. `foo-1024.webp` + `foo-1024.avif`
4. `foo-1600.webp` + `foo-1600.avif`
5. `foo.webp` + `foo.avif` (full-size fallback)

That's 9 output files per source. The script handles all of them.

---

## Per-page slots

### Homepage (`/en/`)

| Slot                    | File base                            | Aspect | W×H suggestion     | Alt text (English)                                             | Subject                                    |
|-------------------------|--------------------------------------|--------|--------------------|----------------------------------------------------------------|--------------------------------------------|
| Hero background         | `home/hero`                          | wide   | 3200×1800          | (decorative, no alt needed — `aria-hidden="true"`)             | Moody close-up of a battery cell stack, electric-blue accents, dark environment |
| Product rail · Power    | `home/product-power`                 | 3:4    | 900×1200           | Power cells for UAV and motive applications                    | Single high-rate pouch cell, studio shot   |
| Product rail · Storage  | `home/product-storage`               | 3:4    | 900×1200           | Long-cycle LFP energy storage cells                            | Rack of LFP prismatic cells                |
| Product rail · Consumer | `home/product-consumer`              | 3:4    | 900×1200           | Ultra-slim pouch cells for consumer devices                    | Thin pouch cell, hand-scale               |
| Product rail · Custom   | `home/product-custom`                | 3:4    | 900×1200           | Bespoke polymer lithium cell development                       | Engineer inspecting custom cell           |
| App tile · EV           | `home/app-ev`                        | 16:9   | 1600×900           | Electric vehicle pack integration                              | EV chassis view showing pack location     |
| App tile · Grid         | `home/app-grid`                      | 16:9   | 1600×900           | Grid-scale battery storage container                           | Container-scale ESS at a substation        |
| App tile · Drone        | `home/app-drone`                     | 16:9   | 1600×900           | UAV with battery-powered flight                                | Commercial drone in flight                 |
| Case preview · 1        | `home/case-ev`                       | 3:2    | 1200×800           | Tier-1 automotive EV production line                           | EV manufacturing line                      |
| Case preview · 2        | `home/case-grid`                     | 3:2    | 1200×800           | 22 MWh grid-scale deployment                                   | ESS container, sunset                      |
| Blog preview · 1        | `home/blog-silicon`                  | 16:9   | 1280×720           | Silicon anode microscopy                                       | Electron-microscope image of silicon anode |
| Blog preview · 2        | `home/blog-coating`                  | 16:9   | 1280×720           | Electrode coating line                                         | Coating machine in operation               |
| Blog preview · 3        | `home/blog-thermal`                  | 16:9   | 1280×720           | Thermal runaway test setup                                     | Battery safety testing rig                 |

OG share card: `og/home.jpg` — 1200×630 — hero crop + tagline overlay.

---

### Products index (`/en/products/`)

| Slot                          | File base                       | Aspect | Alt text                                                   |
|-------------------------------|---------------------------------|--------|------------------------------------------------------------|
| Platform 01 (Power) media     | `products/platform-power`       | 4:3    | BatteryCo power cells for motive and UAV applications      |
| Platform 02 (Storage) media   | `products/platform-storage`     | 4:3    | Prismatic LFP cells for energy storage systems             |
| Platform 03 (Consumer) media  | `products/platform-consumer`    | 4:3    | Ultra-slim pouch cells for consumer electronics            |
| Platform 04 (Custom) media    | `products/platform-custom`      | 4:3    | Custom cell development at the NPI line                    |
| Per-SKU product card (×9)     | `products/<sku>-card`           | 4:3    | `<SKU description>`                                        |

OG: `og/products.jpg` — 1200×630.

---

### Product detail (e.g. `/en/products/polymer-cell-3000.html`)

| Slot                | File base             | Aspect | Alt text                                                        |
|---------------------|-----------------------|--------|-----------------------------------------------------------------|
| Hero main view      | `products/pc-3000-main` | 1:1  | PC-3000 polymer lithium pouch cell, front view                  |
| Gallery thumb 1     | `products/pc-3000-angle`| 1:1  | PC-3000, three-quarter view                                     |
| Gallery thumb 2     | `products/pc-3000-detail`| 1:1 | PC-3000, terminal detail                                        |
| Gallery thumb 3     | `products/pc-3000-scale`| 1:1  | PC-3000, hand-scale reference                                   |
| App tile · Drone    | `products/pc-3000-app-drone` | 16:9 | Drone powered by PC-3000 pack                                |
| App tile · LEV      | `products/pc-3000-app-lev`   | 16:9 | Light EV pack built with PC-3000                             |
| App tile · Tools    | `products/pc-3000-app-tools` | 16:9 | Cordless power tool running on PC-3000                      |

OG: `og/pc-3000.jpg` — 1200×630 — product shot + SKU + specs overlay.

Duplicate this layout for every new product detail page (swap `pc-3000` for the SKU).

---

### Applications

**Index (`/en/applications/`)**

Each of the 8 industry tiles needs a 16:9 full-bleed background.

| Industry         | File base                    | Subject                                                  |
|------------------|------------------------------|----------------------------------------------------------|
| EV               | `applications/ev-tile`       | EV chassis view or production line                       |
| Energy Storage   | `applications/grid-tile`     | Grid-scale BESS container, preferably at dusk            |
| Drones & UAV     | `applications/drone-tile`    | Commercial UAV in flight or with pilot                   |
| Medical          | `applications/medical-tile`  | Portable medical device (ventilator, defibrillator)      |
| Consumer         | `applications/consumer-tile` | Smartwatch, laptop, or tablet interior                   |
| Robotics         | `applications/robotics-tile` | AGV / AMR in a warehouse                                 |
| Marine           | `applications/marine-tile`   | Electric ferry or hybrid yacht                           |
| Aerospace        | `applications/aerospace-tile`| eVTOL aircraft or scientific satellite                   |

**Detail page (`/en/applications/ev.html`)**

| Slot               | File base                     | Aspect | Subject                                          |
|--------------------|-------------------------------|--------|--------------------------------------------------|
| Hero background    | `applications/ev-hero`        | wide   | EV close-up or charging station                  |
| Solution media     | `applications/ev-solution`    | 4:5    | EV pack cross-section or assembly                |
| Product reco × 3   | (reuse `products/*`)          | 4:3    | From product catalog                             |
| Case 1 / 2         | (reuse `home/case-*`)         | 3:2    | From case studies                                |

Same layout per industry detail page (swap `ev` slug).

---

### Case studies

**Index (`/en/cases/`)**

Reuse home `case-*` shots + add 9 case card images (3:2) for the standard grid.

**Detail (e.g. `/en/cases/ev-range-doubling.html`)**

| Slot                 | File base                     | Aspect | Subject                                          |
|----------------------|-------------------------------|--------|--------------------------------------------------|
| Hero background      | `cases/ev-range-hero`         | wide   | The production EV the case study is about       |
| Gallery × 6          | `cases/ev-range-01` … `-06`   | 4:3    | Cells / pack / chassis / production line views   |

OG: `og/case-ev.jpg` — 1200×630.

---

### Blog

**Index (`/en/blog/`)**

Every article needs a card image. Reuse the 3 blog-preview images from the
homepage plus:

| Article slug                  | File base                    | Aspect | Subject                              |
|-------------------------------|------------------------------|--------|--------------------------------------|
| silicon-anodes                | `blog/silicon-anodes`        | 16:9   | Silicon anode electron microscopy    |
| coating-tolerance             | `blog/coating-tolerance`     | 16:9   | Coating line precision               |
| thermal-runaway-prevention    | `blog/thermal-runaway`       | 16:9   | Battery thermal test chamber         |
| high-rate-pouch-cells         | `blog/high-rate-pouch`       | 16:9   | Discharge curve / scope screen       |
| ...                           | ...                          | 16:9   | ...                                  |

**Article detail (e.g. `/en/blog/silicon-anodes.html`)**

| Slot               | File base                     | Aspect | Subject                         |
|--------------------|-------------------------------|--------|---------------------------------|
| Cover (21:9)       | `blog/silicon-anodes-cover`   | 21:9   | Hero imagery for the article    |
| Author avatar      | `about/team-lin-wei`          | 1:1    | Portrait                         |
| Inline figures     | `blog/silicon-anodes-fig-01`… as needed | — | Diagrams, microscopy, charts    |

OG: `og/post-<slug>.jpg` — 1200×630 — cover crop + headline overlay.

---

### About (`/en/about.html`)

| Slot                       | File base                        | Aspect | Alt text                                    |
|----------------------------|----------------------------------|--------|---------------------------------------------|
| Hero background            | `about/hero`                     | wide   | BatteryCo Shenzhen headquarters             |
| Mission media              | `about/mission`                  | 5:4    | Engineer at a cycler bank                   |
| Facility · Shenzhen        | `about/factory-shenzhen`         | 16:10  | Shenzhen plant, coating line                |
| Facility · Suzhou          | `about/factory-suzhou`           | 16:10  | Suzhou prismatic assembly                   |
| Facility · Bac Ninh        | `about/factory-bacninh`          | 16:10  | Vietnam pouch line                          |
| Team portraits × 6         | `about/team-<name>`              | 1:1    | Portrait of <name>                          |

OG: `og/about.jpg` — 1200×630.

---

### Contact (`/en/contact.html`)

| Slot           | File base           | Aspect | Subject                                                  |
|----------------|---------------------|--------|----------------------------------------------------------|
| Map overlay    | `contact/globe-map` | 21:9   | World map with office pins (illustration or photograph)  |

---

### Careers (`/en/careers.html`)

| Slot               | File base                  | Aspect | Subject                                     |
|--------------------|----------------------------|--------|---------------------------------------------|
| Hero background    | `careers/hero`             | wide   | Team collaboration shot, BatteryCo facility |
| Scene · R&D lab    | `careers/scene-rnd`        | 5:4    | Engineer in dry room / at a cycler bank     |
| Scene · Factory    | `careers/scene-factory`    | 5:4    | Operator / automation on coating line       |
| Scene · Commercial | `careers/scene-commercial` | 5:4    | Engineer with a customer at a whiteboard    |

---

## How to replace a gradient placeholder with a real image

All page CSS currently ships a `<div class="ph-img ph-img--*">` gradient as
a stand-in. Two paths to upgrade in-place once real files are ready.

### Path A — `<picture>` element (recommended for content imagery)

```html
<!-- Before -->
<div class="card__media">
  <div class="ph-img ph-img--power" aria-hidden="true"></div>
</div>

<!-- After -->
<div class="card__media">
  <picture>
    <source type="image/avif"
            srcset="/assets/images/home/product-power-640.avif 640w,
                    /assets/images/home/product-power-1024.avif 1024w,
                    /assets/images/home/product-power-1600.avif 1600w"
            sizes="(min-width: 992px) 33vw, 100vw" />
    <source type="image/webp"
            srcset="/assets/images/home/product-power-640.webp 640w,
                    /assets/images/home/product-power-1024.webp 1024w,
                    /assets/images/home/product-power-1600.webp 1600w"
            sizes="(min-width: 992px) 33vw, 100vw" />
    <img src="/assets/images/home/product-power-1024.jpg"
         alt="Power cells for UAV and motive applications"
         loading="lazy"
         decoding="async"
         width="1024" height="1365" />
  </picture>
</div>
```

### Path B — CSS background (use for decorative `::before` layers or when the element already has layout constraints)

```css
/* Before — pages/home.css */
.ph-img--power {
  background: linear-gradient(135deg, #1B2438 0%, #0A84FF 80%);
}

/* After */
.ph-img--power {
  background:
    url('/assets/images/home/product-power-1024.webp') center/cover no-repeat,
    linear-gradient(135deg, #1B2438 0%, #0A84FF 80%);
}
```

The gradient stays as a layered fallback — if the WebP fails to load for
any reason, users still see the intended tint.

---

## Where to source imagery

- **Commissioned photography** (best): product shots, factory, team portraits
- **Stock libraries**: Adobe Stock, Getty iStock, Shutterstock for generic
  industrial / scientific / lifestyle imagery
- **Free stock**: Unsplash (unsplash.com), Pexels (pexels.com) — check the
  license, most are royalty-free but some require attribution
- **Rendered / AI**: product render for custom SKUs, synthetic environment
  shots. Do NOT use AI-generated imagery for people/portraits on the team
  page — source real photos of real people

Every image must have a non-empty `alt` attribute (or `alt=""` if purely
decorative and already described by surrounding text). SEO and
accessibility both depend on it.

---

## Pipeline

After dropping source JPG/PNG files into `public/assets/images/`:

```bash
# Generate WebP + AVIF responsive variants
node scripts/img-to-webp.js

# Regenerate sitemap (image changes don't require this, but routine)
node scripts/build-sitemap.js
```

Generated WebP/AVIF files are gitignored (see `.gitignore`) — only the
source JPG/PNG originals are committed. The output gets regenerated on
each deploy.
