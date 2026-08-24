# Dark-mode audit — findings (REVISED, honest triage)

> **FIX LOG:** ✅ **G6 (btn-default grey) — FIXED & verified.** Root cause was an intentional-but-bad `dark:text-black dark:bg-gray-400` in the skin's own `components/button.css` (+ `button-group.css` divider border + `custom.css` open-dropdown-toggle). Changed to `dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600` etc. Verified on Blog list toolbar — dark slate buttons, no flashbang. (Rebuild `app-C1ZtqJxI.css`.)
>
> ✅ **G21 (fieldset light bg) — FIXED & verified.** Added `.fieldset { background: var(--drk-bg-color-inset); border-color: var(--drk-border-color); color: var(--drk-color) }` to `darkmode.css` Form section (core widget). Computes dark now.
>
> ✅ **G3 (control-table readonly cells) — FIXED & verified.** Core `table.css` paints `.control-table table.data td .content-container.readonly` white `#f7f7f7` (spec 0,4,2); added a matching-specificity dark override (`var(--drk-bg-color-inset)`) in the control-table section. Translate messages source column now `rgb(1,4,9)`, legible.
>
> ✅ **G14 (list totals white bands) — FIXED & verified.** `tr.table-totals` (in thead + tfoot) rendered `#f2f2f2`; added `table.data tr.table-totals{,td,th}` dark override → both bands now `#161b22`. Verified `winter/test/people`.
>
> ✅ **G7 (editor toolbars white) — FIXED & verified.** Both editor bodies were already dark but their toolbars stayed light `#f2f2f2`. Darkened `.control-toolbar.editor-toolbar` (markdown) and `.fr-toolbar` (Froala) to `--drk-bg-color-c` with dark borders, and recoloured toolbar buttons/icons (text + svg fill) light with dark hover/active states + dark separators. Verified markdown toolbar on `blog/posts/create` (`#1b222c`) and Froala toolbar on a Winter.Pages static page (`#1b222c`, light icons).
>
> ✅ **G11 (media checkerboard + "nothing selected") — FIXED & verified.** The existing darkmode.css rule for `.sidebar-image-placeholder` literally re-applied the *light* checkerboard (`#efefef`/`#d2d2d2`) and a white `#fff` "Nothing is selected" band — it was never actually darkened. Swapped the gradient squares to `#0d1117`, base to `--drk-bg-color-c`, and the `p` band to `--drk-bg-color-b`. Verified `cms/media`.
>
> ✅ **form-preview panel (SSO logs preview et al) — FIXED & verified.** `.form-preview` used off-palette flat greys (`#343434` bg / `#626262` border); moved onto the palette (`--drk-bg-color-b` / `--drk-border-color`) so readonly record-preview forms match the theme. Verified `winter/sso/logs/preview/5` (fields already `#010409`, only the outer panel was off).
>
> ⏸️ **G8 (mail-branding preview) — NOT A BUG (working as intended).** `system/mailbrandsettings` renders a faithful WYSIWYG preview of the *actual email*, whose colours are user-configurable and shown live in the right-hand pickers (Body `#F5F8FA`, Content `#FFFFFF`, etc.). Emails are light by design; darkening the preview would misrepresent the real output. The surrounding settings chrome (pickers, section headers) is already dark. Optional future polish: frame the preview as a "canvas" so the white reads as intentional. No change made.
>
> ✅ **G2 (event-log preview panel) — FIXED & verified.** The `#winter-log-viewer` component ships a hardcoded light theme via an inline `<style>` in core's `modules/system/controllers/eventlogs/_field_details.php`. Added a dark override block in darkmode.css (`.dark #winter-log-viewer…` outranks core's id-only inline rules): panel `var(--drk-bg-color-a)`, detail table text/borders, trace frames `--drk-bg-color-b`, code snippets `--drk-bg-color-inset`, highlight line `--drk-bg-color-selection`, and re-coloured all 8 syntax-highlight classes for a dark bg. Verified `/eventlogs/preview/212` end-to-end (detail table, stack frames, code snippet). **Still to check: SSO logs preview `/winter/sso/logs/preview/4` — likely a separate record-preview partial (may be its own G2 instance / plugin-owned).**
>
> ✅ **G1 (tab active lozenge + content border) — FIXED & verified.** The active primary-tab set `--drk-bg-color-a:#555` (washed grey "broken lozenge"); swapped to `#21262d` (palette raised surface), and darkened the light `.tab-content` `border-top` (was gray-200) to `var(--drk-border-color)`. Verified `backend/users/update/1`: active title+wings `rgb(33,38,45)`, content border `rgb(48,54,61)`. NB: this is the *global* `.control-tabs.primary-tabs` rule, so it covers all primary tabs install-wide (supersedes the old `.master-area`-only scope). Secondary-tab wings still to re-verify.
>
> This supersedes the over-optimistic verdicts in `_summary.md`. The first pass caught obvious
> flash-bangs on happy-path list/form screens but **missed structural issues** on tabs, preview
> panels, tables, switches and embeds. This list folds in the user's reported issues + confirmed captures.

