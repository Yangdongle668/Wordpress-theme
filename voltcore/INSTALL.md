# VoltCore — install & customize (5 minutes)

VoltCore is a Tesla-inspired WordPress theme for battery, EV and
clean-energy brands. It installs like any other theme and works
immediately with bundled placeholder imagery.

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
