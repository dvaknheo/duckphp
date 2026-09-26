# DuckPHP User Guide

> This guide is organized in four tiers: **Getting Started → Single Application → Using Third-Party Apps → Advanced Topics**.
> Every chapter follows the pattern "a working minimal example first, then the mechanism"; the examples come from assets that really exist in the repository: `demo/` (a multi-entry sample app), `skeleton/` (the scaffold skeleton) and `tests/data_for_tests/ZThirdDemo` (the sample project for volume 3).
>
> To look up "what methods/options does a class have", check [the reference manual](../reference/index.md); this guide only covers **how to do things**, and links to the corresponding reference pages.
>
> To see the full class-relationship map of the whole repo (inheritance / implementation / use trait / who wires up whom / who replaced whom): [`docs/duckphp.gv.svg`](../../duckphp.gv.svg) (generated from `src/`; see the script table in [the reference manual maintenance guide](../reference-maintenance-guide.md) §5 for how to refresh it).
>
> **All 47 chapters + 4 appendices are written** (Chapters 1-1–1-7, 2-1–2-20, 3-1–3-7, 4-1–4-13); when adding a chapter, follow the convention "unfinished chapters stay as **plain text + `⏳`** with no link attached" (swap in the link once written), so link checking always stays at zero broken links.

## How to use this guide

- Completely new: read volume 1 in order through the end of volume 2, and you will be able to finish a business application on your own.
- Already experienced with Laravel / Yii2 / CodeIgniter / ThinkPHP: read the migration cheat sheet in Appendix C first, then jump around volume 2 by chapter title.
- Want to mount an existing external app or legacy project: go straight to volume 3, the most distinctive volume of this framework.
- Just want to ask "can this feature be done": browse the troubleshooting index in Appendix D, or check the reference manual.

---

## Volume 1 · Getting Started

| Chapter | Title                                | In one sentence                                   |
| --- | --------------------------------- | ------------------------------------- |
| 1-1 | [What DuckPHP Is](intro.md)           | Design trade-offs and the mental model: runnable as a single file, phase isolation, four layers + Helper      |
| 1-2 | [Installation and Minimal Example](install.md)             | Two installation routes, 3 files to run your first page                   |
| 1-3 | [Directory Structure and the Four Layers](project-structure.md) | Directory conventions, naming rules, the five layers' responsibilities and the violation matrix, violation examples             |
| 1-4 | [The First Page](quickstart.md)            | Walk through routing → controller → business → model → view once          |
| 1-5 | [Options and Settings](configuration.md)         | The difference between `options` and `settings`, environment separation       |
| 1-6 | [First Contact with Debugging, Logging and the CLI](debugging.md)    | Error pages, `is_debug`, log levels, `php bin/cli.php` |
| 1-7 | [The Minimal Go-Live Checklist](deployment.md)           | Document root, nginx/apache, directory permissions, production options and the checklist       |

## Volume 2 · Single Application

> Covers the common topics of mainstream PHP frameworks (layering rules / request lifecycle / route hooks / routing / controllers / views / data / models / Helpers / validation / sessions / exceptions / events / cache / i18n / CLI / testing / security), with the correspondences to Yii2, CodeIgniter, Laravel and ThinkPHP. Middleware is not a core capability of this framework; it is only covered as a **compatibility extension** in [Chapter 2-4](route-hooks.md).

| Chapter    | Title                                 | In one sentence                                           |
| ---- | ---------------------------------- | --------------------------------------------- |
| 2-1  | [The Four Layers and the Calling Rules](layers.md)             | **Signpost page**: the layer rules have been merged into Chapter 1-3; only a cheat sheet stays here                    |
| 2-2  | [Request Lifecycle](lifecycle.md)             | The timeline of one request + which built-in components the framework loads by default                        |
| 2-3  | [Routing in Depth](routing.md)                 | Default rules, parameters, URL generation, rewrites and route maps                        |
| 2-4  | [Route Hooks](route-hooks.md)             | The six positions, short-circuiting interception, where the built-in hooks attach, the positioning of middleware                      |
| 2-5  | [Controllers](controllers.md)              | Reading input, the four output styles, redirects and 404                         |
| 2-6  | [Views and Templates](views.md)                  | Locating view files, layout header/footer, passing data and escaping                           |
| 2-7  | [Database](database.md)                 | Connection configuration, read/write splitting, transactions, pagination and SQL export                       |
| 2-8  | [The Model Layer](model.md)                    | The role of Model, its shortcuts and encapsulation rules                           |
| 2-9  | [Helpers and Global Functions](helper.md)          | The division of labor between the four layers' Helpers and the usage principles                            |
| 2-10 | [Forms and Validation](validator.md)            | The three validation conventions and error presentation                                   |
| 2-11 | [Session](session.md)                   | Session read/write, `session_prefix` isolation, what to put in the session         |
| 2-12 | [Exceptions and Error Handling](exception.md)            | Exception layering, conditional throws, error views and the reporter                             |
| 2-13 | [The Event System](events.md)                  | Event name conventions, dispatching and listening                                   |
| 2-14 | [Cache and Redis](cache.md)              | The cache component, multiple Redis instances and invalidation strategy                          |
| 2-15 | [Internationalization and Copy](i18n.md)                  | Multiple languages, the language handler and placeholder translations                                |
| 2-16 | [CLI and Scheduled Tasks](cli.md)                 | Built-in commands, custom commands, working with crontab                       |
| 2-17 | [Testing](testing.md)                   | Unit tests, coverage and the sample-data directory                               |
| 2-18 | [Security and Performance Checklist](security-performance.md) | Item-by-item self-check before going live                                       |
| 2-19 | [Using the User System](user.md)                  | Usage of `Helper::User*()`, handling the not-logged-in case and post-login views             |
| 2-20 | [Using the Admin System](admin.md)                | `Helper::Admin*()`, permissions and logs, the admin menu (PermissionMenu) |

