# 1-1 What DuckPHP Is

> This chapter gives you a mental model: after reading it you will know how it differs from Laravel / Yii2 / CodeIgniter / ThinkPHP, and whether it fits your project.
> About 8 minutes. Then go straight to [Chapter 1-2 Installation and Minimal Example](install.md).

## In one sentence

DuckPHP is a PHP framework built on a **small kernel + convention-based layering + phase isolation**: the kernel only does skeleton work — "boot, routing, request dispatch" — and everything else (database, cache, i18n, user system…) is an optional component; it does not force any ORM or template engine on you, and controllers are plain PHP.

## Three core concepts

Remember these three sentences; every later chapter expands on them:

**① One class is one singleton**

All framework classes and project classes are fetched through `ClassName::_()`, managed uniformly by the "instance container":

```php
UserModel::_()->getUser(1);      // ✅
$m = new UserModel();            // ❌ bypasses the container (phase, overriding, and test injection all break)
```

**② A phase is an instance space**

One process can run multiple apps, each with its own phase; the same class name is a **different instance** under different phases (Chapter 3-1). The root app's phase is `''`, a child app's is `:<name>`.

**③ Controller → Business → Model, one-way calls only**

Layering is an enforced convention (Chapter 2-1 has the "callable / forbidden" matrix): controllers take input and produce output, the business layer holds the logic, the model layer only touches data. Views use global functions only.

## What it looks like (running in 20 lines)

One file is one app — this pattern really exists in the repo (`demo/public/helloworld.php`, backed by a test: `ZAllDemoTest` requests it and compares the output):

```php
<?php declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

// single-file example: the controller lives in this file too, so set the "controller namespace" to root
class MainController
{
    public function index()
    {
        echo 'hello world';
    }
}

\DuckPhp\DuckPhp::RunQuickly([
    'is_debug' => true,
    'namespace_controller' => '\\',
]);
```

A normal project is "one entry point + several class files": `public/index.php` is only 3 lines (Chapter 1-2).

## Compared to mainstream frameworks

|         | Laravel                     | Yii2         | CodeIgniter 4       | ThinkPHP                                                                       | DuckPHP                                                                                         |
| ------- | --------------------------- | ------------ | ------------------- | ------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------- |
| Positioning | Full-stack bundle       | Full-stack bundle | Lightweight MVC | Full-stack bundle (Chinese, with the most Chinese-language material)                                | Small kernel + optional components                                                              |
| Learning curve | High (container / facades / Eloquent / Blade all to learn) | High | Medium | Medium (most complete docs and tutorials, but you learn facades + helper functions + template tags) | Low (just the three concepts)                                              |
| Dependencies | Heavily tied to the Composer ecosystem | Considerable | Medium | Heavily tied to Composer (the `topthink/*` family) | No forced dependencies; **a single file runs too**                                             |
| Database | Eloquent ORM                | ActiveRecord | Query Builder/Model | Built-in ORM (`think\Model` + query builder `Db::name()`)                                    | Plain PDO ([`Db`](../reference/Db-Db.md)/[`DbManager`](../reference/Component-DbManager.md), no ORM) |
| Templates | Blade                      | PHP/widgets      | PHP                 | Built-in Think template engine (`{$var}`/`{volist}` tags, can be turned off for plain PHP)                            | Plain PHP views + global functions (swappable for [JsonView](../reference/Ext-JsonView.md)/EmptyView etc.) |
| Multi-app / multi-tenant | DIY              | Module mechanism | DIY               | Official "multi-app mode" (`think-multi-app`, apps split by first URL segment; the container is global, **no phase-level instance isolation**) | **Built in** (phases + child apps; all of Volume 3 is about this)                                |
| Overriding third-party code | Service container bindings / extension package override | Mostly configuration | Mostly configuration | Mostly configuration + container bindings (`app/provider.php`)                 | Five built-in levels of overriding (Chapter 3-5)                                                 |
| Best for | Mid-to-large team projects  | Mid-to-large projects | Small-to-mid projects | Small-to-large projects in China (want ORM/templates/multi-app out of the box)              | Small-to-mid projects; scenarios needing multi-app / multi-tenant / mounting legacy projects    |

**What it does not do** (being honest): no bundled ORM, migration tool, queue, template engine, or task scheduler; those are solved with PDO/plain PHP or by wiring up a third-party library yourself (the framework won't stop you, because it doesn't force a container or conventions on you).

## When it fits / when it doesn't

**It fits when**:
- you want "small, straightforward, readable" code and dislike black-box magic;
- you need **multiple apps in one process** (frontend + admin + API + third-party admin) sharing database/events;
- you have an existing DuckPHP app / legacy project to mount into a new one without changing its code;
- you want to hand AI or a new colleague a project whose call chain is readable at a glance.

**It doesn't fit when**:
- your team is already heavily invested in the Laravel ecosystem (Eloquent / Blade / Horizon…);
- you need out-of-the-box ORM auto-modeling, migrations, queues, broadcasting, etc.;
- you chase "syntactic sugar density" rather than explicit calls.

## What ships with the framework

| Category      | Contents                                                                                                                           |
| ----------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| Kernel        | App boot and lifecycle, routing (including rewrite/mapping/compat modes), request dispatch, exception handling, phase container     |
| Common components | Database (multiple databases / read-write splitting), Redis, cache, logging, i18n, config, validator, pager, session           |
| User system   | [`GlobalUser`](../reference/GlobalUser-GlobalUser.md) / [`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md) (login, registration, session, permissions, admin menus) |
| Optional extensions (`Ext`) | JSON view, JsonRpc, middleware, permission menus, installer, SQL export, embedded HTTP server…                                                    |
| Helpers       | Four-layer `Helper` + a few global functions (`__h()` / `__url()` / `__res()` / `__l()`)                                            |
|             |                                                                                                                                    |

For which classes exist and what methods each has, see [the reference manual](../reference/index.md); this guide only covers how to use them.

## How to read this guide

| Your situation    | Recommended path                                                                            |
| ------------- | -------------------------------------------------------------------------------- |
| Brand new to it       | Chapters 1-1–1-7 (Getting started) → Chapters 2-1–2-20 (Single application)                                            |
| Know other frameworks | [Chapter 1-3](project-structure.md) to learn the conventions → skim Chapters 2-1–2-20, focusing on "how this differs from Laravel/Yii2/ThinkPHP" |
| Mounting external apps/legacy projects | Chapters 3-1–3-7 (Volume 3), the most distinctive volume of this framework                                                   |
| Just checking "can it do X" | Start with [the reference manual](../reference/index.md); for troubleshooting see [Chapter 4-9 Performance Tuning and Troubleshooting](troubleshooting.md)      |

## Next steps

- [Chapter 1-2 Installation and Minimal Example](install.md): get the snippet above running.
- If terms are unfamiliar, check [Appendix A Glossary](appendix-glossary.md) (app / child app / phase / overriding… consistent across the whole book).
