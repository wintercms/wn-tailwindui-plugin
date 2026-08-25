# Brand-driven CSS-variable color architecture (Winter core)

Goal (per LukeTowers): stop the dark-mode "whack-a-mole". Every backend color should
reference a **named brand color or an automatically-derived, purpose-named variant**.
The **branding settings** should override the whole palette at once by setting CSS
variables. **Dark mode** should **auto-generate dark-safe variants** of the brand
palette via `color-mix()`, with the **option of a custom dark palette**.

Confirmed decisions:
- **Scope: CORE (upstream).** Rewrite core LESS + `BrandSetting` to emit/consume CSS
  vars. Lands as a Winter core PR (note: `modules/` is clobbered by `composer update`,
  so it must land upstream to persist locally).
- **Dark derivation: `color-mix()` auto-derive** from the live brand vars.

---

## 1. Current system (what we're replacing)

- Brand colors are **LESS** vars `@brand-primary / @brand-secondary / @brand-accent`.
- `modules/backend/assets/less/core/variables.less` sets their **defaults**; they're
  used across core LESS (`list.less`, `sidepanel.less`, `sidenav-tree.less`,
  `reportwidgets.less`, `list.variables.less`, `loader.less`, `utilities.less`,
  `select.variables.less`, `checkbox.less`, …) and compiled to **hardcoded hex** into
  the static `modules/backend/assets/css/winter.css` (built via
  `modules/backend/winter.mix.js` → `php artisan mix:compile`).
- `modules/backend/models/brandsetting/custom.less` holds all the brand-colored
  *chrome* (fancy header, master-tabs wings, breadcrumb, list-active accents, etc.),
  written with `@brand-secondary` + LESS `mix()`/`saturate()` derivations. It is
  **recompiled at runtime** by `BrandSetting::compileCss()` (`Less_Parser->ModifyVars`
  with the admin's colors), cached under `backend::brand.custom_css`, injected as a
  `<style>` block. Cache-bust = `php artisan cache:clear`.
- **There are no CSS custom properties today.** Branding "overrides" by recompiling
  LESS; the skin's `darkmode.css` then fights the compiled hex → the whack-a-mole.

Existing dark-variant math already in `custom.less` (reuse the ratios):
```
@custom-dark-secondary: mix(black, saturate(@brand-secondary, 20%), 25%);
@custom-dark-primary:   mix(black, saturate(@brand-primary,   5%), 15%);
@custom-dark-accent:    mix(black, desaturate(@brand-accent, 35%), 20%);
/* tab inactive  = mix(black, saturate(@brand-secondary,20%), 31%)  */
/* breadcrumb    = mix(black, saturate(@brand-secondary,20%), 16%)  */
```

---

## 2. Target architecture

### 2a. Brand palette as CSS variables (the single source)
`BrandSetting`/`custom.less` emit a `:root` block from the settings values:
```css
:root {
  --brand-primary:   #103141;
  --brand-secondary: #2da7c7;
  --brand-accent:    #6cc551;
  /* optional custom dark overrides (empty unless set in settings) */
  --brand-primary-dark:   /* unset → auto-derived */;
  --brand-secondary-dark: /* unset → auto-derived */;
  --brand-accent-dark:    /* unset → auto-derived */;
}
```
Because `custom.less` is injected **after** the static `winter.css`, re-declaring these
`:root` vars lets the settings override the build-time defaults live.

### 2b. Purpose variables (named, derived — the vocabulary the whole backend uses)
Declared once (core `variables.less` for build-time defaults, mirrored/overridden by the
injected block). Light-mode values derive from the brand palette:
```css
:root {
  --ui-header-bg:        var(--brand-secondary);
  --ui-tab-bar:          /* mix(secondary, black 25%) */;
  --ui-tab-active:       var(--brand-secondary);
  --ui-tab-inactive:     /* mix(secondary, black 31%) */;
  --ui-breadcrumb-bar:   /* mix(secondary, black 16%) */;
  --ui-breadcrumb-cur:   /* mix(secondary, black 16%) */;
  --ui-list-active:      var(--brand-secondary);
  --ui-accent:           var(--brand-accent);
  /* …one per purpose currently hardcoded off @brand-* … */
}
```
Every backend rule references a **purpose var** (`background: var(--ui-tab-active)`),
never `@brand-secondary` or a raw hex. Wing rule = same var as its title (structurally
kills wing drift).

### 2c. Dark mode = auto-derive from the SAME brand vars (skin `darkmode.css`)
Under `.dark` / `html[data-color-scheme="dark"]`, redeclare the purpose vars using
`color-mix()` against the live brand vars, falling back to any custom dark override:
```css
html[data-color-scheme="dark"] {
  /* base for mixing toward */
  --ui-dark-base: #0d1117;

  --ui-header-bg:      color-mix(in srgb, var(--brand-secondary-dark, var(--brand-secondary)) 34%, var(--ui-dark-base));
  --ui-tab-bar:        color-mix(in srgb, var(--brand-secondary-dark, var(--brand-secondary)) 28%, var(--ui-dark-base));
  --ui-tab-active:     color-mix(in srgb, var(--brand-secondary-dark, var(--brand-secondary)) 34%, var(--ui-dark-base));
  --ui-tab-inactive:   color-mix(in srgb, var(--brand-secondary-dark, var(--brand-secondary)) 22%, var(--ui-dark-base));
  --ui-breadcrumb-bar: color-mix(in srgb, var(--brand-secondary-dark, var(--brand-secondary)) 26%, var(--ui-dark-base));
  --ui-breadcrumb-cur: color-mix(in srgb, var(--brand-secondary-dark, var(--brand-secondary)) 38%, var(--ui-dark-base));
  --ui-list-active:    var(--brand-secondary-dark, var(--brand-secondary));
  --ui-accent:         var(--brand-accent-dark, var(--brand-accent));
}
```
The `var(--brand-secondary-dark, var(--brand-secondary))` pattern = **custom dark palette
if set, else auto-derive from the brand color**. Change the brand color in settings and
BOTH light and dark chrome follow, with zero per-selector overrides. The mix percentages
replace today's scattered `--drk-accent-*` literals (`#1a4653`≈34%, `#153a45`≈26%,
`#123138`≈22%, `#1e515f`≈38% of `#2da7c7` toward `#0d1117` — tune to match).

