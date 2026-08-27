# `scaffold:*` demo-data commands — handoff

_Pick-up doc for finishing the demo-data scaffolding commands across the Winter
first-party plugins. Written to continue on another machine._

## What these are

A dev-only console command per plugin, `scaffold:{author}.{plugin}`, that seeds
enough varied, representative data across that plugin's models/content to
exercise **every backend surface** locally (lists, filters, row-state styling,
forms/tabs, tree widgets, relation pickers, charts, dashboard widgets, empty
states, long-text truncation, pagination, etc.). Built to support the dark-mode
audit and general "install it and click around" local testing.

### Shared conventions (every command follows this)
- **Signature:** `scaffold:{author}.{plugin} {--fresh}`.
- **Env-guarded:** refuses to run when `app()->environment('production')` (returns
  `FAILURE` before touching anything). This is checked **first** in `handle()`.
- **Idempotent:** a second run detects existing scaffold data (via a recognisable
  marker) and no-ops with a "already exists" warning.
- **`--fresh`:** deletes previously-scaffolded data (scoped by the marker) and
  recreates it. Never touches non-scaffold rows.
- **Marker-scoped cleanup** (how `--fresh`/idempotency finds its own rows):
  - Blog — category `code` / post `slug` prefixed `scaffold-`
  - User — user `email` domain `@scaffold.example`, group `code` `scaffold-`
  - Redirect — redirect `description` prefix `[scaffold]`, category name `Scaffold —`
  - Translate — deterministic managed locale codes + message codes
  - Blocks / Pages — theme-file name prefix `scaffold-` (`scaffold-blocks*` for Blocks)
- Prints backend URLs to the seeded surfaces on completion.
- Registered in the plugin's `Plugin.php` via `registerConsoleCommand(...)`.

## Status — DONE (pushed, awaiting review; no PRs opened yet)

All six were committed to a **`wip/scaffold-command`** branch in their own repo,
branched off `main`/`develop`, and pushed to `origin`. Each commit contains only
the scaffold work (command + `Plugin.php` registration + test); no unrelated
changes were bundled.

| Plugin | Repo | Branch @ SHA | Test | Seeds (verified) |
|--------|------|--------------|------|------------------|
| Winter.Blog | wn-blog-plugin | `wip/scaffold-command` @ `82ed362` | **full** (4) | 8 categories (nested), 29 posts |
| Winter.User | wn-user-plugin | `wip/scaffold-command` @ `703a927` | **full** (4) | 4 groups, 30 users (all states) |
| Winter.Redirect | wn-redirect-plugin | `wip/scaffold-command` @ `495e387` | guard (2) | 3 categories, 34 redirects, 516 client hits, 27 logs |
| Winter.Translate | wn-translate-plugin | `wip/scaffold-command` @ `79eb275` | **full** (4) | 5 locales, 72 messages |
| Winter.Blocks | wn-blocks-plugin | `wip/scaffold-command` @ `28d3f93` | guard (2) | 1 layout, 17 pages (every block type) |
| Winter.Pages | wn-pages-plugin | `wip/scaffold-command` @ `d2f8001` | guard (2) | 6 pages (nested), 1 menu, 2 content, 1 snippet |

Plus, already committed earlier (not part of this batch):
- **LukeTowers.EasyForms** — `scaffold:luketowers.easyforms` on branch
  `wip/bugfixes-and-ci`, with a **full** feature test. This is the reference
  implementation the others were modelled on.

**Winter.Mall** was intentionally left alone — it already ships a native
`SeedDemoData` command + full seeder suite (`classes/seeders/*`).

### Test coverage — "full" vs "guard"
- **full (4 tests):** create-data / idempotent-without-`--fresh` /
  `--fresh`-recreates / refuses-in-production. Asserts exact seeded row counts.
  Used where the plugin's models migrate cleanly under the isolated test harness
  (Blog, User, Translate; EasyForms).
- **guard (2 tests):** command-is-registered / refuses-in-production. Used for the
  three plugins whose full seed can't be reliably asserted in the isolated
  harness (see gotchas). For these the **full seed was cross-checked by running
  the real command against the dev install** (winter.test) — counts in the table
  above are from those live runs. Upgrading these to full suites is optional
  follow-up (see "Next steps").

## How to verify

