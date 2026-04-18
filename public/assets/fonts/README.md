# Fonts — self-hosted web typography

The site registers a branded web font via `public/assets/css/base/fonts.css`
(loaded on every page) and the design token `--font-sans` in
`public/assets/css/tokens.css` already prefers it. Until the actual woff2
files are dropped in, browsers silently fall back to the system stack
(no visual breakage, no console errors).

## Default: Montserrat (free, SIL OFL 1.1)

[Montserrat](https://fonts.google.com/specimen/Montserrat) is an open-source
geometric sans by Julieta Ulanovsky. Its character and proportions are
rooted in the street-lettering tradition of the Montserrat neighborhood in
Buenos Aires — the same typographic lineage that inspired many commercial
American geometric sans typefaces. It pairs well with the minimalist full-
width layout and is free to self-host for any commercial use.

### Download + place

The easiest source for self-hosting-ready woff2 files is:

1. Go to the **Google Webfonts Helper**:
   <https://gwfh.mranftl.com/fonts/montserrat>

2. Select these charsets (the Unicode-range in `fonts.css` already covers
   them):
   - `latin`
   - `latin-ext`

3. Under **Styles**, choose "Variable (wght)" for a single file covering
   every weight from 100 to 900. Include the italic if you need it for
   long-form prose (recommended).

4. Under **Download files**, pick **Modern Browsers (woff2 only)** and hit
   download. You'll get a zip containing several woff2 files.

5. Rename and copy two of them into this directory:

   ```
   <download>/montserrat-latin-variable-wghtOnly-normal.woff2   →  public/assets/fonts/Montserrat-Variable.woff2
   <download>/montserrat-latin-variable-wghtOnly-italic.woff2   →  public/assets/fonts/Montserrat-Variable-Italic.woff2
   ```

   (Filenames from the helper vary by version — grab the variable-weight
   pair, one for each style, and rename them to match the two target
   paths above.)

6. Hard-refresh. The site now renders in Montserrat.

### Alternative source — upstream repo

If you'd rather build the woff2 yourself from the original source,
<https://github.com/JulietaUla/Montserrat> ships TTF variable files under
`fonts/variable/`. Convert to woff2 with `woff2_compress` (from
[google/woff2](https://github.com/google/woff2)):

```bash
git clone https://github.com/JulietaUla/Montserrat.git
cd Montserrat/fonts/variable
woff2_compress Montserrat[wght].ttf
woff2_compress Montserrat-Italic[wght].ttf
# move/rename the outputs to match the expected paths
mv Montserrat[wght].woff2           /path/to/public/assets/fonts/Montserrat-Variable.woff2
mv Montserrat-Italic[wght].woff2    /path/to/public/assets/fonts/Montserrat-Variable-Italic.woff2
```

### Verify

```bash
curl -I https://www.07691688.xyz/assets/fonts/Montserrat-Variable.woff2
# HTTP/2 200 ... content-type: font/woff2
```

In DevTools → Network, filter by Font. You should see both files loaded
once with a long cache-control header (configured in `deploy/nginx.conf`).

### License

Montserrat is distributed under the [SIL Open Font License 1.1](https://openfontlicense.org/).
Self-hosting and commercial use are permitted. Keep a copy of the OFL
license text next to the font files if you redistribute the site bundle:

```bash
curl -o OFL.txt https://raw.githubusercontent.com/JulietaUla/Montserrat/master/OFL.txt
```

---

## Commercial typeface (e.g. Gotham) — if you own a webfont license

If the brand team has purchased a **Webfont License** for a commercial
family such as Gotham (Hoefler&Co. / Monotype), Söhne (Klim Type Foundry),
Neue Haas Grotesk, or similar, self-hosting is allowed by most webfont
EULAs. Double-check your specific license terms first.

> ⚠️ Do **not** download commercial fonts from GitHub gists, "free font"
> sites, or other unofficial mirrors. Those files are almost always
> unauthorized copies — shipping them creates a real copyright
> liability. Buy the license from the foundry or from an authorized
> reseller (MyFonts, Adobe Fonts, Monotype Fonts, etc.).

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
5. Update the preload hint in `public/en/index.html` to point at the
   correct filename.

No other changes needed — the whole site picks up the new family.

---

## Other open-source options

Every option below is free for commercial self-hosting (all SIL OFL):

- **Inter** — modern UI-tuned sans (our previous default; gives the site
  a more restrained, ui-kit feel)
- **Mona Sans** — GitHub's open geometric sans, close to Montserrat
- **Nunito Sans** — softer, slightly more humanist
- **Work Sans** — humanist sans, good neutrality
- **Outfit** — newer geometric sans, Google Fonts

To swap in any of them, follow the same three steps as the Gotham
case above (files in, `@font-face` rewritten, `--font-sans` updated).

---

## Performance guidelines

- **woff2 only** — it's 30% smaller than woff and universally supported.
- **Use the variable font** — one file covers every weight. Montserrat's
  variable woff2 is roughly 120 KB for Latin + Latin-Extended, smaller
  than shipping four static weights.
- **`font-display: swap`** (already set) keeps the page readable with the
  system fallback while the font downloads.
- **Preload the primary file** on landing pages (already wired up on the
  homepage). Secondary pages hit the browser cache so they don't need
  another preload.

### Subsetting further (optional, advanced)

If bundle budget is tight, subset to only the glyphs your pages actually
use. Tools:

- [glyphhanger](https://github.com/zachleat/glyphhanger) — Zach
  Leatherman's subsetter, pairs with `pyftsubset`
- `fonttools` / `pyftsubset` directly

For most sites the default variable file is already small enough.
