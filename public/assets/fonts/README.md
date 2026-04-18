# Fonts — self-hosted web typography

Primary typeface: **Mona Sans** (GitHub, SIL OFL 1.1). A confident,
tall-x-height geometric sans with five variation axes packed into one
file — weight 200–900, width 75–125, italic, optical size. ~518 KB gives
the whole site every style it needs.

## Files

```
MonaSans-Variable.woff2   — full five-axis variable font
MonaSans-OFL.txt          — SIL Open Font License, v1.1
```

From the official [`github/mona-sans`](https://github.com/github/mona-sans)
release (`fonts/webfonts/variable/MonaSansVF[wdth,wght,opsz,ital].woff2`,
renamed on disk for readable HTTP paths).

## Wired up via

- `public/assets/css/base/fonts.css` — two `@font-face` rules (roman + italic).
- `public/assets/css/tokens.css` — `"Mona Sans"` is the first entry of
  `--font-sans`.
- `public/en/index.html` — `<link rel=preload>` points at the variable file.

## Swapping to a different family

1. Drop the new variable woff2 into this directory.
2. Update the `@font-face` `font-family` and `src` in `base/fonts.css`.
3. Replace `"Mona Sans"` in `--font-sans` (`tokens.css`) with the new name.
4. Update the `<link rel=preload>` in `public/en/index.html`.

## License

Mona Sans is distributed under the [SIL Open Font License 1.1](https://openfontlicense.org/).
Self-hosting and commercial use are permitted. Keep `MonaSans-OFL.txt`
alongside the font file when redistributing the site bundle.