Run a plugin's test suite:
```
php artisan winter:test -p Winter.Blog -- --filter ScaffoldCommandTest
```
Cross-check the real seed against the dev DB/theme (idempotent + `--fresh`):
```
php artisan scaffold:winter.redirect --fresh
```
(dev env reports `development`, so the production guard doesn't fire.)

## Harness gotchas discovered (read before adding more / debugging tests)

1. **`winter:test` + symlinked plugins + `bootstrap` attr.** If a plugin's
   `phpunit.xml` hard-codes `bootstrap="../../../modules/system/tests/bootstrap/app.php"`,
   `winter:test` resolves it against the plugin's **real** path (these plugins are
   symlinked into `plugins/winter/*`), so it fails with "Unable to find the
   bootstrap file". Fixes: **omit** the `bootstrap` attribute (EasyForms/Blog do
   this — `winter:test` injects the right one), or run phpunit directly with
   `--bootstrap modules/system/tests/bootstrap/app.php` overriding it. Winter.Pages'
   pre-existing `phpunit.xml` has this attr; its test was verified via the override
   and left untouched (not our file to change).
2. **`protected $refreshPlugins` is dead.** Nothing in the current
   `System\Tests\Bootstrap\PluginTestCase` consumes it. setUp runs
   `Artisan::call('winter:up')` (all migrations) then auto-instantiates the guessed
   plugin (which runs `plugin:refresh` for it).
3. **Sibling plugins that extend your models.** In this multi-plugin install,
   Winter.Translate makes Blog's `Post` translatable, so deleting a post (via
   `--fresh`) hits `winter_translate_attributes`, which isn't migrated in an
   isolated Blog test. Fix used in Blog's test: `$this->instantiatePlugin('Winter.Translate', false)`
   in `setUp` — migrates the sibling if present, **no-op (throw=false) in a bare CI**
   where it's absent. Reusable pattern for any cross-plugin model extension.
4. **Winter.Redirect (Vdlp heritage) does not migrate cleanly in isolation.** Its
   `plugin:refresh` skips the base `create_tables` migration and its rollback drops
   tables, leaving a partial schema (e.g. `vdlp_redirect_categories` missing,
   duplicate-index collisions on re-migrate). Its model table names are also
   config-dependent (resolved by `Winter\Redirect\ServiceProvider`). This is a
   pre-existing plugin-migration robustness issue, orthogonal to the scaffolder —
   hence the guard-only test + live cross-check. Fixing it is its own task.
5. **Theme-file plugins (Blocks, Pages) write to the active theme on disk.** A
   full test would mutate `themes/demo` (not rolled back like DB rows), so those
   got guard-only tests; the seed is cross-checked live. Their production guard is
   reached before any theme access, so the guard test needs no theme.

## Push / auth notes

- Local **SSH is broken** (`ssh-add -l` → no identities; agent refuses to sign).
  Push over HTTPS instead — `gh` is authed with `repo` scope and set as the git
  credential helper (`gh auth setup-git`).
- Repos with an `https://` origin (Blog, Blocks) push directly. Repos with a
  `git@github.com:` origin (User, Pages, Redirect, Translate) were pushed with a
  one-shot rewrite (no persistent remote change):
  ```
  git -c url."https://github.com/".insteadOf="git@github.com:" push -u origin wip/scaffold-command
  ```

## Next steps

1. **Open PRs** for the six `wip/scaffold-command` branches (none opened yet).
2. **Remaining first-party plugins without a scaffolder** (present in this install):
   `winter/builder`, `winter/excel`, `winter/location`, `winter/sso`,
   `winter/tailwindui`. Judgement call per plugin:
   - **location** — has real models (countries/states already seeded by migrations);
     a scaffolder could add sample address/location records. Good candidate.
   - **sso** — could seed sample provider configs / linked-account rows. Candidate.
   - **builder** — a dev tool (builds plugins); low value, maybe skip.
   - **excel** — import/export helper with no models of its own; N/A.
   - **tailwindui** — the backend skin, no models; N/A.
   Follow the shared conventions above and model on EasyForms/Blog (full DB test)
   or Pages/Blocks (theme-file, guard test + live cross-check).
3. **Optional:** upgrade the three guard-only tests (Redirect/Blocks/Pages) to full
   suites once the underlying harness issues (gotchas 4 & 5) are addressed.