## Structural (the ones the first pass wrongly called "clean")

| ID | Sev | Issue | Confirmed on | Notes / fix direction |
|----|-----|-------|--------------|----------------------|
| **G1** | **High** | **Tab wings broken EVERYWHERE**, not just Builder | event-log FORMATTED/RAW tabs; `backend/backend/users/update/1`; Mall product form; settings pages — user says "everywhere" | The Builder-only `.master-area` fix was far too narrow. The `span.title::before/::after` wings render broken/absent for **all** `.control-tabs` (primary + secondary) in dark mode. Fix the wing rendering globally in dark, not per-context. |
| **G2** | **High** | **Record-preview panels render light/washed** → low contrast, "unusable" | `system/eventlogs/preview/212` (body + stack trace on light bg); `winter/sso/logs/preview/4` (reported) | The preview/detail partial layout (`.control-detail`/preview partials) isn't darkened. Likely other `/preview/` screens too. |
| **G3** | **High** | **Tables / lists "look like trash"** | reported broadly; `.control-table` readonly cells render **white** (Translate messages) | Re-audit lists critically (header/border/zebra/hover contrast + `.control-table` cell/readonly backgrounds). I under-called these. |
| **G4** | **Med-High** | **Switch form fields render wrong** | reported (Mall "Is virtual", etc.) | Switch track/handle/OFF-state colours need dark treatment; I wrongly called them fine. |
| **G5** | **Med** | **Weird border around RelationController / relation-manager embeds** | reported | Embedded relation widget border/background not darkened. |

## Flash-bangs / palette

| ID | Sev | Issue | Confirmed |
|----|-----|-------|-----------|
| **G6** | High-leverage | `btn-default` = light-grey + black text on every toolbar (was F1) | ✔ many |
| **G7** | Major | All editor toolbars white — markdown **and** richeditor (was F2) | ✔ Blog, Pages |
| **G8** | Major | **Mail branding preview = giant white email panel** mid-dark-UI | ✔ mail branding |
| **G9** | Moderate | **Theme picker**: light-grey preview placeholder + **over-bright indigo** "Create/Find theme" buttons | ✔ `cms/themes` |
| **G10** | Moderate | **Settings hint / section headers styled wrong** | reported: `settings/update/winter/translate/providers` (google tab) |
| **G11** | Moderate | Media preview checkerboard + white "nothing selected" (was F3) | ✔ media |
| **G12** | Minor | ML "EN" + currency "USD" badges white on dark fields (was F5) | ✔ Blog, Mall |
| **G13** | Minor | Markdown preview table borders gray-200 (was F4) | ✔ Blog |

## Round 2 (user-reported, more of them)

| ID | Sev | Issue | Confirmed |
|----|-----|-------|-----------|
| **G14** | **High** | **List totals header + footer render on white/light bands** (`.list-totals`/summary rows) | ✔ `winter/test/people` (both bands white) |
| **G15** | **High** | **Filter bar is a mess** — truncated dropdowns (`× Ac...`), inconsistent checkbox/number/date-filter styling, odd colours (yellow "Hide published") | ✔ `winter/test/people` |
| **G16** | Major | **Fancy-layout form header** needs a proper dark treatment (currently flat brand-teal regardless of scheme) | reported |
| **G17** | Moderate | **Delete button in CMS template editors** (pages/partials/layouts) styled wrong | reported |
| **G18** | Moderate | **Winter.Pages** list-of-pages + its **delete button** | reported |
| **G19** | High | **Tab wings on Winter.Pages menu editor** (another G1 instance — confirms install-wide) | reported |
| **G20** | Moderate | **Theme-editor component listing** (CMS editor components panel) | reported |
| (G7) | — | richeditor toolbar white — already tracked as G7 | reported |

## Capture characterizations (exact values for the fix)
- **G21 fieldset** (`winter/test/records/update/3#secondarytab-formwidgets`): `.fieldset { background: rgb(245,245,245); border-color: rgb(209,214,217); color: rgb(201,209,217) }` → light bg + light text = low-contrast. Fix: darken bg + border in `darkmode.css`.
- **G26 EasyForms builder** (`luketowers/easyforms/forms/update/N`): the field-palette cards **and the form canvas are white** — the builder is essentially un-dark-mode'd. Field inputs + fancy header are dark; everything else (palette, canvas, "Add field/Properties" toggle) is light. **Owner: LukeTowers.EasyForms** (its builder CSS gets dark-scoped rules). Big surface.

