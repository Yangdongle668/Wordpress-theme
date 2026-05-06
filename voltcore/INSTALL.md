# VoltCore — install & customize (5 minutes)

VoltCore is a Tesla-inspired, **Elementor-native**, SEO-first WordPress
theme for battery, EV and clean-energy brands. On activation it
**seeds the whole site** — pages, posts, products, menus, widgets,
permalinks — so you have a working marketing site before touching a
single setting.

Bundled out of the box:

* **18 VoltCore-branded Elementor widgets** (drag-and-drop under the
  "VoltCore" category): Navbar, Footer, Hero, Stats Row, Icon Box,
  Logo Cloud, Marquee, Feature Card, Stat, Split, CTA, Post Grid,
  Contact Grid, Careers Hero, Press Kit, Press List, Legal Hero,
  Legal TOC, Team Grid, Timeline, FAQ, Product Hero, Product Specs,
  Breadcrumbs.
* **One-click Elementor import** — VoltCore → Import Demo →
  Import Elementor templates → every page becomes an Elementor
  document, images go through the Media Library, header/footer/404
  land as Theme Builder templates.
* **3 selectable homepage layouts** (Classic / Grid / Story) — even
  without Elementor.
* **6 sample blog articles** + **3 sample products** with images and
  categories.
* **10 pre-built pages**: Home, Blog, About, Products, Contact,
  Careers, Press, Privacy, Terms, Cookies — each with its own
  tesla.com-style design language.
* **Primary menu, footer widgets, SEO-friendly permalinks**.
* **JSON-LD structured data + OG/Twitter Card + auto meta
  descriptions + breadcrumbs + XML sitemap**.
* **Built-in Theme Builder** (VoltCore → Theme Builder) works with
  Elementor Free as a Pro-alternative.
* **wp-admin menu** (VoltCore → Dashboard / Options / Theme Builder
  / Import Demo / Docs).

## Recommended workflow

1. Upload `voltcore.zip`, activate.
2. Install and activate Elementor + Elementor Pro.
3. Go to **VoltCore → Import Demo** and run both importers
   (demo content, then Elementor templates).
4. Visit the site — every page is now composed of VoltCore widgets.
   Open Elementor on any page to tweak images, text, colors and
   spacing visually.
5. Replace the bundled images and content with your own — everything
   lives in the Media Library and the Elementor editor.

## 1. Install

1. Zip the `voltcore/` folder (the directory that contains `style.css`) into `voltcore.zip`.
2. In WordPress admin: `Appearance → Themes → Add New → Upload Theme → voltcore.zip → Install Now → Activate`.

## 2. Set the homepage

1. `Pages → Add New` — create a blank page called `Home`. Save.
2. `Pages → Add New` — create a blank page called `Blog`. Save.
3. `Settings → Reading`:
   * **Your homepage displays** → `A static page`.
   * **Homepage** → `Home`.
   * **Posts page** → `Blog`.

## 3. Pick a homepage style

`Appearance → Customize → VoltCore Theme → Brand & Colors → Homepage Style`:

* `Classic` — full-bleed scroll-snap hero stack (closest to tesla.com home).
* `Grid` — product showcase grid with parallax hero.
* `Story` — split-screen narrative with animated stats counters.

You can switch between the three at any time without losing content.

## 4. Replace the imagery

Every image has two replacement paths. Pick whichever is easier for you:

### A. Via the Customizer (recommended)

`Appearance → Customize → VoltCore Theme` has sections for:

* `Hero 1 / Hero 2 / Hero 3` — background image, headline, subheadline, 2 buttons.
* `Product Cards` — 3 cards, each with image, title, description, link.
* `Story Blocks` — 2 split-screen image+text blocks.
* `Stats Counters` — 4 animated counters.
* `CTA Strip` — bottom call-to-action on every homepage variant.
* `Footer` — copyright line.

All images go through the Media Library.

### B. By overwriting default files

Drop your own files into `wp-content/themes/voltcore/assets/images/` with
these exact names and VoltCore will pick them up automatically (as long
as nothing has been uploaded in the Customizer):

