# Fonts

This directory self-hosts web fonts so the site doesn't depend on third-party
font CDNs (faster, more private, better cache control).

## Current strategy

The base stylesheet uses a **system font stack** (see `--font-sans` in
`public/assets/css/tokens.css`):

```
-apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue",
Helvetica, Arial, "PingFang SC", "Hiragino Sans GB",
"Microsoft YaHei", "Source Han Sans CN", sans-serif
```

This gives perfectly native typography on every platform without any network
request and renders instantly on first paint.

## Adding a branded font (optional)

If marketing later wants a branded display face (e.g. Inter, Satoshi,
SF Pro-style), drop the `woff2` files here and replace `--font-sans` with a
stack that falls back to the system stack:

```css
@font-face {
  font-family: "Brand";
  src: url("/assets/fonts/Brand-Regular.woff2") format("woff2");
  font-weight: 400;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: "Brand";
  src: url("/assets/fonts/Brand-Semibold.woff2") format("woff2");
  font-weight: 600;
  font-style: normal;
  font-display: swap;
}

:root {
  --font-sans: "Brand", -apple-system, BlinkMacSystemFont, "Segoe UI",
    "Helvetica Neue", Helvetica, Arial, "PingFang SC", "Hiragino Sans GB",
    "Microsoft YaHei", "Source Han Sans CN", sans-serif;
}
```

Preload the primary weight in the page template:

```html
<link rel="preload" as="font" type="font/woff2"
      href="/assets/fonts/Brand-Regular.woff2" crossorigin>
```

### Guidelines

- Only host `woff2` — it's compressed ~30% smaller than `woff` and supported
  everywhere we care about.
- Ship **at most two weights** per family (regular + semibold usually
  suffices). Every extra weight is another ~30–80 KB.
- Use `font-display: swap` so the page still renders immediately with the
  system fallback and swaps to the branded face when it arrives.
- Subset to the Latin + Latin-Extended ranges unless the site targets CJK
  at scale; self-hosting full CJK faces is 2–5 MB.
- License must permit self-hosting — double-check commercial terms before
  shipping.