## Still-uncaptured (setup/data blockers — do during fix pass)
- **Winter.Builder** G23/G24/G25 (DataTable dropdowns / model form-builder / backend-menu editor): need a Builder-created plugin present (the test ones were deleted) — recreate a scratch plugin to capture.
- Core surfaces named but characterized-from-report (screenshot + probe when fixing): settings-page tabs (G1), SSO logs preview (G2), relation-manager embed border (G5), switch field close-up (G4), CMS template delete button (G17), Pages list + delete (G18), Pages menu-editor tabs (G19), theme-editor component list (G20), dashboard report-widget box-shadow (G22), settings hint headers (G10).

## Blocked on data (can't capture until seeded)
- **Mall Orders / Discounts / Payments** — all lists are **empty** (`mall:seed-demo` never populated products→orders). The list *chrome* is fine; the rich order-detail / discount-rule / payment-gateway screens need a working seed (or manual data) first. **Prereq: fix the Mall seed.**

## Round 3 (user-reported) + fix-ownership directive

**Directive:** dark-mode overrides for a **plugin's own styles** belong **in that plugin** (its backend CSS, scoped under `html[data-color-scheme=dark]` / `.dark`). **Only shared core backend styles** are fixed in TailwindUI `darkmode.css`. This distributes the work and keeps plugins self-contained.

| ID | Sev | Issue | Fix owner |
|----|-----|-------|-----------|
| **G21** | Moderate | **Fieldset** form widget styling | core → **TailwindUI** (`winter/test/records/update/3#secondarytab-formwidgets`) |
| **G22** | Moderate | **Dashboard/report-widget box-shadow** wrong in dark | `redirect/statistics` — if the shadow is Redirect's statistics CSS → **Winter.Redirect**; if core report-widget → TailwindUI (determine) |
| **G23** | High | **DataTable dropdowns** (Builder migration builder) | **Winter.Builder** (its DataTable/build CSS) |
| **G24** | High | **Model form-builder** UI | **Winter.Builder** |
| **G25** | High | **Backend-menu editor** UI | **Winter.Builder** |
| **G26** | High | **EasyForms visual form-builder** (entire thing) | **LukeTowers.EasyForms** |

### Fix ownership map
- **TailwindUI `darkmode.css` (core/shared):** G1 tabs, G2 preview panels, G3 `.control-table`, G4 switches, G5 relation embeds, G6 btn-default, G7 markdown/richeditor toolbars (core editors), G8 mail-branding, G9 theme picker, G10 hint headers, G11 media, G13 md-table borders, G14 list totals, G15 filters, G16 fancy header, G21 fieldset, event-log preview.
- **Winter.Builder:** G23 DataTable dropdowns, G24 model form-builder, G25 backend-menu editor (+ the earlier Builder tab-sides work already there).
- **LukeTowers.EasyForms:** G26 form-builder.
- **Winter.Translate:** G12 ML locale badge (the badge is MLControl's markup — Translate owns it).
- **Winter.Redirect:** G22 if it's the statistics page's own shadow; G17/G18 CMS/Pages delete buttons + G19/G20 are Pages/CMS-editor (Winter.Pages / core CMS module — determine per-element).
- **Winter.Mall:** currency badge + its orders/discounts/payments chrome (pending capture).

Each plugin already loads backend CSS (e.g. Builder `build.js`/`builder.css`, EasyForms builder assets, Blog `winter.blog-preview.css`); the dark rules get appended there, scoped to the dark selector — mirroring how TailwindUI scopes its own.

## Root-cause clusters (for the fix pass)
1. **Undarkened light backgrounds** (the "white band" family): list totals G14, mail-branding preview G8, preview/detail panels G2, `.control-table` cells G3, media checkerboard G11, theme placeholder G9.
2. **Tabs / wings** install-wide: G1, G19.
3. **Buttons**: `btn-default` grey G6, theme indigo buttons G9, CMS/Pages delete buttons G17/G18.
4. **Filters** G15.
5. **Editor toolbars** G7; **fancy header** G16; **hint headers** G10; **switches** G4; **relation embeds** G5; **component list** G20; **badges** G12; **md borders** G13.

## Genuinely OK (still believe these pass — but re-verify with a critical eye)
Chart.js statistics charts; Settings *index* (the category grid, not the tabbed sub-pages); nav/sidebar. Everything else gets re-scrutinised.

## Corrected approach for the fix phase
Priority order: **G1 tabs** and **G2 preview panels** and **G3 tables** first (structural, install-wide, most visible), then **G6/G7/G8** (flash-bangs), then the rest. Each fix in `darkmode.css` with a light-mode regression check. Re-screenshot each fixed surface — no more "looks fine from a glance."
