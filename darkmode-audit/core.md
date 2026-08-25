# Dark-mode audit — Core backend (shared widgets & sections)

**Layout:** side menu + inline icons · **Scheme:** dark
These surfaces are shared by every plugin, so fixes here cascade widely. `darkmode.css` already has sections for List/Form/Tabs/Modals/Dashboard/CMS/media/eventlogs — the audit finds the gaps.

## Findings

| # | Severity | Surface | Issue | Maps to `darkmode.css` |
|---|----------|---------|-------|------------------------|
| **X1** | **Moderate** (pervasive) | Every toolbar | `btn-default` renders light-grey (`rgb(156,163,175)`) + black text — flash-bang vs dark primary buttons. Seen on Media (Move/Delete/view-toggles), Administrators (Manage Roles/Groups), Blog (Export/Import/Reorder), Import (Show ignored/Auto match). **Same root cause as Blog B2 — highest-leverage single fix.** | shared button section |
| C1 | **Moderate** | Media manager → preview pane | Empty-state shows a **light checkerboard** transparency pattern + white "Nothing is selected" box → flash-bang on the right third of the screen. | `/* backend/media */` |

## Verified clean in dark mode

- **Settings index** — grouped categories (icons + descriptions), search box: clean.
- **Administrators list** — columns/sort, filter bar (Super User / Last login / Role / Show deleted), **Settings context-sidenav** (left), pagination: clean (apart from X1).
- **Media manager** — DISPLAY sidebar, Order/Direction dropdowns, file list rows: clean (apart from X1 + C1).

## Still to capture (core)

Administrator **create/edit form** (permissions editor grid) · **Manage Roles/Groups** · **Event log** list + detail · **Mail** templates/settings · **CMS/Theme editor** · **backend list** row-hover/zebra + **relation manager popup** + **repeater/nested form** + **validation callout** (will surface via plugin scaffolds too).

> Note: X1 == Blog B2 — dedupe when consolidating. It is the single highest-impact dark-mode fix (touches every plugin).
