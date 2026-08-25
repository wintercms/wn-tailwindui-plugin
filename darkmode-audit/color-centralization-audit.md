# Winter.TailwindUI backend skin — hardcoded color audit & CSS-variable centralization plan

Audit-only report. No CSS files were modified.

Scope audited (skin **source** only; NOT `dist/`, NOT `node_modules`):

- `assets/src/css/darkmode.css` (2163 lines — 255 color occurrences, the primary offender)
- `assets/src/css/components/fancy-layout.css` (314 lines — 9 color occurrences)
- `assets/src/css/widgets/table.css` (1 occurrence, light-mode)
- `assets/src/css/custom.css` (1 occurrence, light-mode)
- `assets/src/css/base.css`, `assets/src/css/app.css`, `assets/src/css/widgets/all.css`, `assets/src/css/components/{all,badge,button,button-group,counter,form,froala,menu,nested-form}.css` — **zero hardcoded colors** (all use `@apply` Tailwind utilities or `var(--drk-*)`).

**Total distinct hardcoded color values found: 134** (131 in `darkmode.css`, incl. the 10 already-declared `:root` vars values; 5 in `fancy-layout.css`; 1 in `widgets/table.css`; 1 in `custom.css`; named colors `greenyellow`/`orangered`/`cyan`/`red` counted).

The two files that hold ~99% of the colors — `darkmode.css` and `fancy-layout.css` — both compile into the same `app.css` bundle (`app.css` `@import`s them; `components/all.css` imports `fancy-layout.css`). Build: `php artisan vite:compile Winter.TailwindUI`.

---

## 0. Bundle & `:root` visibility (answers the fancy-layout question)

`app.css` is the single entry:

```
@import 'base.css';            /* @tailwind base/components/utilities */
@import 'components/all.css';   /* -> imports fancy-layout.css */
@import 'widgets/all.css';
@import 'custom.css';
@import 'darkmode.css';         /* declares :root { --drk-* } at the top */
```

Everything lands in one stylesheet. **CSS custom properties declared in `:root` in `darkmode.css` are visible to every rule in the bundle**, including `fancy-layout.css`'s *unlayered* rules (lines 276-314) and its `@layer components` rules. Custom-property resolution happens at cascade/computed-value time against the element's inherited value; it is independent of `@import` order and independent of `@layer` (layers only affect which *declaration* wins, not which `:root` a `var()` can read). `:root` matches `<html>`, so the values inherit to the whole document.

