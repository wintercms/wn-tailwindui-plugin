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
- Vite caches builds in `node_modules/.vite`; a source edit can silently no-op (the compiled `dist` keeps the old value even though the source changed). If a change isn't taking effect, `rm -rf node_modules/.vite` and recompile.

## Fast iteration loop (browser MCP + HMR)

Iterating via edit → `vite:compile` → `winter:mirror` → screenshot is slow, and with **headless** Playwright it's also misleading — synthetic clicks trigger `:focus-visible` that a real mouse click doesn't (this sent a whole debugging session chasing a phantom focus ring). Prefer:

1. **Drive a persistent Chrome with the `chrome-devtools` MCP.** It stays logged in across turns, and `evaluate_script` both inspects computed styles and drives the UI in a single call — no throwaway `*.js` scripts, no browser relaunch, and a real browser means no headless render/focus artifacts.
2. **Find CSS values by live-injecting**, not recompiling. Add a `<style>` via `evaluate_script`, eyeball it, iterate the value with zero compiles; only once it's right do you write it to source. Bonus: if a rule works when injected but vanishes after compile, the *build* is eating it (e.g. the `@layer` purge), not your CSS — a much faster diagnosis than specificity spelunking.
3. **Hot-reload source edits with `vite:watch`.** `php artisan vite:watch Winter.TailwindUI` runs a dev server; edits to `assets/src/**` push over HMR with no compile/mirror/reload (~1–2s). On exit it runs `vite:compile`, so the committed `dist/` is left intact.
   - **Gotcha:** on IPv6 hosts the watch writes an unreachable `http://[::]:5173` into `assets/dist/hot`, so the backend loads unstyled. After starting the watch: `echo -n 'http://localhost:5173' > assets/dist/hot`, then reload the page once so the backend serves from the dev server. (Tracked upstream: wintercms/winter#1525.)
   - When done, stop the watch and confirm `assets/dist/hot` is gone (clean shutdown removes it) so the backend goes back to serving compiled `dist/`.

## CSS structure

- Components: `assets/src/css/components/*.css` (imported via `components/all.css`), plus `base.css`, `custom.css`, `darkmode.css`. Uses Tailwind `@apply` inside `@layer components` — note layered rules lose to *unlayered* core/plugin CSS regardless of specificity, so overrides of core backend styles usually need `!important` (see the existing component files).

## Restyling core controls: watch for plugin overlays

When you restyle a core backend control, remember other plugins position elements **on top of** it. The one that bites: **Winter.Translate** absolutely-positions a `.ml-btn` language switcher over translatable inputs (it sizes to its own content, ~48px). If you shrink an input's height (e.g. the fancy-layout header inputs), the switcher overhangs it — size the input to fit the switcher and align their edges. See `components/fancy-layout.css`. Base Winter avoids this by using a naturally tall header input; TailwindUI's smaller text needs an explicit `min-h`.
