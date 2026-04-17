# Installable theme package

`voltcore.zip` in this folder is the **ready-to-install** WordPress theme
package. Upload this file directly in WordPress admin — not the whole
repository zip from the "Code → Download ZIP" button.

## Install

1. Download the zip:
   https://github.com/yangdongle668/wordpress-theme/raw/claude/tesla-wordpress-theme-6yh7P/dist/voltcore.zip
2. WordPress admin → `Appearance → Themes → Add New → Upload Theme`.
3. Choose `voltcore.zip` → `Install Now` → `Activate`.

On activation the theme seeds pages, posts, menu, widgets and permalinks
automatically. Visit `VoltCore → Dashboard` in the left admin menu.

## Why not the repo zip?

GitHub's "Download ZIP" packages the entire repository as
`wordpress-theme-<branch>.zip`, with the theme nested under a
`voltcore/` subfolder. WordPress expects `style.css` at the zip root,
so it refuses that file with the error:

> The package could not be installed. The theme is missing the style.css stylesheet.

`dist/voltcore.zip` is packaged correctly: `voltcore/style.css` sits at
the zip root, which is what WordPress needs.

## Rebuild (for developers)

```bash
rm -f dist/voltcore.zip
zip -r dist/voltcore.zip voltcore -x "*.DS_Store" "*.git*"
```
