# Dark-mode / brand-colour architecture — handoff

_Last updated: 2026-08-25. Pick-up doc for continuing on another machine._

## TL;DR — where we are

1. **Dark-mode bug fixes (G1–G26): shipped.** On `wip/darkmode-fixes`, rebased clean onto `main`, open as **PR wintercms/wn-tailwindui-plugin#58**.
2. **Core brand-var exposure: merged.** `modules/backend/models/brandsetting/custom.less` now emits `:root { --brand-primary/secondary/accent }`, so those CSS vars are live in the backend.
3. **Active work = the light-mode colour cleanup** (Task #14 "brand architecture P2–P4"), driven by an interactive report (below). Nothing here is applied to core yet — it's the plan + approval gate.
4. **A full design mock exists** (`palette-ui-kit-fable.html`) — a *prototype* of the future skin direction. Reviewed by "Fable" at **A− / shippable direction**. NOT implemented in the real skin.

## Branches / PRs

- Plugin repo `wintercms/wn-tailwindui-plugin`:
  - `wip/darkmode-fixes` → **PR #58** (dark-mode fixes + Phase-1 brand-derived fancy accent). Rebased onto `main` (the Builder-support base was squash-merged as #57, so those 4 commits were dropped). Backup tag `backup/darkmode-fixes-pre-rebase` exists locally at the old tip if ever needed.
  - `darkmode.css` derives `--drk-accent-*` from `--brand-secondary` via `color-mix()` (falls back to committed literals if the core var isn't present, so #58 is self-contained).
- Core repo `wintercms/winter` (`develop`):
  - `custom.less` brand-var block: **merged**.

## The artifacts (all in `plugins/winter/tailwindui/darkmode-audit/`)

- **`color-consolidation.html`** ← START HERE for the current task. Interactive report: every core light-mode hardcode (152 distinct hex / 428 uses across 105 LESS files) clustered into **21 proposed `--wn-*` tokens**. Each group card = clickable circle swatches (winner ring-highlighted) + a live preview of the real UI elements it's used on; click a swatch to toggle the candidate in place. **This is the approval gate — the owner reviews and signs off / overrides each group's winner before any mass find-replace.**
- `color-centralization-audit.md` — the *dark plugin* palette audit (`--drk-*`), separate from the core light-mode work above.
- `color-misuse-audit.md` — ~34 off-palette misuse accents in the dark skin (still only audited, not fixed): e.g. `#3498db` (darkmode.css:82,133), `#0180ff` (981), gold filter-active, neon User.Statistics, media-manager title rainbow.
- `brand-color-variable-architecture.md` — the P1–P4 design doc.
- `findings.md`, `_summary.md`, `blog.md`, `core.md`, `redirect.md` — the G-item audit reports that drove PR #58.
- `palette-ui-kit-fable.html` — the design mock (see below).
- `Design System - Colours.pdf`, `Design System - Logo.pdf` — official brand references.

## The design mock — `palette-ui-kit-fable.html`

Self-contained HTML prototype of the proposed TailwindUI skin. Single `#stage`, top-right cluster (theme swatches Winter/Mario/Zelda/Fall + Tint + light/dark), everything token/`color-mix()`-driven so both modes work from one element set. Sections: brand-palette live pickers, palette scales, dashboard scoreboards, list widget (sortable/setup/reorder/zebra/totals), three form layouts (standard/fancy/sidebar = the full Winter.Test `record/fields.yaml` field gallery), snowcap tabs + nested tab hierarchy, alerts, empty/loading, modal. Mobile-responsive. Fonts: Public Sans + Mulish.

**It is a proposal, not shipped.** Porting it into the real skin is a *separate* effort (feasibility audit concluded it's ~all plugin-side CSS + the one small core var-PR, no core markup changes).

### Locked design decisions (from the owner)
- Palette = the official Winter Design System: Indigo Dye (primary) · Pacific Blue (accent) · Slate (neutral) · Mantis (success) · Flame (danger) · **Marigold** (warning, muted gold S 62 between Mantis/Flame — not the loud amber).
- Fonts: **Public Sans** (base) + **Mulish** (headings, at display sizes only — dashboard KPIs / empty-state / modal titles; the dense chrome stays ≤14px).
- Tabs = "snowcap"; fancy header uses **Option B** (flowing feet on the active tab only). Snowcap indicator inset unified to 12px.
- Totals shown in **both** thead and tfoot (intentional). All six tab systems kept (reflect real Winter breadth). Idle sort = bidirectional ⇅ glyph.
- Brand-driven vs fixed: brand pickers drive accents/buttons/tabs/selection/header (via `color-mix` per mode); neutrals + semantic Mantis/Flame/Marigold stay fixed. Optional dark "chrome tint" mixes brand-primary into the dark surfaces.
- Luminance-guarded ink (`--hdr-fg`/`--primary-fg`/`--on-ink` computed in JS) so light brands (e.g. the Fall preset) don't ship white-on-orange.

## Next actions (ordered)

**A. Core brand-var Tier-1 swap** (small, proves the pattern). In core LESS, swap the ~14 semantic vars that map directly to a brand colour and have zero LESS colour-fn dependents → `var(--brand-x, @brand-x)` (keep the LESS fallback):
`@link-color`, `@color-focus`, `@color-sortable-active`, `@color-list-active-border`, `@color-select-active-bg`, `@component-active-bg`, `@highlight-active-bg`, `@color-sidebarnav-tree-active-marker`, `@color-stripe-loader`, `@tooltip-bg`, `@color-list-progress-bg`, `@color-list-nav-arrow`, `@color-datepicker-today-text`, `@color-filter-items-bg-hover`. Then delete the now-redundant matching blocks from `custom.less`, rebuild `winter.css`, verify a brand-colour change propagates live. (Caveats: `@link-color` has 1 hover-darken → use `color-mix`; `@color-fancy-form-tabless-fields-bg` has 5 computed dependents → leave / plugin's domain.)

**B. Canonical neutral token set** — the one design decision. Approve the `--wn-*` winners in `color-consolidation.html` (152 distinct → 21 tokens; collapse the 10 off-whites, the silver cluster, the `#2b3e50`/`#2a3e51` dupe, etc.). Winners prefer the already-declared-but-bypassed `@gray-*`/`@color-*` values.

**C. Mechanical replacement** — emit the approved tokens as a `:root` block, find-replace the 428 core hardcodes → `var(--wn-*)` (scriptable from the report/audit map), rebuild `winter.css`.

**D. Dark mode = token redefinition** — once core renders from ~20 `--wn-*` vars, dark mode becomes a redefinition block under `.dark`; the plugin's `darkmode.css` per-element overrides (~64 KB) largely delete themselves. Then also apply the ~34 `color-misuse-audit.md` fixes.

## The point of all this
Core currently hardcodes 430 colour occurrences (152 distinct, ~10 near-duplicate off-whites, a bypassed `@gray-*` scale) — which is *why* the dark skin needs hundreds of per-element overrides. Centralising to ~20 CSS-var tokens makes the palette retunable in one place and lets dark mode + brand changes flip tokens instead of overriding rules — drastically simplifying overrides in **both** core and the plugin.

## Build / verify notes
- Plugin skin build: `php artisan vite:compile Winter.TailwindUI` then `php artisan winter:mirror public --relative`; bust brand CSS cache with `php artisan cache:clear`.
- Core backend LESS → `winter.css`: rebuild via the backend module's mix pipeline (`modules/backend/winter.mix.js`).
- `composer update` clobbers `modules/` — core changes must go via a Winter core PR (same as the merged `custom.less` change).
- Local dev: winter.test (Herd); backend creds + browser-loop steps are in the private memory `darkmode-audit-env.md`.
- To open the mock/report: they're static HTML — open the file directly in a browser (do NOT publish as a Twig CMS page without `{% verbatim %}` + `layout=""`, or Twig chokes on the `{% for %}` code samples).
