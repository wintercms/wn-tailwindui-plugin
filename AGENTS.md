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

### Tailwind purges plain author `:focus`/`:focus-visible` rules inside `@layer`

Tailwind content-scans everything inside `@layer components` and **strips rules it considers unused** — and plain author `:focus` / `:focus-visible` rules get dropped this way even when their selector's classes are clearly present (a `:before`/`:after` rule on the *same* selector survives, so it looks baffling). Symptom: your rule is in the source but **absent from the compiled `dist`**, and the build hash doesn't change when you add it. Two ways to keep such a rule:

- Author it with `@apply` (Tailwind then treats it as a generated utility and keeps it) — this is how the secondary-tab focus ring in `fancy-layout.css` survives; **or**
- Move it **outside** `@layer components` as plain unlayered CSS. Unlayered author CSS is never purged and its `!important` still wins. See the focus-ring rules at the bottom of `components/nested-form.css`.

Fastest way to catch this: live-inject the rule in the browser (see the core `AGENTS.md` fast-loop) — if it works injected but vanishes after compile, the build purged it; stop chasing specificity.

## Restyling core controls: watch for plugin overlays

When you restyle a core backend control, remember other plugins position elements **on top of** it. The one that bites: **Winter.Translate** absolutely-positions a `.ml-btn` language switcher over translatable inputs (it sizes to its own content, ~48px). If you shrink an input's height (e.g. the fancy-layout header inputs), the switcher overhangs it — size the input to fit the switcher and align their edges. See `components/fancy-layout.css`. Base Winter avoids this by using a naturally tall header input; TailwindUI's smaller text needs an explicit `min-h`.

### The fancy-layout action-bar styles leak into *any* nested `.form-buttons`

`components/fancy-layout.css` restyles the form's sticky action bar by targeting `.form-buttons` broadly:

```
.layout.fancy-layout :not(.nested-form) > .form-widget > .layout-row .form-buttons .btn.btn-primary { … !important }
```

That compiles to a `0,8,0` selector with `!important` (the ghost-button treatment: `background-color: transparent !important`, white hover border). Because `.form-buttons` is matched as a **descendant** (not a direct child), it also hits any *nested* `.form-buttons` — a modal or popup footer rendered inside the form widget. The core **iconpicker** modal footer was `.form-buttons`, so its Insert button inherited `transparent !important` and went white-on-white.

- A control that renders its own button bar inside the form shouldn't reuse `.form-buttons` for a *modal/popup* footer — give it its own class and style that (this is how the iconpicker modal was fixed, on the core side). Trying to win from the consumer's scoped CSS is fragile: you'd need to beat `0,8,0 !important`, i.e. 9+ classes.
- If you ever need the action-bar rules to *not* reach nested footers, tighten the selector here (e.g. scope `.form-buttons` to a direct-child chain) rather than expecting every consumer to defend against it.
