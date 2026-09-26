# Core colour tokens — handoff

_Updated 2026-09-14. Companion to `HANDOFF.md` (dark-mode fixes / PR #58 / the P1–P4 plan).
This document covers the core light-mode colour tokenisation._

---

## STATUS: shipped. Open as wintercms/winter **PR #1541**.

**Branch:** `wip/colour-tokens-experiment`, based on `develop` at `44e9d68f8`.
**Four commits**, 124 files, +1343 / −1161.

| commit | what |
|---|---|
| `41b708ce1` | Refresh backend brand assets (the logo swap) |
| `27e892565` | Only label the fancy delete button when it has a title |
| `3502bf1ab` | Route core backend colours through CSS custom properties |
| `dbd532a06` | Fix tab and toolbar focus indicators, and fancy tab contrast |

**661 of 717** core colour literals now resolve through **168 tokens** in
`modules/system/assets/ui/less/tokens.less`, imported by `storm.less`.

---

## What the tokenisation actually is

Every rewrite keeps the original value as the `var()` fallback:

```less
color: #666666;   ->   color: var(--wn-text, #666666);
```

So compiled output is unchanged if the token block is ever absent, and the change is
exactly reversible — `tools/unapply.php` reads the literal back out of the fallback.
That single property is the whole safety story.

Three groups of token, applied by three passes:

| group | count | values | script |
|---|---|---|---|
| Neutral scale, snapped onto the DS slate/indigo ramps | 28 | **changed** | `apply.php` |
| Identity colours (flash, callout, chart, file-type icons), named from the LESS variable they already sat on | 68 | preserved exactly | `apply-identity.php` |
| Inline colours with no variable to borrow a name from, named by source file + role | 68 | preserved exactly | `apply-inline.php` |

Only the 28 neutral tokens change a pixel. The largest single shift is body text
`#666666 → #445a6b`; the backend moves from neutral grey toward blue-slate.

---

## The tooling — now in git

`darkmode-audit/tools/`. The first set lived only in a scratchpad and was lost when it
was cleared; that is why these are committed. **Read `tools/README.md` first.**

| script | what |
|---|---|
| `apply.php` | replays `token-map.json` and regenerates `tokens.less`. Idempotent. |
| `unapply.php` | reverts every rewrite, removes `tokens.less` and its import |
| `build.sh` | compiles **both** pipelines with plugins disabled, restores their prior state |
| `verify.php` | proves no selector paints a different colour set than pristine. `--capture` remakes baselines |
| `contrast.php` | WCAG 2.1 before/after on pairs that genuinely co-occur |
| `extract-map.php` | re-captures `token-map.json` from the applied tree |
| `lab.php` | CIELAB / ΔE / chroma / WCAG helpers |

**Changing a colour** is: edit `tools/tokens-values.json` → `apply.php` → `build.sh`.
`tokens.less` is generated — never hand-edit it.

`token-map.json` was captured *from the applied tree* rather than by re-running the
original role-inference heuristics. Those were tuned interactively and are not
reproducible; reading the tree back cannot drift from what was built and reviewed.

---

## What is left

### 1. The locked 56 — needs a decision

LESS evaluates colour functions at compile time, so a variable feeding `darken()`,
`mix()` or `saturate()` cannot become a `var()`. Passing one in fails the build outright.
Concentrated in `global.variables.less` (25) and `global.mixins.gradient.less` (10).

The set is smaller than it looks. Of 44 brand-derived calls, **22 are in
`brandsetting/custom.less`, which is compiled at runtime from the user's brand settings**
— they already track branding, there is nothing to fix. Of the rest, only **9 are
`darken`/`lighten`**; the other 35 are `saturate`/`mix`/`desaturate`, which `color-mix`
cannot express — converting those means *redesigning* the relationship, not translating it.

**Recommendation: leave them.** Adopt `color-mix` case-by-case where it earns its keep
(the fancy tab ladder in `dbd532a06` is the worked example: runtime brand var + a simple
lighten/darken + a visible payoff). A mass migration buys little and carries the
`@supports` liability below.

Before converting anything for dark mode, measure which of the 56 actually need a
different dark value. That is cheap and may reduce the set to near zero.

### 2. Eleven pre-existing contrast failures

Not introduced here — `contrast.php` separates "regressed by the tokens" from "already
failing". Datepicker selected state (2.52), `--wn-text-secondary` on raised (2.51),
progress bar (3.14), flash default (4.09). **Each is now a one-line fix in
`tokens-values.json`**, which is the first concrete payoff of the token layer.

### 3. No tests

This is a CSS/asset change with no natural unit test, but nothing guards the invariants.
The cheapest meaningful guard: assert every `var(--wn-*)` in `modules/**/*.less` resolves
to a definition in `tokens.less`. That is the failure mode that is *silent* — a typo'd
token name just falls back to the hex — and the one most likely to regress as people edit
LESS later.

---

## Traps that cost real time

**There are two asset pipelines.** `winter.css` comes from `winter:util compile less`;
`storm.css` comes from mix (`modules/system/winter.mix.js`). `storm.less` is **not**
imported by `winter.less`, and `tokens.less` is imported by `storm.less` — so the `:root`
block only ships in `storm.css`. Running one without the other leaves the tokens inert or
stale. `tools/build.sh` runs both.

**`winter:util compile less` aborts the whole run on the first plugin whose LESS fails**,
quietly enough to look successful, leaving `winter.css` silently stale. `Winter.Builder`'s
`buildingarea.less` calls `.clearfix()` with no import and does not compile against this
core — pre-existing, reproduces on pristine core. `build.sh` disables plugins for the
compile and greps the output for real errors.

**A `var()` fallback does NOT rescue a defined-but-invalid value.** If a token is
*defined* as something the browser cannot parse (e.g. `color-mix()` on an old engine),
`var(--token, #hex)` does **not** fall back — the declaration is invalid at
computed-value time and the element paints *transparent*. The usual "static declaration
first, `var()` second" trick does not help either. Both verified in-browser. So a token
needing a feature guard must be declared **inside** `@supports`, never unguarded: where
the guard fails the token stays *undefined*, which is the one case `var()` covers.

**`verify.php` cannot catch a bad palette.** It collapses `var()` to the *fallback*, so it
validates the plumbing, not the values. A token value change is invisible to it. It would
never have caught the washed-out media manager. Only the browser and `contrast.php` do.

**`overflow: hidden` is still a scroll container.** It clips visually but stays
programmatically scrollable, so the browser will scroll it to reveal a focused
descendant — and that offset survives blur. This is what made the tab strip jump 2px
permanently. `overflow: clip` is not always available (beside a scrolling axis it computes
back to `hidden`, and the tab strip must stay horizontally scrollable for
`drag.scroll.js`); a negative `scroll-margin` on the focusable child is the fix that
changes no layout.

**Never derive a path from `__DIR__` / `BASH_SOURCE` in this directory.** It is normally
reached via `plugins/winter/tailwindui` → `plugins-src/Winter/TailwindUI`, and
`plugins-src` is the *same directory* as the sibling `Plugins/` checkout, so `realpath()`
walks out of the core tree. `coreRoot()` searches for `artisan` + `storm.less` instead.

**`composer update` overwrites `modules/`** with whatever the packaged modules contain,
which can be *newer* than your git checkout. It did here. Re-capture `verify.php`
baselines afterwards or upstream edits look like token regressions.

**A green build proves nothing about colour.** Every catastrophic misrouting found in this
work compiled cleanly: a red file icon turned white, `#fff` turned dark navy, the list
zebra stripe turned white and vanished.

---

## Findings that survive regardless

**1. Group by role, not by colour.** `@body-bg` and `@table-bg-accent` are both `#f9f9f9`
in light but `#0d1117` vs `#0c121a` in dark. Weld them and zebra striping disappears the
moment dark mode becomes a token redefinition. Two tokens may share a value today and
still must stay separate.

**2. You cannot de-duplicate your way to a small palette.** Collapsing only what is
perceptually invisible (ΔE 3) still leaves 123 distinct values. A small palette must be
*chosen*, not derived.

**3. Identity colours must be excluded from snapping** — but not from tokenisation. They
exist to be *distinct*, so they keep their exact values; they still need names so theming
can redefine them. That distinction is what passes 2 and 3 implement.

**4. Role inference is the weak link, not the token set.** Kind (surface/text/border/icon)
from the CSS property is reliable; tier from selector heuristics is not. The fit guard
(`min(ΔE to canonical, ΔE to target)` ≤ 10) exists because a green build cannot catch a
misrouted colour.

**5. Two tokens sharing a canonical value break tier selection.** Nearest-canonical
matching cannot separate them, so one silently swallows the other's members —
`surface-raised` ate `surface-input`, `hover`/`canvas` ate `stripe`. Watch for it when
adding tokens.

---

## Local environment notes

- 30 plugins symlinked into `plugins/winter/`; demo data seeded from every `scaffold:*`
  command (user, blog, pages, blocks, translate, redirect, easyforms).
- `storage/app/media/test-files/` has one empty file per supported file-type icon (30) —
  a direct visual check that the identity colours survived untouched.
- A second backend user `focustest` (id 2) exists because editing user 1 redirects to
  My Account; created to reach the fancy layout.
- **No controller in core uses `formLayout: fancy`** — it is only a
  `winter:create:controller --layout` option. Winter.Blog does use it, which is where the
  focus work was verified.
- Branding `custom_css` was cleared earlier: it held a leftover security-test payload that
  made `BrandSetting::compileCss()` throw, so `--brand-*` never reached the page. Keep it clear.

## Superseded files, safe to delete

`color-consolidation-roles.html`, `palette-consolidation.html` (1.5 MB, wrong scope),
`uikit-v5.html` (a 2-byte-different copy of `palette-ui-kit-fable.html`).
`color-token-map.html` and `color-consolidation.html` predate the fit guard — their
groupings are looser than what shipped.
