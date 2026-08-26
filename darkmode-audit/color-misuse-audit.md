# Winter CMS backend — color MISUSE audit (dark-mode focus)

Audit-only. No files modified. Companion to `color-centralization-audit.md` (raw inventory) — this report is about **wrong / arbitrary semantic role**, not raw counts.

Scope: `plugins/winter/tailwindui/assets/src/css/{darkmode.css, components/fancy-layout.css, custom.css, widgets/table.css, components/*.css}` + core LESS (`modules/system/assets/ui/less/*`, `modules/backend/assets/less/**`, `modules/backend/models/brandsetting/custom.less`).

Line numbers are from the **current** `darkmode.css` (2204 lines) — the file has grown since the inventory (which was ~2163 lines), so line refs differ from that document.

**Reference palette (the roles colors SHOULD map to):**
- brand-primary `#103141` navy / brand-secondary `#2da7c7` teal / brand-accent `#6cc551` green
- semantic: success green, info blue/cyan, warning amber-orange, danger red
- neutral greys: text / border / bg
- **selection / active = brand teal** (`--drk-bg-color-selection #2c434e`, `--drk-accent-*` teal family)
- syntax-token = the self-contained VS Code/GitHub code-highlighting set (legit, not chrome)

**Headline finding:** core LESS (`brandsetting/custom.less`, `fancylayout.less`) is disciplined — active/selection everywhere derives from `@brand-secondary` (teal). The dark skin (`darkmode.css`) then **re-implements those same states with a scattered set of ad-hoc blues, golds, tans, corals, salmons and named CSS colors** that have no palette basis. Almost every misuse below is skin-owned and lives under `.dark`.

---

## Section 1 — Worst offenders (ranked)

| # | file:line | color | element | why it's egregious | fix → role |
|---|---|---|---|---|---|
| 1 | darkmode.css:2059-2072 | `aqua` / `greenyellow` / `orangered` / `cyan` | User.Statistics widget: total / activated / banned / all table cells | **Four named CSS colors, zero palette basis.** A neon rainbow. "banned" being orange-red is the only vaguely-semantic one; "activated" as neon greenyellow and "total"/cells as aqua/cyan are pure noise and clash with the whole teal-navy backend. | total→neutral-text; activated→success; banned→danger; cells→neutral-text |
| 2 | darkmode.css:812, 816, 820 | `#ff9800`, `#d16969`, `#ffc107` | media-manager list item `h4` (generic), folder `h4`, file `h4` | Same conceptual thing (a media item title) rendered in **amber, VS-Code-red, and gold** depending on type. Folder titles being red reads as "error". None are states. | all → neutral-text (or one muted accent) |
| 3 | darkmode.css:916, 927, 1018, 1022, 1015 | `#deb887` tan, `#daeb60` yellow-green, `#1e261e`, `#34495e`, `#1e1600` | list table **sort links / sort-asc / sort-desc / active row** | The sort indicator is **burlywood tan**, its hover is **yellow-green**, asc-active is greenish-black, desc-active is slate-blue, active-row is brownish-black — five unrelated hues for one control's states. Should be one accent (teal) with value shifts. | link/hover→neutral-text + teal selection; active bands→`--drk-bg-color-selection` |
| 4 | darkmode.css:939-952 | `#ffd700` gold ×5 | `.control-filter .filter-scope:hover/.active` + `.filter-has-popover:hover` | **Active/hover filter scope = gold.** "Active" everywhere else in the backend is brand teal. Gold appears nowhere else as an active state; it reads as a warning. | selection / brand-teal |
| 5 | darkmode.css:339, 344, 832 | `#00b0b3`, `#00ffff`, `#00ffff` | report-widget links, media file `item-title` | `#00ffff`/`cyan` pure-cyan links and titles — maximum-saturation cyan that isn't the brand teal and isn't the info blue. Two different cyans for links vs hover. | link→brand-secondary/info; hover→lighten |
| 6 | darkmode.css:751, 755, 792, 796, 816, 836, 1080 | `#d16969` (×5 as text), `#ce9178`, `#d7ba7d` | media selector-group active, tree-path root, media placeholder text, preview title, folder title, list `p.no-data` | `#d16969` (VS-Code error-red) used as **plain label / active / no-data text** — it signals "error" where nothing is wrong. `no-data` being gold `#d7ba7d` is another arbitrary pick. | all → neutral-text / muted |
| 7 | darkmode.css:82, 133 | `#3498db` | mainmenu active top-border, sidenav active left-border | **Primary navigation "active" indicator is a random flat-UI blue**, while core LESS marks active nav with `@brand-secondary` teal (custom.less:52,74,83,123). Direct inconsistency with core. | brand-secondary (teal) |
| 8 | darkmode.css:1148, 1218, 703, 707 | `#fa8072` salmon, `#ff7f50` coral, `#bdb884` tan, `#7fffd4` aquamarine | `.field-section h4`, mediafinder `.find-object h4`, component `.description`, component `.alias` | Section/finder **headings tinted salmon & coral**; component description tan, alias aquamarine. Decorative hues on neutral headings/labels. | all → neutral-text / muted |
| 9 | darkmode.css:1634, 310, 314 | `#f9e23d` yellow, `#bbbbbb`/`#c8c8c8` | theme-selector `.theme-description h3`, welcome message | Theme name heading is **bright yellow** — arbitrary; every other heading is neutral. | neutral-text/bright |
| 10 | darkmode.css:258, 2019 | `#005087`, `#0654ca` | balloon-selector active, notes toolbar `.btn-icon:hover` | Balloon-selector **active = deep blue** in dark mode, but core LESS sets the very same `.control-balloon-selector li.active` to `@brand-secondary` teal (custom.less:204). Notes hover is yet another blue. | brand-secondary / selection |