| Filename        | Used on                        | Suggested size |
|-----------------|--------------------------------|----------------|
| hero-1.jpg      | Hero 1 background              | 1920 × 1200    |
| hero-2.jpg      | Hero 2 background              | 1920 × 1200    |
| hero-3.jpg      | Hero 3 background              | 1920 × 1200    |
| product-1.jpg   | Product card 1                 | 1200 × 900     |
| product-2.jpg   | Product card 2                 | 1200 × 900     |
| product-3.jpg   | Product card 3                 | 1200 × 900     |
| story-1.jpg     | Story block 1                  | 1400 × 1050    |
| story-2.jpg     | Story block 2                  | 1400 × 1050    |
| about.jpg       | (available for custom pages)   | 1400 × 900     |
| logo.svg        | Fallback logo                  | any            |

## 5. Brand color

`Appearance → Customize → VoltCore Theme → Brand & Colors → Accent Color`.
The color flows through buttons, links, category labels and hover states.

## 6. Menus

`Appearance → Menus`:

* Create a menu, assign location `Primary Menu` — top-right nav.
* Create a second menu, assign location `Footer Menu` — footer bottom row.

## 7. Widgets

`Appearance → Widgets`:

* `Blog Sidebar` — appears on blog index / archives / single posts.
* `Footer Column 1..4` — four-column footer grid.

## 8. Logo

`Appearance → Customize → Site Identity → Logo` — upload any PNG/SVG.
If none is set, the site name is printed as uppercase text in the nav.

## 9. Blog

Works out of the box. Create posts normally with `Posts → Add New`:

* Set a featured image — it drives the card thumbnail and single-post hero.
* Assign categories — they appear as accent-colored kickers on cards and hero.
* Tags appear below the article body.

## 10. Tesla-clone widgets (v3.0.0)

VoltCore 3.0 adds a Tesla.com-style interactive widget set under the
**VoltCore** Elementor category:

| Widget | What it does |
| --- | --- |
| **VoltCore Navbar** | Transparent → frosted nav with hover mega-panels (image cards + side-links per primary item), Shop/Account/Region icons, full-height drawer with grouped sections. |
| **VoltCore Vehicle Panels** | Full-viewport panels for the homepage with image **or** looping video, eyebrow/title/subtitle/price, dual CTAs. Scroll-snap on; videos autoplay only when in view. |
| **VoltCore Model Gallery** | Pinned image left, scrolling feature blocks right. Active image swaps as each block enters the viewport. |
| **VoltCore Model Specs** | Tabbed spec matrix (e.g. Plaid / Long Range). Each tab is a grid of value + label cells. |
| **VoltCore Design Studio** | Configurator. Paint swatches swap the vehicle image; wheel/interior/autopilot picks update a live total. The CTA URL carries the chosen IDs as query params. |
| **VoltCore Savings Calculator** | Powerwall/Solar readout. Sliders → annual savings, 25-year savings, CO₂ offset, payback. |
| **VoltCore Supercharger Map** | Leaflet + OpenStreetMap with V2/V3 pins, search, chips, and clickable list. Leaflet is loaded only when this widget is on the page. |
| **VoltCore Inventory** | Client-side searchable grid. Filters: model, trim, color, max-price, ZIP. Result counter and empty state. |
| **VoltCore Test Drive** | Four-step booking: vehicle → when/where → contact → confirmation. Animated transitions, guarded navigation. |
| **VoltCore Modals** | Drop once in the footer template. Provides Account + Region modals. Buttons open via `data-vc-modal-open="account"` / `"region"`. |

## Folder map

```
voltcore/
├── style.css                       theme header (metadata only)
├── functions.php                   setup + enqueue + helpers
├── front-page.php                  routes to the chosen homepage variant
├── header.php, footer.php          global chrome
├── index.php, archive.php          blog listings
├── single.php, page.php            content templates
├── search.php, searchform.php      search
├── 404.php                         error page
├── comments.php                    comments block
├── readme.txt                      WordPress readme
├── INSTALL.md                      this file
├── screenshot.png                  theme preview (wp-admin)
├── assets/
│   ├── css/main.css                all styling
│   ├── js/main.js                  scroll, fade-in, counter, drawer
│   └── images/                     replaceable placeholder images
├── inc/
│   ├── customizer.php              all Customizer controls
│   └── template-tags.php           reusable partials
└── template-parts/
    ├── home-classic.php            Homepage variant #1
    ├── home-grid.php               Homepage variant #2
    ├── home-story.php              Homepage variant #3
    ├── section-stats.php           Animated stats counter
    ├── section-cta.php             Bottom CTA strip
    └── section-latest-posts.php    Latest blog posts grid
```
