# Dark-mode token tooling

Measures what `assets/src/css/darkmode.css` actually does in a real browser, so dark-mode changes can be proven instead of eyeballed. This is what drove the move onto core's `--wn-*` tokens: 86 declarations were shown to be redundant once the token block existed, and every visual change the block introduced was listed and reviewed.

It runs through the Playwright MCP server (`browser_run_code_unsafe` / `browser_evaluate`) against a logged-in local backend with TailwindUI enabled and the user's appearance set to dark.

## How it works

1. `tag.mjs` stamps every rule inside the `.dark { … }` block with a `--dm-id` custom property and writes `rules.json` (each rule's source declarations). Build with the tagged source, so every compiled CSSOM rule maps back to its source rule.
2. `harness.js` runs in the page. `capture()` records every element's and pseudo-element's colours, border colours/widths/styles, outline, shadows, fill/stroke and opacity, with transitions disabled. On top of that:
   - `probe()` gives every `--wn-*` token a unique probe colour with the dark-mode declarations removed, which shows exactly which element-properties each token paints and what dark mode currently paints them.
   - `prune(tokenCss)` applies optional extra CSS (a trial token block), removes every colour-affecting declaration, restores those that explain a difference, then re-tests each restored one alone. What is still removed changed nothing on that page.
3. `run.js` visits the pages in `pages.js`, runs the chosen phase (`localStorage['dm:phase']`) and stores results in `localStorage`; dump them with `browser_evaluate` and its `filename` option.
4. `analyse.py` combines prune results: a declaration is removable when it matched on at least one page and no page needed it. Declarations that matched nothing anywhere are kept, since their redundancy was never shown. It also groups the visual changes the injected CSS causes on its own, for review.
5. `apply.mjs` deletes the removable declarations from the untagged source using the same rule numbering, and drops rules left empty. Comments are left for manual review.
6. `verify.js` checks a real build: it captures each page, swaps the TailwindUI stylesheet for a saved copy of the previous build (`assets/dist/assets/_baseline.css`, delete it before committing) and captures again. Run it in dark mode to review intended changes, and in light mode where it must report zero.

`aggregate.py` summarises probe results per token; it is a starting point for choosing dark token values, not a substitute for choosing them.

## Traps

- **Overlapping shorthands.** A rule can set `border-top` and `border-color`, which share longhands. The harness re-applies a rule's remaining declarations in source order after every change, and `!important` ones last, because CSSOM `setProperty()` just overwrites where parsing would keep the important one.
- **State, not style.** A difference that appears as a mirrored pair (opacity 0 → 1 on one element and 1 → 0 on another, a tab turning active) is the page changing between captures, e.g. a tab still opening. Re-run that page with a longer settle before trusting it.
- **Vite empties `assets/dist/` on every build**, including `_baseline.css`; put it back after each compile.
- Edit `D` and `origin` at the top of `run.js`/`verify.js` for your checkout; `tag.mjs`/`apply.mjs`/`make-data.py` expect to run from the Winter root (or `WINTER_ROOT`).

## Typical run

```bash
# from the Winter root
node <tools>/dark-tokens/tag.mjs tag plugins/winter/tailwindui/assets/src/css/darkmode.css <tools>/dark-tokens/rules.json
python3 <tools>/dark-tokens/make-data.py <tools>/dark-tokens/rules.json
php artisan vite:compile Winter.TailwindUI
# in the browser: localStorage.setItem('dm:phase', 'prune'), run run.js, dump the dm:prune:* keys to prune-dump.json
python3 <tools>/dark-tokens/analyse.py prune-dump.json <tools>/dark-tokens/rules.json
node <tools>/dark-tokens/tag.mjs untag plugins/winter/tailwindui/assets/src/css/darkmode.css
```