---

## Section 2 — By category

### Category 1 — Semantic color used non-semantically
A semantic hue (danger-red / success-green / warning-amber / info-blue) applied where there is no such state.

| file:line | color | element/selector | why wrong | suggested role |
|---|---|---|---|---|
| darkmode.css:751 | `#d16969` (error-red) | `.nav.selector-group li.active a` | red = "active", not an error | selection/neutral-text |
| darkmode.css:792, 796 | `#d16969` | media placeholder `i.icon-level-up`, `p` | red on an empty-state hint (not an error) | neutral-text/muted |
| darkmode.css:816 | `#d16969` | media folder `h4` | folders aren't errors | neutral-text |
| darkmode.css:836 | `#d16969` | preview-sidebar `[data-label="title"]` | a title is not an error | neutral-text |
| darkmode.css:335 | `#ce9178` (orange) | `.status-text.warning` | *technically* a warning, but uses a **syntax-string orange**, not `@brand-warning #de8754`; drifts from the semantic warning hue | warning (brand-warning) |
| darkmode.css:759 | `#31ac5f` (green) | `table.name-value-list td` | success-green on a plain key/value table | neutral-text |
| darkmode.css:812, 820 | `#ff9800`, `#ffc107` (amber) | media item / file `h4` | amber = "warning" applied to ordinary titles | neutral-text |
| darkmode.css:939-952 | `#ffd700` (gold≈warning) | filter-scope active/hover | gold reads as warning on an *active* control | selection |
| darkmode.css:2067 | `orangered` | user-stats banned | right idea, wrong hue (named color, not `--drk-color-danger`) | danger |
| darkmode.css:1077, 1234 | `#ed1700`, `#ef0319` | fileupload error `h4`; **fileupload remove/drag handles** | 1077 is a genuine error (OK-ish but off-palette red); **1234 paints the remove-button AND the plain drag-handle danger-red** — the drag handle is not destructive | danger (var) for remove only; drag-handle→neutral |

### Category 2 — Arbitrary one-off hues (no palette role)

| file:line | color | element/selector | why wrong | suggested role |
|---|---|---|---|---|
| darkmode.css:2059 | `aqua` | user-stats total | named neon, no basis | neutral-text |
| darkmode.css:2063 | `greenyellow` | user-stats activated | named neon | success |
| darkmode.css:2071 | `cyan` | user-stats table cells | named neon | neutral-text |
| darkmode.css:339, 344, 832 | `#00b0b3`, `#00ffff` | report links, media item-title | max-sat cyans | info/brand-secondary |
| darkmode.css:916, 919 | `#deb887` (burlywood) | table sort links, list-setup link | tan on interactive links | neutral-text |
| darkmode.css:927 | `#daeb60` (yellow-green) | sort link hover | random | neutral-text/teal |
| darkmode.css:703, 1344 | `#bdb884` (tan) | component `.description`, popover-head `p` | tan on muted text | muted |
| darkmode.css:707 | `#7fffd4` (aquamarine) | component `.alias` | named-ish neon mint | muted |
| darkmode.css:1081, 1185 | `#d7ba7d` (gold) | list `p.no-data`, fileupload `h4` | gold on empty-state / heading | muted/neutral |
| darkmode.css:1148 | `#fa8072` (salmon) | `.field-section h4` | salmon heading | neutral-text |
| darkmode.css:1218 | `#ff7f50` (coral) | mediafinder `.find-object h4` | coral heading | neutral-text |
| darkmode.css:1634 | `#f9e23d` (yellow) | theme-description `h3` | yellow heading | neutral-text |
| darkmode.css:1984 | `#8b7964` (brown) | import bindings `:before` icon | brown accent | muted |
| darkmode.css:686, 691 | `#6fc2f1` (light blue) | component-item text-shadow glow | one-off cyan glow | neutral / drop |
| darkmode.css:1038 | `#1f99dc` (blue) | checkboxlist control `i:hover` | one-off blue hover | brand-secondary |
| darkmode.css:957 | `#3e71a5` (blue) | filter-popover bottom border | one-off blue border | border/teal |
| darkmode.css:1166 | `#27709c` (blue) | codeeditor 2px border | one-off blue frame | border/brand-secondary |
| darkmode.css:184?, 226 | `#f0f8ff` (aliceblue) | select2 arrow glyph | near-white named | neutral-text |
| fancy-layout.css:1148 salmon etc. covered above | | | | |

