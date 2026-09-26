# Dark-mode audit — Winter.Blog

**Layout:** side menu + inline icons · **Scheme:** dark (`data-color-scheme=dark`)
**Scaffolder:** `php artisan scaffold:winter.blog --fresh` — nested category tree (3 levels + a very long name) and 29 posts (Markdown showcase, very-long title, draft, scheduled/upcoming, many-categories, + filler for pagination), featured images attached.
**Method:** each surface screenshotted in dark mode; suspect elements confirmed with computed colours.

## Findings

| # | Severity | Surface | Issue | Evidence | Maps to `darkmode.css` |
|---|----------|---------|-------|----------|------------------------|
| B1 | **Major** | Post form → Markdown editor | The editor **toolbar** (formatting icons) renders on a near-white band — a flash-bang against the dark editor/preview. | toolbar `background: rgb(242,242,242)` | `/* texteditors */` (extend for `.control-markdowneditor .editor-toolbar`) |
| B2 | **Moderate** (pervasive) | Every list/action toolbar | `btn-default` buttons render light-grey with black text — flash-bang vs the dark primary button. Seen on **Export/Import** (posts), **Reorder Categories**, and **Show ignored columns / Auto match columns** (import). Affects every plugin's toolbars. | `background: rgb(156,163,175)` (gray-400), `color: #000` | shared button section (highest leverage fix) |
| B3 | Minor | Post form → Markdown **preview** | Rendered **table borders** are light (gray-200) → harsh/high-contrast grid lines in dark mode. | th/td `border-color: rgb(229,231,235)` | `/* texteditors */` / markdown table rules |
| B4 | Minor | Any translatable field (Title, Slug, Excerpt, preview) | The floating **"EN" locale badge** (Winter.Translate) is white/light → small flash-bang on dark fields. | badge white bg | `/* Winter.Translate */` (new section) |

## Verified clean in dark mode (no action)

- Posts **list** — rows, long-title wrap, many-categories wrap, header/sort-column highlight, filter row, checkboxes, pagination.
- Post form **fancy header** (teal), Save/Cancel actions, **primary tabs** (Edit/Categories/Manage), Delete button.
- Markdown **preview** body — headings, emphasis, links, **code block** (`bg rgba(0,0,0,.5)`, light text — good), blockquote, image.
- **Manage tab** — Published checkbox, native **date/time pickers**, **dropdowns** (author/preview page), **excerpt** textarea, and **featured-image upload tiles** (thumbnail + filename + size all darkened correctly).

## Additional surfaces captured (all clean in dark mode)

- **Categories nested tree** — expand/collapse icons, indentation, nested rows all render correctly.
- **Category filter dropdown** (open) — dark popover, search box, `+`-to-add option rows: clean.
- **Import page** — dropzone, format dropdown, first-row checkbox, file-columns / database-fields matching UI (required-field red asterisks): clean apart from B2 buttons.

## Not captured (low-signal / shared elsewhere)

Export page (mirrors Import) · relation-picker popup (Categories tab) · Blog dashboard report widget (needs adding to a dashboard) · date-range filter popover · validation-error callout (shared core surface — will catch in the core-widgets pass) · Blog Settings (standard settings form).

**Blog audit status: complete** — 4 findings (B1–B4), all mapping to shared/core `darkmode.css` sections; the rest verified clean.

## Notes

- Fixes are centralized in `tailwindui/assets/src/css/darkmode.css` (scoped to `.dark` / `[data-color-scheme=dark]`), so B1–B4 are edits there, each needing a light-mode regression glance.
- B1/B3 also benefit every other plugin that uses the Markdown editor; B2 benefits every list toolbar → good candidates for the shared "core widgets" batch rather than a Blog-only fix.
