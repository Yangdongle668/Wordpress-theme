=== VoltCore ===

Contributors: voltcore
Tested up to: 6.5
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Tesla-inspired, Elementor-native WordPress theme for battery, EV, and clean-energy brands.

== Description ==

VoltCore is a premium-feel, minimalist WordPress theme modelled after Tesla's
marketing aesthetic: full-bleed hero sections, tight typography, subtle
scroll-snap storytelling, and monochrome palette with a single accent color.

It ships with:

* Fully Elementor-native workflow: 18 VoltCore-branded widgets
  (Navbar, Footer, Hero, Stats, Split, CTA, Post Grid, Feature Card,
  Icon Box, Logo Cloud, Marquee, Contact Grid, Careers Hero, Press
  Kit, Press List, Legal Hero, Legal TOC, Team Grid, Timeline, FAQ,
  Product Hero, Product Specs, Breadcrumbs).
* One-click Elementor template importer that turns every bundled
  page into an Elementor document.
* 3 ready-to-use homepage styles (Classic / Grid / Story) also
  available as a Customizer variant selector for non-Elementor users.
* Full blog system (index, archive, single, category, tag, search, 404).
* Products CPT (vc_product) with category taxonomy and dedicated
  archive + detail templates.
* Sticky minimal navigation with transparent → solid on scroll.
* Intersection-observer driven fade-in / counter-up animations.
* Mobile drawer menu.
* Customizer controls for logo, hero images, headlines, CTAs, accent
  color, footer, and homepage variant selection.
* Placeholder images so the site works the moment it is activated.
* JSON-LD, Open Graph, Twitter Card and breadcrumbs on every page.
* Built-in Theme Builder (VoltCore → Theme Builder) that covers
  header/footer/archive/single/404 without needing Elementor Pro.

== Installation ==

1. In WordPress admin, go to `Appearance → Themes → Add New → Upload Theme`.
2. Upload `voltcore.zip` and click `Install Now`, then `Activate`.
3. Go to `Appearance → Customize` to:
   * Upload your logo
   * Choose one of the 3 homepage styles (Classic / Grid / Story)
   * Replace hero images and headlines
   * Set your brand accent color
4. Create a new Page, set Template to "Front Page", and set it as your
   homepage under `Settings → Reading`.
5. Replace the placeholder images in `wp-content/themes/voltcore/assets/images/`
   with your own (keep the same filenames) OR upload new ones in the
   Customizer — the theme will use whatever is in the Customizer first.

== Replacing images ==

Every image in the theme can be swapped in two ways:

* Via the Customizer (recommended) — images go through the Media Library.
* By overwriting the default files in `assets/images/` with the same filename.

Default filenames:

* hero-1.jpg, hero-2.jpg, hero-3.jpg — homepage hero backgrounds
* product-1.jpg, product-2.jpg, product-3.jpg — product cards
* story-1.jpg, story-2.jpg — split-screen story sections
* about.jpg — about / CTA block
* logo.svg — fallback logo when no custom logo is uploaded

== Changelog ==

= 2.0.0 =
* Full Elementor-native rearchitecture.
* 18 VoltCore-branded Elementor widgets under a unified category.
* One-click Elementor template importer (Import Demo → Import
  Elementor templates): every bundled page becomes an Elementor
  document, header/footer/404 land in the Theme Builder with
  display conditions auto-assigned under Elementor Pro.
* PHP templates now act as fallbacks when Elementor is not active
  — no functionality lost for users running without Elementor.
* Added Careers, Press, Privacy, Terms, Cookies page templates.
* Admin dashboard shows Elementor + Pro status pills.
* Persistent dismissible admin notice guides users to install
  Elementor / Pro when missing.

= 1.0.0 =
* Initial release.

== Credits ==

* Typography: system font stack + Inter webfont fallback
* Inspired by Tesla's marketing site aesthetic (no assets copied)