*Syntax-token colors (LEGIT, self-contained — note, do not "fix"):* darkmode.css:1940-1947 `#ff7b52 #c586c0 #98c379 #569cd6 #dcdcaa #f97583 #7d8f5a #c9d1d9` (log-viewer snippet), plus `#7d8f5a` beautifier-class (1876), and the GitHub git-diff palette `#58a6ff`/`#3fb950`/`#238636` (1543,1593,1133) which mirror core's markdown/diff rendering. These are a coherent set; recommend grouping into `--drk-syntax-*` but they are not "random chrome".

### Category 3 — Wrong-meaning states (active/selection/hover using off-role color)

| file:line | color | element | should be | note |
|---|---|---|---|---|
| darkmode.css:82 | `#3498db` blue | mainmenu active | brand-secondary teal | core uses teal for active nav |
| darkmode.css:133 | `#3498db` blue | sidenav active | brand-secondary teal | same |
| darkmode.css:258 | `#005087` blue | balloon-selector **active** | brand-secondary teal | core LESS custom.less:204 sets this to `@brand-secondary`; the dark override contradicts it |
| darkmode.css:939-952 | `#ffd700` gold | filter-scope active/hover | selection/teal | active-state as gold |
| darkmode.css:981, 985, 1788 | `#0180ff`, `#028dff` blue | active control-table borders / toolbar | selection/teal | "active table" marked with two different blues |
| darkmode.css:100,117,367,399,405,411,415 | `#1f6feb` blue (+ `#0f5acc` border, `#0c2d6b` focus-ring 191/239) | dropdown-menu & account-menu **hover/focus**, manage-widgets open | selection/teal | internally consistent, but it's a GitHub blue "selected item" role where the rest of the backend uses teal; the whole menu-selection family should collapse to ONE selection color and ideally the brand teal |
| darkmode.css:237, 189 | `#1f6feb` + `#0c2d6b` | select2 search focus ring | focus (brand) | focus ring blue vs. teal selection elsewhere |
| darkmode.css:1015,1018,1022 | `#1e1600`,`#1e261e`,`#34495e` | active row / sort-asc / sort-desc backgrounds | `--drk-bg-color-selection` | one "active/selected row" tone, not three |

### Category 4 — Dark-mode-specific clashes (jarring / off-palette clusters)

| file:line | color | element | issue |
|---|---|---|---|
| darkmode.css:2059-2072 | aqua/greenyellow/orangered/cyan | user-stats | neon named colors on a `#000` panel — the single most jarring block in dark mode |
| darkmode.css:916/927/1018/1022 | tan/yellow-green/greenish/slate | sort controls | rainbow within one widget |
| darkmode.css:939-952 | `#ffd700` gold | filter scope | gold "active" clashes with teal "active" used elsewhere the same screen (list + filter bar sit together) |
| darkmode.css:812/816/820 | amber/red/gold | media titles | traffic-light coloring of non-state titles |
| darkmode.css:82/133 teal-vs-blue | `#3498db` | nav active | blue nav-active vs teal tab/breadcrumb-active on the same layout |
| darkmode.css:339/344 | `#00b0b3`→`#00ffff` | report links | link and hover are two unrelated cyans |
| darkmode.css:686/691 | `#6fc2f1` | component glow | stray light-blue text-shadow, no analog anywhere else |

---

## Section 3 — "Same thing, many colors"

One conceptual role, currently rendered with N different colors:

**ACTIVE / SELECTION indicator** — should be ONE (brand teal / `--drk-bg-color-selection`):
- `#3498db` mainmenu active (82), sidenav active (133)
- `#005087` balloon-selector active (258)
- `#ffd700` filter-scope active/hover (939-952)
- `#0180ff` active table border (981,985), `#028dff` active toolbar (1788)
- `#1f6feb` menu item hover/focus "selected" (100,367,399,411,415)
- `#1e1600` / `#1e261e` / `#34495e` active-row / sort-active backgrounds (1015,1018,1022)
- `--drk-bg-color-selection #2c434e` (the *correct* one — used only at 1854,1936)
- core LESS correctly uses `@brand-secondary` teal for all active nav/tabs/tree/balloon.
→ **8+ distinct colors for "active".**