### 2d. Custom dark palette (settings UI)
Add to `modules/backend/models/brandsetting/fields.yaml` (Colors tab): optional
`primary_color_dark / secondary_color_dark / accent_color_dark` colorpickers (+ a
"derive automatically" toggle). `BrandSetting` emits `--brand-*-dark` only when set;
darkmode.css already prefers them via the `var(x, fallback)` pattern above.

---

## 3. Implementation phases (ordered, low-risk)

**Phase 1 — Var foundation on the brand chrome (custom.less + darkmode.css).**
Runtime-compiled + skin only, no core build needed; cache-bust = `cache:clear`.
1. `custom.less`: prepend a `:root` block emitting `--brand-primary/secondary/accent` +
   the `--ui-*` purpose vars (LESS-derived light values). Rewrite its chrome rules to use
   the purpose vars.
2. `darkmode.css`: replace the `--drk-accent-*` block with the §2c `color-mix()`
   derivations; delete the piecemeal per-tab-type overrides (the chrome now reads the
   purpose vars, which are already dark). Keep wing rules only where a wing selector must
   exist at all (they inherit the same var as the title).
3. Verify: change `secondary_color` in branding → all chrome (light) retints; toggle dark
   → auto-derives; every tab type's wings match titles; no bright teal.

**Phase 2 — Migrate core LESS `@brand-*` → `var(--ui-*)`/`var(--brand-*)`.**
Files in §1. Declare build-time defaults in `variables.less` `:root`. Rebuild core
(`php artisan mix:compile` for the backend module per `winter.mix.js`). This makes the
static `winter.css` var-based too (so non-custom.less brand usages also react to settings
+ dark). ~10 files.

**Phase 3 — Custom dark palette UI.** fields.yaml + BrandSetting + lang strings.

**Phase 4 — Fold the rest of the dark palette** (the ~100 non-brand greys/bgs from
`color-centralization-audit.md`) into the same `:root` system for full consistency.

---

## 4. Risks / notes
- `color-mix()` needs a modern browser — the backend already targets modern (CSS vars,
  `:has()`, etc. in use). Acceptable.
- Core changes get clobbered by `composer update` → must land as a Winter core PR; keep a
  local branch (`wip/packagemanager-symlinked-plugins` pattern) to re-apply meanwhile.
- `BrandSetting::compileCss()` strips tags + refuses `@import` (security) — the `:root`
  emission must be plain CSS output, fine.
- Tune the mix percentages to match the current hand-picked `--drk-accent-*` so dark mode
  looks identical to today's (already-approved) muted teal.