**Conclusion:** we can declare ALL new vars in the single existing `:root` block in `darkmode.css` and reference them from `fancy-layout.css` (layered or unlayered) with no duplication. No need to re-declare in `fancy-layout.css`. (Optional belt-and-suspenders: if we ever split the bundle, mirror the accent vars into `fancy-layout.css`'s own `:root`; not needed today.)

**Important scope caveat:** `darkmode.css` colors are all nested under `.dark { … }` or `html[data-color-scheme="dark"]` — safe to change without touching light mode. BUT `custom.css:127` (`background-color: white`) and `widgets/table.css:7` (`background: #e2e2e2`) are **NOT** under any dark selector — they are light-mode / always-on. Leave those out of the dark-palette refactor (see §2 Misc).

---

## 1. Every distinct hardcoded color value (count + file:line)

Counts include comment mentions; `rgb()` values are double-counted where they appear twice on one declaration line (webkit + standard). Grouped near-duplicates are flagged with ⇒ collapse hints.

### 1a. Already-declared `:root` var values (baseline palette)
| Value | Var it defines | Line |
|---|---|---|
| `#0d1117` | `--drk-bg-color-a` | 2 (also reused literally at 743×3, 744×3 — see below) |
| `#161b22` | `--drk-bg-color-b` | 3 |
| `#1b222c` | `--drk-bg-color-c` | 4 |
| `#21262d` | `--drk-bg-color-btn` | 5 |
| `#010409` | `--drk-bg-color-inset` | 6 (also in `--drk-box-shadow` line 11) |
| `#2c434e` | `--drk-bg-color-selection` | 7 |
| `#c9d1d9` | `--drk-color` | 8 |
| `#f85149` | `--drk-color-danger` | 9 |
| `#30363d` | `--drk-border-color` | 10 |

**Values that duplicate an existing var but are still written literally (should use the var):**
| Literal | Should be | Occurrences (file:line) |
|---|---|---|
| `#0d1117` | `var(--drk-bg-color-a)` | 743, 743, 743, 744, 744, 744 (a `background: #0d1117 … #0d1117` gradient/shorthand) |
| `#010409` | `var(--drk-bg-color-inset)` | 185, 1086, 1113, 1288, 1926 |
| `#21262d` | `var(--drk-bg-color-btn)` | 1642, 1651; **fancy-layout.css:289 (comment), 297** |
| `#161b22` | `var(--drk-bg-color-b)` | 3 only (var def) — no stray literals ✅ |
| `#c9d1d9` | `var(--drk-color)` | 1899, 2156 |
| `#f85149` | `var(--drk-color-danger)` | 9 only (var def) ✅ |
| `#30363d` | `var(--drk-border-color)` | 10 only ✅ |

### 1b. Fancy / accent TEAL family — THE KEY GROUP
| Value | Role | file:line |
|---|---|---|
| `#1a4653` | header band + master-tab active + active-tab wings | darkmode.css:509, 538, 552 (+ comment 526) |
| `#153a45` | breadcrumb bar + master-tabs container bar + secondary-content-tabs bar | darkmode.css:513, 529, 548 (+ comment 525) |
| `#1e515f` | current/active breadcrumb segment (a touch brighter) | darkmode.css:520 |
| `#123138` | recessed inactive master-tab title | darkmode.css:543 |

The **bright source teals** named in the brief (`#2da7c7`, `#238199`, `#175767`, `#0c2d35`, `#2c9cb9`, `#0f88a8`, `#0e7d9a`, `#12303a`) **do NOT appear as literals anywhere in the source** — they come from the runtime `--secondary` brand color family and *leak through wherever the skin does not override the tab bar/wing*. See §4 for exactly which tab types still leak.

### 1c. Base dark backgrounds NOT covered by an existing var (one-off darks)
| Value | file:line | Notes |
|---|---|---|
| `#202833` | 484, 619 | hover bg (filelist / component-list) ⇒ candidate: one "hover-raised" var |
| `#0d1520` | 1757 | control-table header th bg |
| `#121922` | 1770 | control-table zebra row |
| `#10161e` | 1558 | (log/table region) |
| `#081934` | 1568, 1572 | ⇒ near `#0c2d6b`? no — deep navy |
| `#0e253c` | 850, 987 | deep navy panel |
| `#0c121a` | 868 | near-black panel |
| `#192634` | 861 | |
| `#1a2938` | 825 | |
| `#15232c` | 1696 | tab content bg region |
| `#13171d` | 119 | |
| `#1d242e` | 145 | |
| `#2c2f38` | 2161 | docs `pre code` bg |
| `#202833`, `#1a2938`, `#192634`, `#15232c`, `#1d242e`, `#13171d` | — | **all cluster around `--drk-bg-color-c` (#1b222c) ± a few points** ⇒ strong collapse candidates |
| `#0e253c`, `#0c121a`, `#081934`, `#0d1520`, `#121922`, `#10161e` | — | **cluster around `--drk-bg-color-inset` (#010409) / very-dark navy** ⇒ collapse to 1-2 "inset/recessed" vars |
| `#34495e` | 826, 827, 938, 939, 982 | slate-blue raised surface (5×) ⇒ its own var |
| `#395169` | 854 | lighter slate, pairs with `#34495e`/`#0e253c` |
| `#3e71a5` | 916 | filter-popover border accent |
| `#005087` | 227 | |
| `#184d70` | 2053, 2058 | sidepanel content-header bg + its `:after` |
| `#27709c` | 1125 | codeeditor 2px border |
| `#3498db` | 51, 102 | mainmenu active top-border + one more (light blue accent) |
| `#0c2d6b` | 160, 208 | deep blue (focus/selection accent) |

### 1d. Button/raised greys (cluster around `--drk-bg-color-btn` #21262d and mid-greys)
| Value | file:line |
|---|---|
| `#43474a` | 1148, 1154, 1162 (mediafinder/fileupload buttons) |
| `#313131` | 1139 |
| `#3b3b3b` | 1015 |
| `#3c3a3a` | 1787 |
| `#353535` | 915 |
| `#373838` | 222 |

### 1e. Borders / hairlines NOT using `--drk-border-color`
| Value | file:line | Notes |
|---|---|---|
| `#555` | 693, 766, 1615, 1623, 1627, 1641, 1717, 1800 (8×) | tab `span.title > span` top-border + others ⇒ mid-grey border var |
| `#565656` | 1019, 1713 | content-tabs `:before` rail |
| `#646464` | 266 | |
| `#6a6a6a` | 1164 (×2, inset+outset shadow) | mediafinder file-single shadow |
| `#7b7b7b` | 1140 | upload-object border |
| `#898989` | 1149 | mediafinder find-button dotted border |

### 1f. Text / foreground greys
| Value | file:line |
|---|---|
| `#d1d1d1` | 233, 238, 242, 246, 250 (5×) |
| `#bbb` | 137, 287, 321, 1168 |
| `#bbbbbb` | 279 |
| `#c5c5c5` | 404, 428, 1348 |
| `#c8c8c8` | 262, 283 |
| `#c3c3c3` | 891 |
| `#d6d6d6` | 1003, 1032 |
| `#d0…` misc: `#a0a0a0` 1081, `#a5a5a5` 267, `#9a9a9a` 417, `#999` 921, `#8a8a8a` 1002, `#8b8b8b` 1307, `#777` 767/1023/1027/1990, `#707070` 2098, `#646464` 266, `#b2bfca` 1685, `#8194a5` 1681, `#8b949e` 153, `#e0dfdf` 1422, `#e6ebed` 631, `#f0f6fc` 70/367/379/385 |
| **These 20+ near-grey text shades between `#777`–`#f0f6fc`** ⇒ collapse to ~3 tiers: bright text (`--drk-color`), muted text, faint text. |

### 1g. Status / semantic
| Value | Meaning | file:line | Owned/Override |
|---|---|---|---|
| `#ffd700` gold | active/hover filter scope, active state | 898, 903, 907, 911, 1677 | skin-owned |
| `#f9e23d` yellow | (log/warning) | 1593 | skin-owned |
| `#0180ff` blue | active control-table border | 940, 944 | skin-owned |
| `#028dff` blue | active control-table toolbar border | 1747 | skin-owned |
| `#03a9f4` blue | log link | 1851 | skin-owned |
| `#1f99dc` blue | 997 | | skin-owned |
| `#6fc2f1` light blue | 645, 650 | | skin-owned |
| `#58a6ff` blue | 1502 (git-diff add link, GitHub palette) | | override of core |
| `#0f5acc` / `#0654ca` / `#0c2d6b` blue | 378/386, 1978, 160/208 | | |
| `#31ac5f` green | success | 296, 300, 718 | skin-owned |
| `#3fb950` green | 1552 (GitHub add) | override | |
| `#238636` green | 1092 (GitHub btn) | override | |
| `#f85149` = `--drk-color-danger` | danger | (var) | |
| `#9f0700` deep red | 1388, 1396 | danger dark | |
| `#ef0319` / `#ed1700` red | 1193, 1036 | delete buttons | |
| `#a5382c` / `#bc4436` brick | fancy delete pill + hover | fancy-layout.css:183, 192 | skin-owned |
| `#fa8072` salmon | 1107 | | |
| `#f97583` red | 1905 (syntax) | | |
| `red` (named) | 1855 | log | |
| `greenyellow` | 2022 | user-stats activated | |
| `orangered` | 2026 | user-stats banned | |
| `cyan` | 2030 | user-stats table | |
| `#00ffff` cyan | 313, 791 | | |
| `#00b0b3` teal | 308 | (NOT the fancy header teal — a semantic accent) | |
| `#ff9800` / `#ffc107` amber | 771, 779 | | |
| `#ff7f50` coral | 1177 (mediafinder h4) | | |
| GitHub semantic `rgb()` alpha set (below) | callouts/labels | — | override of GitHub-style core |

**GitHub-style semantic alpha `rgb()` set** (used for label/callout backgrounds & borders — these mirror core's GitHub palette; skin-owned overrides):
| Value | file:line |
|---|---|
| `rgb(248 81 73 / 15%)` danger bg | 1383×2, 1387×2, 1464×2, 1492×2, 1530×2 (10) |
| `rgb(248 81 73 / 40%)` danger border | 1465, 1493, 1531 |
| `rgb(56 139 253 / 15%)` info bg | 1182, 1454×2, 1482×2, 1515×2 (7) |
| `rgb(56 139 253 / 40%)` info border | 1455, 1483, 1516 |
| `rgb(46 160 67 / 15%)` success bg | 1449×2, 1477×2, 1510×2 (6) |
| `rgb(46 160 67 / 40%)` success border | 1450, 1478, 1511 |
| `rgb(187 128 9 / 15%)` warning bg | 1459×2, 1487×2, 1520×2 (6) |
| `rgb(187 128 9 / 40%)` warning border | 1460, 1488, 1521 |
| `rgb(110 118 129 / 10%)` neutral bg | 110, 212, 218, 425 |
| `rgb(110 118 129 / 40%)` neutral border | 2154 |

### 1h. Syntax-highlighting colors (`#winter-log-viewer` snippet, lines 1899-1906, + docs)
| Value | Token | line |
|---|---|---|
| `#c9d1d9` | bracket | 1899 (= `--drk-color`) |
| `#ff7b52` | variable | 1900 |
| `#c586c0` | control | 1901 |
| `#98c379` | string | 1902 |
| `#569cd6` | number | 1903 (also 431) |
| `#dcdcaa` | html | 1904 |
| `#f97583` | bool | 1905 |
| `#7d8f5a` | comment | 1906 (also 1835) |
| `#d16969` | (no-data text / error strings) | 456, 710, 751, 755, 775, 795 (6×) — VS Code red |
| `#ce9178` | (string-ish orange) | 304, 714, 783 |
| `#d7ba7d` | (gold token) | 1040, 1144 |
| `#deb887` / `#bdb884` / `#daeb60` / `#8b7964` / `#7fffd4` / `#1e1600` / `#1e261e` | assorted token/console colors | 875/879, 662/1303, 886, 1943, 666, 974, 978 |
| docs: `#2c2f38` pre bg (2161), `rgb(110 118 129 / 40%)` code border (2154), `#c9d1d9` (2156) | | |

### 1i. Pure white / black / misc
| Value | file:line |
|---|---|
| `#fff` (18×) | darkmode 128,133,174,217,317,444,450,598,670,824,1243,1263,1264,1589,1655,1814; fancy-layout 184,193 |
| `#000` (5×) | darkmode 234,588,602,1008,2004 |
| `#fafafa` | fancy-layout 263(comment),285,290(comment) — light-mode active tab wing base |
| `#f0f8ff`,`#f9f9f9`,`#f5f5f5`,`#f7f7f7`,`#f2f2f2`,`#eee`,`#e2e2e2`,`#e0dfdf`,`#d6d6d6` | scattered near-whites |
| `rgb(0 0 0 / 50%)` | 1435 (overlay) |
| `rgb(0 154 247 / 25%)` + `rgb(30 94 145)` | 1204 (nested-form inset shadow pair) |
| `#8b949e` | 153 (GitHub muted) |
| **light-mode (NOT dark):** `white` custom.css:127; `#e2e2e2` widgets/table.css:7 | — leave alone |

---

## 2. Categorization summary (owned vs. override)

- **Base dark backgrounds** — `#0d1117/#161b22/#1b222c/#010409/#21262d/#2c434e` already vars. **Off-palette one-offs to fold in:** the "-c-ish" cluster (`#202833 #1a2938 #192634 #15232c #1d242e #13171d`), the "inset/recessed navy" cluster (`#0e253c #0c121a #081934 #0d1520 #121922 #10161e`), and distinct raised surfaces `#34495e`(5×)/`#395169`. All **skin-owned**.
- **Borders** — `--drk-border-color` covers most; off-palette `#555`(8×)/`#565656`/`#646464`/`#6a6a6a`/`#7b7b7b`/`#898989`. **Skin-owned.**
- **Text/foreground** — 20+ near-grey shades `#777`→`#f0f6fc`; collapse to bright/muted/faint tiers. **Skin-owned.**
- **Fancy/accent TEAL** — `#1a4653 #153a45 #1e515f #123138` (skin-owned) + leaking runtime `--secondary` bright teals (see §4). **This is the whack-a-mole source.**
- **Status/semantic** — gold `#ffd700`, blues `#0180ff/#028dff/#03a9f4`, greens `#31ac5f` (skin) / `#3fb950 #238636 #58a6ff` (GitHub-palette **overrides**), reds/danger, GitHub alpha `rgb()` set (**overrides**), named `red/greenyellow/orangered/cyan`.
- **Syntax highlighting** — VS Code / GitHub token palette lines 1899-1906 + `#d16969 #ce9178 #d7ba7d #deb887 #bdb884 #daeb60 #569cd6` etc. Mostly **overrides** of core's hardcoded snippet colors; self-contained, could stay literal but benefit from a `--drk-syntax-*` set.
- **Misc/light-mode** — `custom.css:127 white`, `widgets/table.css:7 #e2e2e2` — **out of scope** (not dark).

---

## 3. Proposed CSS-variable scheme

Declared in the existing `:root { … }` block in `darkmode.css` (top of file). Keeps `--drk-` prefix. All values below are *proposed* consolidations — near-duplicates collapse to one var.

### 3a. New ACCENT / TEAL family (the priority — kills wing inconsistency)
```css
/* Fancy-layout accent (muted teal) — header, breadcrumb, tab bars & wings */
--drk-accent-bar:      #153a45; /* bar/background behind tabs + breadcrumb + master/secondary-content containers */
--drk-accent-raised:   #1a4653; /* active tab title + its wings + header band (the "raised on the bar" tone) */
--drk-accent-active:   #1e515f; /* current breadcrumb segment / strongest accent */
--drk-accent-recessed: #123138; /* inactive tab title (pushed into the bar) */
```
Mapping (all in `darkmode.css` unless noted):
| Current literal | file:line | ⇒ Var |
|---|---|---|
| `#153a45` | 513, 529, 548 | `--drk-accent-bar` |
| `#1a4653` | 509, 538, 552 | `--drk-accent-raised` |
| `#1e515f` | 520 | `--drk-accent-active` |
| `#123138` | 543 | `--drk-accent-recessed` |

### 3b. Extend base backgrounds (fold in one-offs)
```css
--drk-bg-color-raised:   #34495e; /* slate raised surface (was #34495e ×5, #395169) */
--drk-bg-color-hover:    #202833; /* row/item hover (was #202833 ×2, cluster ~#1a2938/#192634) */
--drk-bg-color-recessed: #0e253c; /* deep navy panels (folds #0c121a #081934 #0d1520 #121922 #10161e) */
```
- `#34495e`(826,827,938,939,982) + `#395169`(854) ⇒ `--drk-bg-color-raised` (`#395169` is a hover of it; could add `--drk-bg-color-raised-hover: #395169`).
- `#202833`(484,619) + `#1a2938`(825) `#192634`(861) `#15232c`(1696) `#1d242e`(145) `#13171d`(119) ⇒ collapse toward `--drk-bg-color-c` / `--drk-bg-color-hover` (test each; some are content-tab backgrounds that may want to stay slightly distinct).
- `#0e253c`(850,987) `#0c121a`(868) `#081934`(1568,1572) `#0d1520`(1757) `#121922`(1770) `#10161e`(1558) ⇒ `--drk-bg-color-recessed` (or reuse `--drk-bg-color-inset`).
- Stray literals of existing vars (§1a table): replace with `var(--drk-bg-color-a/-inset/-btn/-b)`.

### 3c. New border/text tiers
```css
--drk-border-color-strong: #555;     /* was #555 ×8, #565656, #646464 */
--drk-border-color-btn:    #6a6a6a;  /* raised-button hairline (was #6a6a6a, #7b7b7b, #898989) */
--drk-color-muted:  #8b949e;         /* secondary text (folds #a0a0a0 #9a9a9a #999 #8a8a8a #8b8b8b #b2bfca #8194a5) */
--drk-color-faint:  #777;            /* tertiary text (folds #707070 #646464 as text) */
--drk-color-bright: #f0f6fc;         /* emphasis text (folds #e6ebed #e0dfdf #d6d6d6 #d1d1d1 #c8c8c8 #c5c5c5 #c3c3c3 #bbb) — verify per shade */
```
(The `#d1d1d1 #c5c5c5 #c8c8c8 #bbb …` tier is a judgment call: they may map to `--drk-color` (#c9d1d9) rather than bright. Recommend: map the `#c*`/`#d*` greys → `--drk-color`, the `#8*/#9*` greys → `--drk-color-muted`, the `#7*` → `--drk-color-faint`.)

### 3d. Semantic (optional but recommended — stops per-selector drift)
```css
--drk-status-active:  #ffd700; /* gold active/hover (filter scope ×5) */
--drk-status-info:    #0180ff; /* active control-table (folds #028dff) */
--drk-status-success: #31ac5f;
--drk-color-danger-strong: #9f0700;
--drk-accent-delete:       #a5382c; /* fancy delete pill (fancy-layout.css) */
--drk-accent-delete-hover: #bc4436;
```
GitHub alpha `rgb()` set + `#3fb950 #238636 #58a6ff` are self-consistent GitHub-palette overrides — optional `--drk-gh-*` vars; lower priority.

### 3e. Syntax highlighting (optional grouping)
```css
--drk-syntax-variable: #ff7b52; --drk-syntax-control: #c586c0;
--drk-syntax-string:   #98c379; --drk-syntax-number:  #569cd6;
--drk-syntax-html:     #dcdcaa; --drk-syntax-bool:    #f97583;
--drk-syntax-comment:  #7d8f5a; --drk-syntax-error:   #d16969;
```

### Collapse recommendations (highest value first)
1. **Teal → 4 accent vars** (removes the wing inconsistency; §4).
2. **~20 grey text shades → 3-4 vars** (biggest raw-count win).
3. **~12 one-off dark bgs → 3 vars** (`raised`/`hover`/`recessed`).
4. **`#555` ×8 + border greys → 2 border vars.**
5. **Stray literals of existing vars → the existing var** (§1a).

---

## 4. Tab-wing roadmap (every wing/tab-accent selector)

Wing = `span.title::before` / `::after` (skewed slivers). "Title" = the pill background. For consistency, **wing color must equal its title's background**. Current state:

### MASTER-TABS (Builder/CMS document "browser" tabs) — `darkmode.css`
| Selector | Lines | Current | Target var |
|---|---|---|---|
| container bar `div.tabs-container` | 533-536 → 529 | `#153a45` | `--drk-accent-bar` |
| active title + `:before` + `:after` (wings) | 532-539 → 538 | `#1a4653` | `--drk-accent-raised` |
| inactive title `li:not(.active) … span.title` | 550-553 → 543 | `#123138` (title only — **wings NOT set on inactive**, so inactive wings show the bar) | `--drk-accent-recessed` (title); leave wings = bar |
| welcome tab (Builder) title + wings | 2035-2041 → `var(--drk-bg-color-a)` | matches doc bg | keep (special-case) |

### PRIMARY-TABS (fancy header) — `darkmode.css`
| Selector | Lines | Current | Target |
|---|---|---|---|
| header band `.form-tabless-fields, .control-tabs.primary-tabs > … ul.nav-tabs` | 507-510 | `#1a4653` | `--drk-accent-raised` |
| active-tab wings `.control-tabs.primary-tabs > … li.active … span.title:before/:after` | 1720-1722 | `var(--drk-bg-color-a)` | **INCONSISTENT** — wings use page-bg, not the accent; decide: match title (accent) vs. blend into content (bg-a). Document intent. |

### PRIMARY-TABS.MASTER-AREA (Builder editor tabs) — `fancy-layout.css` (unlayered)
| Selector | Lines | Current | Target |
|---|---|---|---|
| active-tab wings `::before/::after` (light) | 276-286 | `#fafafa` | keep light (var `--tui-tab-active` optional) |
| active-tab wings `::before/::after` (dark) | 295-297 | `#21262d` (= `--drk-bg-color-btn`) | `var(--drk-bg-color-btn)` — literal today |

### BREADCRUMB (fancy) — `darkmode.css`
| Selector | Lines | Current | Target |
|---|---|---|---|
| bar `.control-breadcrumb` | 511-514 | `#153a45` | `--drk-accent-bar` |
| current segment `li:last-child, li.active` | 517-521 | `#1e515f` | `--drk-accent-active` |

### SECONDARY-CONTENT-TABS — `darkmode.css`
| Selector | Lines | Current | Target |
|---|---|---|---|
| bar `… secondary-content-tabs … ul.nav-tabs` | 545-548 | `#153a45` | `--drk-accent-bar` |
| active title `… li.active a > span.title` | 550-552 | `#1a4653` | `--drk-accent-raised` |
| **wings for secondary-content active tab: NOT overridden** | — | leaks bright `--secondary` | **ADD** `::before/::after` rule = `--drk-accent-raised` |

### CONTENT-TABS / SECONDARY-TABS (non-content) — `darkmode.css` + `fancy-layout.css`
| Selector | Lines | Current | Notes |
|---|---|---|---|
| content-tabs `ul.nav-tabs:before` rail | 1712-1713 | `#565656` | border var |
| `span.title > span` top-border | 1715-1717 | `2px solid #555` | border var |
| secondary-tabs (fancy-layout, layered) active `bg-white text-secondary` | fancy-layout 236-247 | Tailwind `bg-white`/`bg-secondary` (runtime teal) | **light/runtime path** — the bright-teal source; dark override lives in darkmode.css:494 |

**Leak diagnosis:** wherever a tab bar/title/wing is styled with Tailwind `bg-secondary` (fancy-layout.css) and **no `.dark` override exists in darkmode.css**, the runtime bright teal (`#2da7c7` family) shows in dark mode. Confirmed leak spots: (a) secondary-content-tabs active-tab **wings** (title overridden 552, wings not), (b) any secondary-tabs variant not caught by darkmode.css:494. Fix = add matching `.dark … span.title:before/:after { background: var(--drk-accent-raised) }` rules for every tab type whose title we recolor.

---

## 5. Implementation plan (ordered, low-risk)

All target rules are under `.dark`/`[data-color-scheme="dark"]` (except the light-mode `#fafafa` wing in fancy-layout and the two non-dark files) ⇒ **light mode is unaffected** by every replacement except: fancy-layout.css:285 `#fafafa` (light) — leave as-is or var it separately; custom.css:127 & widgets/table.css:7 — **out of scope**.

**Step 1 — Declare vars.** Add §3a accent vars first (4 lines) to the `:root` in `darkmode.css`. Then §3b/§3c. Optionally §3d/§3e. (~15-25 new var lines.) *Verify:* build succeeds, nothing visually changes yet.

**Step 2 — Teal/wings (darkmode.css lines 507-553, 1720-1722, 2035-2041; fancy-layout.css 295-297).** Replace 4 teal literals with accent vars; **add** the missing secondary-content-tabs wing rule; convert fancy-layout dark wing `#21262d`→`var(--drk-bg-color-btn)`. This is the payoff step — resolves wing inconsistency. ~12-14 replacements + 1 new rule. *Verify in browser dark mode:* every tab type's active wings match its title; no bright-teal leaks.

**Step 3 — Stray literals of existing vars (§1a).** Replace `#0d1117 #010409 #21262d #c9d1d9` literals with their vars. ~13 replacements. Zero visual change expected.

**Step 4 — One-off dark bgs → §3b vars** (darkmode.css). ~14 replacements. Verify per selector (some content-tab bgs are intentionally distinct — screenshot before/after).

**Step 5 — Borders → §3c** (`#555`×8, `#565656`, `#6a6a6a`, `#7b7b7b`, `#898989`, `#646464`). ~13 replacements.

**Step 6 — Text greys → §3c tiers.** ~25 replacements; highest judgment — do in small batches, screenshot.

**Step 7 — Semantic/syntax (§3d/§3e), optional.** ~30 replacements; self-contained, low visual risk.

**Estimated total replacements:** ~120 literal→var substitutions across `darkmode.css` (+ ~3 in `fancy-layout.css`), plus ~1 new wing rule. New `:root` var declarations: ~15-25.

**Per-file light-mode confidence:**
- `darkmode.css` — 100% under `.dark`; safe. ✅
- `fancy-layout.css` — mixed: lines 183-297 include light (`#fafafa` 285, `#a5382c/#bc4436` delete pill — always-on) and dark (295-297). Only convert the dark rule to vars; treat 285/183/192 as owned always-on colors (var them under a non-`--drk-` name if desired). ⚠ verify light mode.
- `widgets/table.css`, `custom.css` — light-mode; **exclude from dark refactor.** ✅
