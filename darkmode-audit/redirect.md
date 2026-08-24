# Dark-mode audit — Winter.Redirect

**Scaffolder:** `scaffold:winter.redirect --fresh` — 34 redirects (all match/target types, 301/302/303/404/410, enabled/disabled/scheduled), 3 categories, **516 client hit records + 27 logs** so the charts render with real data. Authored + registered (uncommitted).

## Captured

- **Statistics page (charts)** — `winter/redirect/statistics`: **CLEAN / excellent in dark mode.** Chart.js "Redirect hits per day" bar chart (teal visitor + dark-teal crawler bars, readable axis labels, subtle gridlines, legend), the status-code **donut** (green/orange/yellow/blue/red segments), scoreboard (516 / 26 / 161 / latest request), and Redirects-per-month / Top-crawlers / Top-redirects panels. No flash-bang; charts are dark-aware. **The highest-risk surface (charts) passes.**

## Findings

_None yet on the captured surface._ Expect the shared **X1 grey `btn-default`** on the redirects-list toolbar + the "Actions" popup (to confirm next session).

## Remaining capture targets (next session)

Redirects list p1/p2 (colored switches, sparkline column, `special` scheduled row) + filter popovers + Actions popup · create form (balloon status-code selector, dependsOn target fields) · edit form tabs (General/**Requirements** repeater/Test/Scheduling/**Advanced** balloon selectors/**Logs** relation) · categories list/form · dashboard "Top 10 redirects" + "Create redirect" report widgets · TestLab.

**Status:** scaffolder ready; charts verified clean; lists/forms pending.
