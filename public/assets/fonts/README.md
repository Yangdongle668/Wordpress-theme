# Fonts — self-hosted web typography

The site registers a branded web font via `public/assets/css/base/fonts.css`
(loaded on every page) and the design token `--font-sans` in
`public/assets/css/tokens.css` already prefers it. Until the actual woff2
files are dropped in, browsers silently fall back to the system stack
(no visual breakage, no console errors).

## Default: Inter (recommended, free, SIL OFL 1.1)

[Inter](https://rsms.me/inter/) is a modern geometric sans tuned for
on-screen legibility. It's open source (SIL Open Font License 1.1), ships
as a variable font (one file for every weight 100–900), and gives the
site a crisp, contemporary look that pairs well with the minimalist full-
width layout.

### Download + place

1. Grab the latest release from the official source:
   <https://github.com/rsms/inter/releases>

2. From the release zip, copy these two files into this directory:

   ```
   Inter-<version>/web/InterVariable.woff2        →  public/assets/fonts/InterVariable.woff2
   Inter-<version>/web/InterVariable-Italic.woff2 →  public/assets/fonts/InterVariable-Italic.woff2
   ```

3. Hard-refresh. The site now renders in Inter.

### Verify it's loading

```bash
curl -I https://www.example.com/assets/fonts/InterVariable.woff2
# HTTP/2 200 ... content-type: font/woff2
```

In DevTools → Network, filter by Font. You should see both files loaded
once with a long cache-control header (configured in deploy/nginx.conf).

### License

Inter is distributed under the [SIL Open Font License 1.1](https://openfontlicense.org/).
Self-hosting and commercial use are permitted. Keep a copy of the OFL
license text next to the font files if you redistribute the site bundle
(not strictly required for hosting, but polite):

```
curl -o OFL.txt https://raw.githubusercontent.com/rsms/inter/master/LICENSE.txt
```

---

## Alternative 1: a commercial typeface (e.g. Gotham)

If the brand team has licensed a paid typeface such as **Gotham**,
**Neue Haas Grotesk**, **Söhne**, or similar, self-hosting is allowed by
most webfont licenses (Monotype, Hoefler&Co., Commercial Type, and
others all permit it — read your specific EULA).

### How to swap

1. Obtain the web-ready `.woff2` files from your licensed source.
2. Place them in this directory using your chosen naming convention, e.g.:
   ```
   Gotham-Book.woff2
   Gotham-Book-Italic.woff2
   Gotham-Medium.woff2
   Gotham-Bold.woff2
   ```
3. Replace the `@font-face` blocks in
   `public/assets/css/base/fonts.css` with per-weight declarations:
   ```css
   @font-face {
     font-family: "Gotham";
     font-style: normal;
     font-weight: 400;
     font-display: swap;
     src: url("/assets/fonts/Gotham-Book.woff2") format("woff2");
   }
   /* repeat for 500, 600, 700 and italic */
   ```
4. In `public/assets/css/tokens.css`, change the `--font-sans` token so
   `"Gotham"` is first.

No other changes needed — the whole site picks up the new family.

---

## Alternative 2: other open-source options

Every option below is free for commercial self-hosting:

- **Manrope** — rounded geometric, soft humanist finish
- **Satoshi** — geometric, free weights available on Fontshare
- **General Sans** — modern grotesk, free on Fontshare
- **Work Sans** — humanist sans, SIL OFL
- **Outfit** — geometric sans, SIL OFL

To swap in any of them, follow the same three steps as the Gotham case
above (files in, @font-face rewritten, token updated).

---

## Performance guidelines

- **woff2 only** — it's 30% smaller than woff and universally supported
- **At most two weights** per family unless you have a real reason. The
  variable Inter file covers every weight in ~335 KB (compressed) —
  more efficient than shipping 4 static weights
- **`font-display: swap`** (already set) keeps the page readable with the
  system fallback while the font downloads
- **Preload the critical weight** on pages where the font arrives after
  the LCP element. Example to add in page `<head>`:
  ```html
  <link rel="preload"
        as="font"
        type="font/woff2"
        href="/assets/fonts/InterVariable.woff2"
        crossorigin>
  ```
  Only preload on landing pages — on secondary pages the file is already
  in cache.

---

## Subsetting (optional, advanced)

If bundle budget is tight, you can subset Inter to only the glyph ranges
you ship text in (Latin + Latin-Extended covers English and most European
languages, dropping the file to ~70 KB). Tools:

- [glyphhanger](https://github.com/zachleat/glyphhanger) — Zach Leatherman's
  subsetter, pairs with pyftsubset
- `fonttools` / `pyftsubset` directly

For most sites, this is optional — the default file is already small.
