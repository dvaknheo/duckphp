# AGENTS.md — DuckPHP **framework development** (this repo) — NOT for apps that merely use DuckPHP

> **SCOPE — read this first.** This directory **is the DuckPHP framework source**. Everything in this file is about **developing and maintaining the framework itself**: it is written for the framework's own maintainers and for coding agents that change `src/`, `tests/`, `docs/` or `demo/` **here**. It says **nothing** about how to build an application with DuckPHP.
>
> **If you are here to *use* DuckPHP (not to change it), this is the wrong file — stop and take the proper route:**
> 1. in *your own* project: `composer require dvaknheo/duckphp`, then `./vendor/bin/duckphp new` to scaffold it (or copy `skeleton/` by hand);
> 2. the scaffolded project carries its **own** `AGENTS.md` (generated from [`skeleton/AGENTS.md`](skeleton/AGENTS.md)) — **that** is your project's convention sheet (layering, naming, directory tree, the four steps of adding a feature);
> 3. how to use the framework: [`docs/zh/guide/index.md`](docs/zh/guide/index.md) (52 chapters, English mirror under `docs/en/guide/`); per-class API and option defaults: [`docs/zh/reference/index.md`](docs/zh/reference/index.md). In an installed project they sit under `vendor/dvaknheo/duckphp/docs/`.
> 4. if you opened this file from `vendor/dvaknheo/duckphp/AGENTS.md` inside a user project: close it and read that project's own `AGENTS.md` instead. The rules below (framework-wide `src/` ASCII, 100% coverage gates, the Chinese/English docs trees, the `ZAllDemoTest` baselines, …) do **not** apply to a project that merely depends on DuckPHP.
>
> Audience for the rest of this file: AI agents / engineers working **in this repository** (the framework itself, not a user project). **Read this file first**; jump to the two maintenance guides when you need the long version.
> Language note: this file is English; the two maintenance guides and the documentation tree are Chinese (the project's working language) — that is intentional, not an oversight.

## 0. What this is

| Item | Value |
|---|---|
| Project | DuckPhp (formerly DNMVCS): zero-dependency, fully replaceable components, **library-style** PHP framework |
| Composer package | `dvaknheo/duckphp` |
| PHP | `>= 7.4.0` (**do not use 8.x syntax**; the measured runtime is PHP 8.2) |
| Framework source | `src/` (pure ASCII, see §3) |
| Tests | `tests/` (PHPUnit 9.6; `phpunit.xml` enables `processIsolation`) |
| Docs | `docs/zh/` (**source of truth**) + `docs/en/` (parallel tree, identical file names) |
| Demo / skeleton | `demo/` (living documentation *and* the E2E test host), `skeleton/` (the template that generates user projects) |
| CLI / containers | `bin/duckphp`, `docker/test-php74`, `docker/test-php84` |

## 1. Environment prerequisites (where things go wrong; verified 2026-10-02)

**Always run from WSL** (a Windows-side PHP has no redis extension, so the redis test cases fail spuriously). The repository is `E:\ProjectGoat\DNMVCS` on Windows and `/mnt/e/ProjectGoat/DNMVCS` in WSL; every command below runs from the repository root.

1. **`vendor/` must match `composer.json`.** This repository **does not commit `composer.lock`** (a library does not pin its dependencies), so the local dependencies come from `composer update`. Correct versions: `phpunit 9.6.37`, `php-code-coverage 9.2.32`, `dvaknheo/libcoverage 1.0.8`, `nikic/php-parser 5.x`.
   When versions are out of step you see these, and they mean:

   | Error | What is missing / wrong |
   |---|---|
   | `Call to undefined method LibCoverage\LibCoverage::_()` | libcoverage is too old (< 1.0.8) |
   | `Class "PHP_Token_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG" not found` | `php-token-stream` is too old; `covagg.php` and `tests/support.php` then read no dump at all |
   | `Class "PhpParser\ParserFactory" not found` | `nikic/php-parser` is missing (needed by `gen-architecture-gv.php`) |
   | `Class "Redis" not found` / `RedisException: Connection refused` | see the next point |

2. **The full test run also needs redis**: `apt-get install -y php8.2-redis redis-server`, then
   `redis-server --requirepass 123456 --save "" --appendonly no --daemonize yes`
   — the port and password match the `redis_list` in `tests/data_for_tests/setting.php` (the `docker/test-php74` compose file starts redis the same way). Without either one, `RedisCacheTest` / `RedisManagerTest` / `DuckPhpTest` / `Ext/RouteHookWebInstallerTest` turn red.

3. **Run the full suite as `XDEBUG_MODE=coverage php vendor/bin/phpunit`**: `phpunit.xml` uses process isolation, and `LibCoverage` inside the child processes needs xdebug's coverage mode; this machine's `xdebug.mode` defaults to `develop`, so **do not** run the full suite with `--no-coverage` (a batch of cases then fail in the children with `Code coverage needs to be enabled`).

4. `vendor/`, `composer.lock`, `test_reports/`, `test_coveragedumps/` and `backup/` are in `.gitignore` — **never commit them**.

## 2. Layout

```
src/           the framework itself (Core / Component / Ext / Foundation / Db / Helper / GlobalAdmin / GlobalUser / HttpServer + the entry classes)
tests/         PHPUnit cases + fixtures under tests/data_for_tests/; tests/bootstrap.php boots LibCoverage
demo/          sample application: many entry points + i18n (config/lang-en.php, lang-zh_CN.php) + the host of the ZAllDemoTest E2E run
skeleton/      the user-project skeleton (contains skeleton/AGENTS.md, which ships to users)
docs/zh|en/    Chinese (source of truth) and English parallel tree: guide/ 52 chapters, reference/ 112 pages
docs/scripts/  documentation generators and gate scripts (§5)
docker/        the php74 / php84 full-test containers
bin/duckphp    the installer CLI (`./vendor/bin/duckphp new`)
```

## 3. Code conventions

- **PHP >= 7.4**: no 8.x syntax (`match`, enums, constructor property promotion, `?->`, …).
- **Keep `src/` pure ASCII**: comments and strings in English. Before committing run `bash docs/scripts/check-non-ascii.sh` and expect `Total non-ASCII lines: 0`.
- **One-way layering**: `System → Controller → Business → Model`. Components live in `src/Component/` (replaceable); extensions in `src/Ext/` (**not wired automatically** — they must be declared in `ext`). The full convention sheet (aimed at user projects) is `skeleton/AGENTS.md`.
- **Coverage is a hard metric**: `src/` must stay at 100% (currently `4737/4737`). Whenever you change or add code, add assertions in `tests/` that **really execute those lines**; check that the test was green *before* your change (red/green proof), and do not settle for "the suite passes".
- After touching `src/`: `php -l <file>` → `check-non-ascii.sh` → run the affected cases → if class relationships or names changed, `php docs/scripts/gen-architecture-gv.php --check`.

## 4. Tests and coverage

| Goal | Command |
|---|---|
| Full run (authoritative) | `XDEBUG_MODE=coverage php vendor/bin/phpunit` (about 6 minutes) |
| One file | `XDEBUG_MODE=coverage php vendor/bin/phpunit tests/Core/RouteTest.php` |
| Cases that do not touch coverage | `php vendor/bin/phpunit --no-coverage tests/ZAllDemoTest.php` |
| Aggregate coverage verdict | `php docs/scripts/covagg.php` → `TOTAL 80 files, 86 dumps, lines …` and `0 files with gaps` |
| HTML report | `XDEBUG_MODE=coverage php vendor/bin/phpunit tests/support.php` → `test_reports/index.html` |

**Current baseline (measured in WSL on 2026-10-02)**: `OK (95 tests, 889 assertions)`; coverage `80 files, 86 dumps, 4737/4737 (100.00%)`.
The numbers move as cases are added — **§8 of `docs/zh/reference-maintenance-guide.md` is the authoritative baseline**, and it also records the environment prerequisites for a full run.

**`ZAllDemoTest` compares byte lengths**: it starts the built-in server and `curl`s every route, comparing the **output length** of each one. Changing visible wording in `demo/`, or changing wording/class names/paths that the `src/` dumps contain, turns it red. When it does:
- the expectations live in `tests/data_for_tests/ZAllDemoTest.config.php` (its comments record where each change came from); on failure the actual output is dumped to `tests/data_for_tests/ZAllDemoTest-<length>.txt`, so you can `diff` the old and the new dump;
- the test URLs all carry `?lang=en` (the demo is multilingual: English by default, `zh_CN` comes from `?lang=` / a cookie / `Accept-Language`) — **do not remove that pinning**, or the lengths start wobbling with the machine's `LANG` and headers;
- the expected length of the `files` section depends both on "how many options the root application declares" and on "the list of loaded files" (`demo/config/lang-*.php` ends up in that list too).

**How to read a red run**: first check whether it is the environment issue from §1 (the five redis/coverage-related cases), and only then suspect your own change; temporarily stashing `src/` and re-running is the quick way to tell "already red" from "I broke it" (`git stash push -- src/` → run → `git stash pop`; ⚠️ the workspace may also hold a stash left behind by another AI session, so check `git stash list` and make sure the top entry is yours before popping).

## 5. Documentation (change Chinese ⇒ change English in the same commit)

- **Chinese is the single source of truth** (`docs/zh/`); `docs/en/` is a **parallel tree with identical file names** (52/52 chapters, 112/112 pages). A change to Chinese must change English **in the same commit**; only the two maintenance guides are Chinese-only. The translation conventions live in [`docs/en/TRANSLATION.md`](docs/en/TRANSLATION.md).
- **Never hand-edit a generated page.** After changing `src/` or the options, re-run the generators:
  - `options.md` / `options-by-class.md` / `options-index.md` / `setting.md` / `index.md`: `php docs/scripts/gen-options-docs.php` (English: `--lang=en`);
  - the architecture diagram: `php docs/scripts/gen-architecture-gv.php` → `docs/duckphp.gv`, then re-render `docs/duckphp.gv.svg` (the rendering recipe is in §5 of the reference maintenance guide; this machine has no graphviz, so the WASM build is used).
- **Gates to run before committing** (all from the repository root; expected output in brackets):

  | Command | Expected |
  |---|---|
  | `python3 docs/scripts/check-doc-links.py docs/zh` | `broken: 0` |
  | `python3 docs/scripts/check-en-docs.py --all` | `165 file(s), 0 error(s)` (`--missing` leaves only the 2 maintenance guides) |
  | `php docs/scripts/gen-options-docs.php --check` (then again with `--lang=en --check`) | `up to date` |
  | `php docs/scripts/gen-architecture-gv.php --check` | `docs/duckphp.gv is up to date` |
  | `python3 docs/scripts/check-skeleton-tree.py` | prints `一致` (consistent) — mandatory after touching `skeleton/` |
  | `python3 docs/scripts/find-unmentioned-classes.py` | `从没被链到: 0` ("never linked: 0") |
  | `python3 docs/scripts/check-md-layout.py` | entries reported as `layout-only` can be dropped with `git checkout --`; only `CONTENT` needs a diff review |
  | `bash docs/scripts/check-non-ascii.sh` | `Total non-ASCII lines: 0` (after touching `src/`) |

- ⚠️ **Do not use `php docs/scripts/gen-reference.php verify --all` as a consistency verdict**: it expects method entries to start with a backtick, while this repository consistently uses four-space indentation, so it reports `method missing in md` for nearly every page (thousands of false positives) and also misses genuinely stale entries (see §10 of the maintenance guide).

## 6. Commits and workspace hygiene

- **`git add` explicit paths; never `git add .`** — that sweeps in test artifacts and the author's untracked files (we have been burned: one commit carried 52 `tests/**/runtime/*.log` files and had to be redone with `reset --soft`).
- Write commit messages **in Chinese** and say "what changed + why" (keep the history in the commit message, **not in the docs**). For breaking changes, baseline shifts or environment changes, include the verification commands and their results.
- **Never commit test artifacts**: `tests/data_for_tests/*/runtime/`, `tests/data_for_tests/ZAllDemoTest-*.txt`, `demo/runtime/`, `test_reports/`, `test_coveragedumps/`, `dump.rdb`.
- The workspace also contains untracked files left by the author or by other AI sessions (`github.ppk`, `docker/**/… - 副本*`, `docs/新建文本文档.txt`, `fix_iterable_types.php`, `path_of调整.txt`, …). Leave them alone unless your task is about them; ask the author first if you think they need to change.
- Check `git status --short` before committing, and finish with "no uncommitted tracked changes".

## 7. How to approach the usual tasks

| Task | Steps and where to look |
|---|---|
| Change a class | `src/` → the matching `docs/zh/reference/<module>-<class>.md` (+ the same file under `en/`) → cases → gates; the long form is §6 of the reference maintenance guide |
| Change / add a documentation chapter | §2 (template) and §6 (standard moves) of the guide maintenance guide; chapter numbers use the "volume-chapter" form, and the single source of truth for them is `docs/zh/guide/index.md` |
| Delete a class | Clean all four places at once: the `src/` file, the `tests/` case, the `docs/{zh,en}/reference/` page, and the paragraphs that talk about it in the guides (§8 of the reference maintenance guide) |
| Add / change an option | the `src/` `$options` → that class's reference page (its "Options" table) → `php docs/scripts/gen-options-docs.php` (zh + `--lang=en`) |
| Touch `demo/` | remember the `ZAllDemoTest` baseline (§4); visible wording goes through `__l()` + `demo/config/lang-*.php` (Chinese in `lang-zh_CN.php`), comments stay English |
| Touch the skeleton `skeleton/` | `python3 docs/scripts/check-skeleton-tree.py`; `skeleton/AGENTS.md` is the single authoritative list shipped to users, do not copy it elsewhere |
| Touch the architecture diagram / gate scripts | §5 (script table) and §9 (smoke checks) of the reference maintenance guide |

## 8. Authoritative documentation index

| You want to know | Go to |
|---|---|
| How to write, verify and renumber documentation | [`docs/zh/guide-maintenance-guide.md`](docs/zh/guide-maintenance-guide.md) |
| How to write a reference page, how the scripts work, what the baseline is | [`docs/zh/reference-maintenance-guide.md`](docs/zh/reference-maintenance-guide.md) |
| The Chinese→English conventions (glossary, layout, files allowed to keep Chinese) | [`docs/en/TRANSLATION.md`](docs/en/TRANSLATION.md) |
| Which classes and options exist | [`docs/zh/reference/index.md`](docs/zh/reference/index.md) (a generated page) |
| How to use the framework | [`docs/zh/guide/index.md`](docs/zh/guide/index.md) |
| Project introduction and contact details | [`README-zh.md`](README-zh.md) / [`README.md`](README.md) |
| The conventions for user projects | [`skeleton/AGENTS.md`](skeleton/AGENTS.md) |

## 9. Containers (only needed when changing PHP versions)

`docker/test-php74` (PHP 7.4) and `docker/test-php84` (PHP 8.4) are two line-aligned compose setups; each starts redis and runs the full suite. The existing local images (`test-php74_fulltest`, `test-php84_fulltest84`) still work, but **rebuilding fails** — the Dockerfile's `apt-get install redis-server` now hits 404s (the Debian bullseye-security repository is stale), and an in-container `composer update` can also time out on a GitHub clone. Day-to-day full runs should use WSL; to fix the containers, update the Dockerfile's apt sources or its base image first.
