# Agent guide — Winter.TailwindUI

TailwindUI restyles the Winter backend. Source is in `assets/src/`, compiled (and **committed**) output in `assets/dist/`. It hooks in by extending every backend controller (`Plugin.php`) with `addVite('assets/src/css/app.css', 'Winter.TailwindUI')`.

## Building assets

Compile from a Winter install that has this plugin:

```
php artisan vite:install Winter.TailwindUI   # once, installs node deps
php artisan vite:compile Winter.TailwindUI   # rebuilds assets/dist/
```

- **If the plugin is symlinked into the project**, the Vite config loads from the plugin's real path, so Node can't resolve the project's `node_modules` (`@vitejs/plugin-vue`, `laravel-vite-plugin`). Symlink it in: `ln -s <project>/node_modules ./node_modules` (gitignored), then compile.
- Commit the regenerated `assets/dist/` (new hashed files + `manifest.json`, removing superseded ones). Do **not** commit the generated `package-lock.json`.
- On a mirror-based dev docroot, re-run `php artisan winter:mirror public --relative` after building so the new hash serves.

## CSS structure

- Components: `assets/src/css/components/*.css` (imported via `components/all.css`), plus `base.css`, `custom.css`, `darkmode.css`. Uses Tailwind `@apply` inside `@layer components` — note layered rules lose to *unlayered* core/plugin CSS regardless of specificity, so overrides of core backend styles usually need `!important` (see the existing component files).

## Restyling core controls: watch for plugin overlays

When you restyle a core backend control, remember other plugins position elements **on top of** it. The one that bites: **Winter.Translate** absolutely-positions a `.ml-btn` language switcher over translatable inputs (it sizes to its own content, ~48px). If you shrink an input's height (e.g. the fancy-layout header inputs), the switcher overhangs it — size the input to fit the switcher and align their edges. See `components/fancy-layout.css`. Base Winter avoids this by using a naturally tall header input; TailwindUI's smaller text needs an explicit `min-h`.
