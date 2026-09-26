# 1-2 Installation and Minimal Example

> Goal: get it installed, serve one page, and know why each file exists. About 12 minutes.
> This chapter's examples are taken from real files in the repo: the `skeleton/` scaffold, the `demo/` sample app, and the single-file example `demo/public/helloworld.php`.

## Requirements

| Item | Requirement |
|---|---|
| PHP | **>= 7.4** (the framework itself is compatible up to 8.4) |
| Extensions | `pdo` (when using a database), `redis` (when using Redis), others as needed |
| Composer | Recommended; the framework does not require it (see "Running without Composer" below) |
| Web root | Only the `public/` directory may be exposed (Chapter 1-7) |

## Route A: scaffold a project (recommended)

```bash
composer require dvaknheo/duckphp
php vendor/bin/duckphp new        # copies skeleton/ into your project (namespace: see below)
php vendor/bin/duckphp show       # runs the **framework's bundled demo/ app** for a look (default port 8080; not your project)
```

The `duckphp` command has only three subcommands: `new` / `show` / `help` (provided by [`DuckPhpInstaller`](../reference/Ext-DuckPhpInstaller.md)). What `new` does is plain: **it copies the whole `skeleton/` directory into your project**, so the generated structure matches `skeleton/`. The namespace **prefers the `autoload.psr-4` entry in your `composer.json` that points at `src`** (e.g. `"MySite\\": "src"` → `MySite`); only if none is found does it ask interactively (default `Demo`); you can also pass `--namespace=MySite` directly.

Two renames happen during generation — don't be surprised:

- `src/System/App.php` → `src/System/{last segment of the namespace}App.php` (class `App` → `MySiteApp`), and every place that mentions it (`public/index.php`, `bin/cli.php`, the bundled `AGENTS.md`) is rewritten along;
- all other files just replace `YourProjectName\` with your namespace.

The generated project contains an `AGENTS.md` (plus a 3-line `CLAUDE.md` pointer for Claude Code): that is **your own project's** convention list — file-level directory tree, naming suffix table, layering and layer-violation rules, the four steps for adding a feature, and common pitfalls. **For how to use the framework, or a method signature and option defaults, always consult the docs shipped with the package**: `vendor/dvaknheo/duckphp/docs/zh/guide/` (47 chapters) and `.../reference/` (one page per class). The division of labor in one sentence: conventions in `AGENTS.md`, API in the reference manual.

```bash
php vendor/bin/duckphp new --verbose          # prints every file written
php vendor/bin/duckphp new --force            # overwrites same-name files in the target directory (stops with a prompt by default)
```

## Route B: hand-write the minimal project (3 files + 1 optional view)

If you don't want the scaffold, create these three files yourself:

**① `public/index.php` — the Web entry point (just a few lines)**

```php
<?php declare(strict_types=1);
foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}
\MyProj\System\App::RunQuickly([
    // to temporarily override options, write them here, e.g. 'is_debug' => true
]);
```

> This is exactly how `skeleton/public/index.php` is written (the file in the scaffold, likewise backed by a test). "Looking for vendor in two places" supports both layouts: "framework installed inside the project" and "project inside the framework repo".

**② `src/System/App.php` — the application class (the configuration hub of the whole app)**

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',      // project root: view/ config/ runtime/ are all relative to it
        // set the next two **only when the view files actually exist**: if you set `error_404` without view/_sys/error_404.php,
        // a 404 turns into a 500 (the framework has to render the error page you named). Leave them empty to use the built-in placeholder pages.
        //'error_404' => '_sys/error_404',  // 404 view (relative to view/)
        //'error_500' => '_sys/error_500',
    ];
}
```

**③ `src/Controller/MainController.php` — the first controller**

```php
<?php declare(strict_types=1);
namespace MyProj\Controller;

class MainController
{
    public function index()
    {
        echo 'Hello DuckPHP';
    }
}
```


**④ `view/main/index.php` (optional)** — to use views, call `Helper::Show()`:

```php
<?php // view/main/index.php ?>
<h1><?= __h($title) ?></h1>
```

```php
// in the controller
Helper::Show(['title' => 'Hello DuckPHP'], 'main/index');
```