**SORT indicator** (list column header): tan `#deb887` (916,919), hover yellow-green `#daeb60` (927), asc-active `#1e261e` (1018), desc-active `#34495e` (1022). → 4 colors for one control.

**LINK color** (backend body links): `#00b0b3`/`#00ffff` (report widget 339,344), `#03a9f4` (log file link 1892), `#58a6ff` (git-diff 1543), `#1f99dc` (checkboxlist 1038). → 5 blues/cyans, none the brand teal.

**"NO DATA" / empty-state text**: gold `#d7ba7d` (list 1080), red `#d16969` (media placeholder 792,796). → 2 colors; should be one muted-neutral.

**HEADING accents** (`h3`/`h4` in widgets): salmon `#fa8072` (1148), coral `#ff7f50` (1218), gold `#d7ba7d` (1185), yellow `#f9e23d` (1634), tan `#bdb884` (1344), red `#d16969` (816,836), amber `#ff9800`/`#ffc107` (812,820). → 7+ colors for "a heading".

**DELETE / DESTRUCTIVE**: brick `#a5382c`/`#bc4436` (fancy-layout.css:183,192 + fancylayout.less:721 — the intentional delete pill), plus `#ed1700` (1077), `#ef0319` (1234), `#9f0700` (danger border 1429/1437), `--drk-color-danger #f85149`, `orangered` (2067), core Tailwind `bg-red-*` (button/counter/badge/button-group). → the pill brick is a deliberate exception, but the remaining delete/danger reds are ~5 shades that could collapse to `--drk-color-danger`.

**FOCUS RING**: `#0c2d6b` (191,239) vs teal selection elsewhere — inconsistent focus vs selection tone.

---

## Section 4 — Summary counts

**Distinct OFF-PALETTE accent colors flagged as misuse** (excluding legit syntax-token set, excluding neutral greys/bg darks which are a separate consolidation task): **~34**, by hue family:

| hue family | distinct values | examples |
|---|---|---|
| blues (non-brand) | 9 | `#3498db #005087 #0180ff #028dff #1f99dc #3e71a5 #27709c #0654ca #184d70` (+ menu-blue `#1f6feb`/`#0f5acc`/`#0c2d6b` as a 3-tone GitHub set) |
| cyans/teals (non-brand) | 3 | `#00b0b3 #00ffff #6fc2f1` |
| golds/yellows | 4 | `#ffd700 #d7ba7d #daeb60 #f9e23d` |
| oranges/ambers | 3 | `#ff9800 #ffc107 #ce9178` |
| corals/salmons | 2 | `#ff7f50 #fa8072` |
| tans/browns | 3 | `#deb887 #bdb884 #8b7964` |
| reds (excess, beyond `--drk-color-danger`) | 4 | `#d16969 #ed1700 #ef0319 #9f0700` |
| greens (non-brand) | 1 | `#31ac5f` (used as plain text, not success) |
| named neon | 4 | `aqua greenyellow orangered cyan` |

**Top consolidation wins (highest impact first):**
1. **Collapse the 8+ "active/selection" colors → one brand-teal selection var.** Biggest single coherence win; also aligns the dark skin with the core LESS which already uses `@brand-secondary` for every active state. (nav `#3498db`, balloon `#005087`, filter `#ffd700`, table `#0180ff/#028dff`, active-row `#1e1600/#1e261e/#34495e`, menu `#1f6feb`.)
2. **Kill the user-statistics named-neon block** (aqua/greenyellow/orangered/cyan) → success/danger/neutral.
3. **Detox the media-manager rainbow** (amber/red/gold/coral titles + cyan item-title) → neutral-text.
4. **Unify sort indicator** to neutral-text + teal active band (removes tan/yellow-green/greenish/slate quartet).
5. **Unify heading accents** (salmon/coral/gold/yellow/tan/red on `h3`/`h4`) → neutral-text.
6. **Fold excess danger reds** (`#d16969 #ed1700 #ef0319 #9f0700`) into `--drk-color-danger`, and stop using `#d16969` as plain/active text.
7. **Route body links** through one link var (brand teal or info) instead of `#00b0b3/#00ffff/#03a9f4/#58a6ff/#1f99dc`.

**Note on `#1f6feb`:** it is *internally consistent* (always menu hover/focus "selected item", GitHub-style) so it's the least-bad blue — but it still represents "selection" in a blue where the brand's selection is teal. Consolidating it into the teal selection role is optional-but-recommended for whole-backend coherence.

**Out of scope / leave alone:** syntax-token set (1940-1947, `#7d8f5a`), GitHub git-diff/callout `rgb()` alpha set + `#58a6ff/#3fb950/#238636` (mirror core), the deliberate delete-pill brick `#a5382c/#bc4436`, and the light-mode `custom.css`/`widgets/table.css` colors.