## Volume 3 · Using Third-Party Apps

> This volume is the most distinctive one of DuckPHP: mount external apps in, share components, override their content.
> The examples come from `tests/data_for_tests/ZThirdDemo` (runnable, with a smoke test as the fallback).

| Chapter   | Title                                      | In one sentence                    |
| --- | --------------------------------------- | ---------------------- |
| 3-1 | [Application Tree and Phase Basics](advanced-phase.md)           | Running multiple apps in one process: phases, naming and switching    |
| 3-2 | [Mounting an External App](mount-app.md)                | Separate directory / namespace / prefix, multiple entry points sharing one codebase |
| 3-3 | [Static Resources and the Document Root](static-resources.md)         | Each app's own resource directory, conflicts and versioning      |
| 3-4 | [Component Sharing and Cross-App Communication](component-sharing.md)      | The shared container, cross-phase calls, the event bus, where shared data goes |
| 3-5 | [Overriding and Replacing](overriding.md)                  | The five override levels, priorities and troubleshooting "who wins"     |
| 3-6 | [The Installer and the Web Install Flow](installer.md)           | Install state, the install page and customization           |
| 3-7 | [Hands-On: Frontend + Admin + API](case-multi-app.md) | A complete landing of three apps in one             |

## Volume 4 · Advanced Topics

| Chapter    | Title                               | In one sentence                                        |
| ---- | -------------------------------- | ------------------------------------------ |
| 4-1  | [Container and Phase Internals](container-phases.md) | How instances are stored and fetched, how to troubleshoot when things go wrong                        |
| 4-2  | [Writing Components and Extensions](custom-component.md)   | Write your own components and extensions and wire them into the app                             |
| 4-3  | [Replacing Framework Behavior](replace-behavior.md)    | Replacing singletons, replacing system calls, overriding framework classes                          |
| 4-4  | [No Composer · Single File · Embedding](embed.md)    | Stuffing the framework into another project                                  |
| 4-5  | [Long-Running Processes and the Embedded HTTP Server](http-server.md)   | The built-in server and long-running operation                                 |
| 4-6  | [Multi-Entry · Multi-Domain · Multi-SAPI](multi-entry.md) | web / cli / rpc multiple entries on one codebase and subdirectory deployment                |
| 4-7  | [Test Infrastructure and the Coverage Pipeline](coverage.md)       | Coverage dumps, CI / WSL / containers                     |
| 4-8  | [Maintaining the Docs and the Reference Manual](doc-maintenance.md)  | How the reference manual stays in sync with the source                              |
| 4-9  | [Performance Tuning and Troubleshooting Manual](troubleshooting.md)  | Common symptoms → troubleshooting paths                                |
| 4-10 | [Design Trade-Offs and Known Pitfalls](design-notes.md)      | Which parts are deliberate, which are easy to step on                               |
| 4-11 | [Implementing the User System](impl-user.md)             | The three-piece implementation of session / login service / local service + options and mounting          |
| 4-12 | [Implementing the Admin System](impl-admin.md)            | Same as above (less registration, more `isSuper()`) plus the menu contract               |
| 4-13 | [The `Ext\*` Extension Classes](ext-classes.md)       | The optional components under `src/Ext/`: middleware, route tables, the admin menu, SQL export, view replacements… (including deprecated and obscure ones) |

---

## Appendices

| Appendix | Contents |
|---|---|
| [A Glossary](appendix-glossary.md) | App / child app / phase / mount prefix / shared container / override… unified across the whole book |
| [B Code Snippets](appendix-snippets.md) | CRUD, pagination, upload, login, permissions, cross-app calls |
| [C Migrating from Yii2 / CodeIgniter / Laravel / ThinkPHP](appendix-migration.md) | Concept mapping and side-by-side patterns |
| [D FAQ and Troubleshooting Index](appendix-faq.md) | Look up by symptom |

> **Global function reference**: see [DuckPhp\Core\Functions](../reference/Core-Functions.md); **application options reference**: see [the options cheat sheet](../reference/options.md), [options by class](../reference/options-by-class.md) and [options by index](../reference/options-index.md). (The two appendices that used to live on the guide side have been removed; their content was merged into the reference manual.)

---

> Maintaining / taking over this guide (writing template, hard constraints, verification commands, known pitfalls, chapter ordering and renumbering): [the user guide maintenance guide](../guide-maintenance-guide.md)
