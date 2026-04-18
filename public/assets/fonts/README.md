# Fonts — self-hosted web typography

The site ships with **Inter** (variable weight + italic) self-hosted as
woff2. `public/assets/css/base/fonts.css` registers the `@font-face`
blocks, `public/assets/css/tokens.css` references `"Inter"` first in
`--font-sans`, and `public/en/index.html` preloads
`/assets/fonts/InterVariable.woff2` on the home page.

## Files

```
InterVariable.woff2              — weight 100–900, normal
InterVariable-Italic.woff2       — weight 100–900, italic
Inter-OFL.txt                    — SIL Open Font License, v1.1
```

All from the official [rsms/inter releases](https://github.com/rsms/inter/releases).

## License

Inter is distributed under the [SIL Open Font License 1.1](https://openfontlicense.org/).
Self-hosting and commercial use are permitted. Keep `Inter-OFL.txt`
alongside the woff2 files if you redistribute the site bundle.

## Swapping to a different family

If a project needs a different typeface:

1. Drop the new variable woff2 file(s) into this directory.
2. Update the `@font-face` `font-family` and `src` in
   `public/assets/css/base/fonts.css`.
3. Replace `"Inter"` in `--font-sans` (in `tokens.css`) with the new name.
4. Update the `<link rel=preload>` in `public/en/index.html`.

No other changes required — every page picks up the new family via the
design token.