## Run it

```bash
# for development, use the built-in server (simplest)
php -S 127.0.0.1:8080 -t public

# or use the framework's bundled long-running HTTP server (Chapter 4-5), also a built-in server
php bin/cli.php run                 # defaults to 127.0.0.1:8080, document root public/
php bin/cli.php run --port=9000     # change the port
```

> Both startup styles hand only paths that "don't look like files" to `index.php`: `/` and `/Note/index` work; **URLs with an extension** (like the framework-served `/res/main.css`) need an extra router script — see [Chapter 1-7 §2](deployment.md).

Open `http://127.0.0.1:8080/` in a browser; seeing `Hello DuckPHP` means success. The scaffold also ships a sample route that **strings all four layers together**, accessible right after generation:

| URL | Path taken |
|---|---|
| `/` | `MainController::index()` → `Business\DemoBusiness::_()->foo()` → `Model\DemoModel` → `view/main.php` |
| `/test/done` | `Controller\testController::done()` (likewise through business/model) → routed to `view/test/done.php` |
| Anything else | `view/_sys/error_404.php` (the `error_404` option) |

The CLI entry `bin/cli.php` shares the same codebase as the Web entry:

```php
<?php declare(strict_types=1);
foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../vendor/autoload.php'] as $file) {
    if (file_exists($file)) { require $file; break; }
}
\MyProj\System\App::RunQuickly(['cli_enable' => true]);
```

```bash
php bin/cli.php help        # lists all available commands (Chapter 2-16)
php bin/cli.php version
```

## Running without Composer

The framework ships a minimal autoloader; the repo entry `bin/duckphp` does exactly this:

```php
require __DIR__ . '/src/Core/AutoLoader.php';
spl_autoload_register([\DuckPhp\Core\AutoLoader::class, 'DuckPhpSystemAutoLoader']);
```

To do this in your own project, just add one more mapping for your project namespace (Chapter 4-4).

## Directory overview (details in Chapter 1-3)

```
project/
├── public/index.php      ← 唯一对外暴露的入口
├── bin/cli.php           ← 命令行入口
├── config/               ← 设置文件（敏感信息放这里）
├── src/{System,Controller,Business,Model}/
├── view/                 ← 视图（含 _sys/ 错误页）
├── runtime/              ← 日志等可写目录
└── vendor/
```

## Common errors

| Symptom                                        | Cause                                        | Fix                                                                                        |
| --------------------------------------------- | ------------------------------------------ | ----------------------------------------------------------------------------------------- |
| Visiting `/` gives a 404                      | The controller method is named `action_index()` while the prefix is empty            | Drop `action_` from the method name; or explicitly set `'controller_method_prefix' => 'action_'`                            |
| Whole site 500, "class not found"             | Namespace and directory don't match                                 | `MyProj\Controller\MainController` must live in `src/Controller/MainController.php`, and `path` must point at the project root |
| `Failed opening required vendor/autoload.php` | The relative path in the entry file is wrong                                  | Copy the "two vendor locations" pattern from the entry file above                                                                     |
| Under `php -S`, everything but the homepage is 404 | Forgot `-t public`, or visited paths beyond `/index.php/foo` | Use `-t public`; short paths are taken over by the framework (Chapter 1-7 covers server configuration)                                                     |
| CLI runs into the Web branch                  | The entry didn't pass `cli_enable`, and `cli_enable` is off      | Pass `['cli_enable' => true]` in the CLI entry                                                          |
| In a hand-written project, any nonexistent URL gives **500** instead of 404 | `error_404` is set but `view/_sys/error_404.php` doesn't exist | Add the error page view (copy `skeleton/view/_sys/`), or remove the option — the framework then uses a built-in placeholder page |

## Next steps

- [Chapter 1-3 Directory Structure and the Four Layers](project-structure.md): lay out the project "the way the framework expects", and learn which layer may not call which.
- [Chapter 1-4 Your First Page](quickstart.md): walk the full chain routing → controller → business → model → view once.
- The reference manual: [DuckPhp\DuckPhp](../reference/DuckPhp.md), [DuckPhp\Ext\DuckPhpInstaller](../reference/Ext-DuckPhpInstaller.md)
