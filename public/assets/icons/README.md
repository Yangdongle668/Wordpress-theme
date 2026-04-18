# Icons

All UI icons ship as a single **SVG symbol sprite** (`icons.svg`) that's
referenced from pages via `<use>`:

```html
<svg class="icon" width="20" height="20" aria-hidden="true">
  <use href="/assets/icons/icons.svg#arrow-right"></use>
</svg>
```

## Why a sprite?

- **One HTTP request** for all icons (~4 KB gzipped vs. dozens of PNGs)
- **Styleable with CSS** — every icon strokes `currentColor`, so it inherits
  the parent text color (hover, dark mode, focus state — all free)
- **Cacheable** — one file, long `max-age` header via Nginx
- **Searchable & diff-able** — plain XML, version controls cleanly

## Design conventions

| Property      | Value                                     |
|---------------|-------------------------------------------|
| viewBox       | `0 0 24 24`                               |
| Stroke        | `currentColor`                            |
| Stroke width  | `1.5` (2 for check/close to read at small sizes) |
| Linecap/join  | `round`                                   |
| Fill          | `none` (outline style throughout)         |

Stroke-based outline icons pair best with the site's minimalist typography.
Filled glyphs are avoided except for brand logos (in footer).

## Current set

- **Arrows / chevrons**: `arrow-right`, `arrow-left`, `arrow-up`, `arrow-down`,
  `chevron-right`, `chevron-down`
- **UI**: `close`, `menu`, `search`, `check`, `plus`, `minus`, `download`,
  `external`
- **Contact**: `mail`, `phone`, `map-pin`
- **Industry**: `battery`, `bolt`, `shield`, `leaf`

## Adding a new icon

1. Open `icons.svg`.
2. Add a new `<symbol id="your-name" viewBox="0 0 24 24" …>…</symbol>`.
3. Reuse one of the existing icon blocks as a pattern — keep stroke width,
   linecap, and `currentColor` stroke consistent.
4. Reference from HTML: `<use href="/assets/icons/icons.svg#your-name">`.

### Suggested sizing helper

If you find yourself writing `<svg width="20" height="20">` a lot, add a
utility class in `components/misc.css`:

```css
.icon { width: 1em; height: 1em; fill: none; stroke: currentColor; }
.icon--sm { font-size: 14px; }
.icon--md { font-size: 20px; }
.icon--lg { font-size: 28px; }
```

Then: `<svg class="icon icon--md" aria-hidden="true"><use …></svg>`.

## Accessibility

- Decorative icons: add `aria-hidden="true"` on the `<svg>`.
- Meaningful icons (no accompanying text): give the `<svg>` `role="img"`
  and a `<title>` child, or label the containing element with
  `aria-label="…"`.
