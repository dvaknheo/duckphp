# 4-13 `Ext\*` extension classes

> What this solves: what the optional components under `src/Ext/` are, when they are worth using, and how to mount them; which are the recommended path, which are only compatibility layers, and which are already obsolete.
> Prerequisites: [Chapter 4-2 Developing components and extensions](custom-component.md), [Chapter 2-4 Route hooks](route-hooks.md). About 20 minutes.
> This chapter is the **overview** of the `Ext\` family: the chapters in volume 2 only cover "how to use it" (how to switch it on, a one-line example), while class names, `ext` assembly, options and pitfalls are all collected here.

## Minimal example

Components under `Ext\` **are not wired automatically**; to use one, write it into the application's `ext`:

```php
// src/System/App.php
public $options = [
    'ext' => [
        \DuckPhp\Ext\RouteLister::class => true,   // true = assemble it the default way
    ],
    'cmd' => [
        \DuckPhp\Ext\RouteLister::class => true,   // and register its CLI command along the way
    ],
];
```

```bash
php bin/cli.php routes        # once mounted, the route table can be listed (Chapter 2-16)
```

## How it works

### 1. What "not wired automatically" means

- **AutoLoader only loads class files on demand**, which is the same for every class;
- but **being assembled into the current phase** (putting an instance into the container, registering hooks/commands/events) requires a declaration in `ext` — the values `ext` accepts (`true` = **follow this level's application options**, an options array, `'option-key-name'`, `'@method-name'`, and the `EXT_*` forms) are covered in [Chapter 4-2](custom-component.md);
- every extension the framework **installs by default** is a `Component\*` (the `common_options['ext']` of `src/DuckPhp.php`: `Lang`, `RouteHookRewrite`, `RouteHookRouteMap`, `RouteHookResource`, `RouteHookPathInfoCompat`), and **not one `Ext\` class is among them**.

So "I wrote it but nothing happens" is almost always this one thing: the class exists and its file is loaded, but it was never written into `ext` (the same entry appears among the common errors of [Chapter 2-4](route-hooks.md)).

### 2. How this chapter classifies them

| Category | Which ones | When to look |
|---|---|---|
| **Common (must be mounted explicitly)** | `RouteHookManager`, `MyMiddlewareManager`, `RouteLister`, `PermissionMenu`, `SqlDumper`/`SqlDumperSupporter*`, `CallableView`/`EmptyView`/`JsonView`, the `RouteHook*` family, `DuckPhpInstaller` | When you need middleware, a route table, a back-office menu, SQL export, or another view implementation |
| **Compatibility layers (usable, but not the recommended path)** | `EventManager`, `ExceptionWrapper`, `MyFacadesBase`/`MyFacadesAutoLoader`, `ExtendableStaticCallTrait` | Only when you meet them in old code, or know exactly what you want |
| **Obsolete and obscure** | `HookChain`, `ThrowOnTrait`, `StaticReplacer` | So that you know which side you are standing on when you run into them in the reference manual |
| **Pages that are not for business code** | `RouteHookWebInstallerView`, `PermissionMenuMetaInterface`, `RouteHookDirectoryMode` | When changing the installer's look, the menu metadata schema, or directory-mode routing |

The only evidence for calling something "obsolete" is the marker in the source; run this once to see the current list:

```bash
grep -rn "@todo deprecate" src/     # 6 classes matched when this was written
```

⚠️ **"Not recommended" does not mean "will be deleted"**: the classes in the table above are all covered by tests (coverage is a hard metric of this repository, [Chapter 4-7](coverage.md)) and are not planned for removal in 1.x. The only ones really deleted are `Ext\MiniRoute` and `Ext\Misc` (the author judged that "nobody uses it / it is not needed", and source, tests and reference page went together) — so **do not write configuration based on class names that appeared in stale copies such as `docs/en/`**.

## Common extensions

### 3. `Ext\RouteHookManager`: naming hooks, attaching and detaching them

[`RouteHookManager`](../reference/Ext-RouteHookManager.md) is the "named management layer for hooks": `Core\Route` itself only has three linked lists plus `addRouteHook($callback, $position, $once)` ([Chapter 2-4](route-hooks.md)), and once there are many hooks, **who runs before whom and how to detach them** becomes awkward. This class registers hooks as "name → position", so you can:

```php
use DuckPhp\Ext\RouteHookManager;

RouteHookManager::_()->attachPreRun()->append(MyHook::class);   // append to the end of the pre chain
RouteHookManager::_()->moveBefore(NewHook::class, OldHook::class);
RouteHookManager::_()->removeAll('MyProj\Middleware\AuthHook'); // detach by name
echo RouteHookManager::_()->dump();                             // print the current hook table
```

- `attachPreRun()` / `attachPostRun()` choose the chain, and `append($name)` / `insertBefore()` / `moveBefore()` / `removeAll()` / `setHookList()` adjust it;
- it **is an Ext too**: either write it into `ext`, or call `RouteHookManager::_()` manually when needed (it `use`s the singleton trait itself);
- note that it manages **names and order**; the hooks themselves are still `Route`'s three linked lists — what `getHookList()` returns is the current table.

### 4. Onion middleware: `Ext\MyMiddlewareManager` (compatibility layer)

Let us be clear about its position: **middleware is not DuckPHP's main road**. The framework's default approach is "route hooks + layered Helpers"; middleware is only a compatibility layer for people used to the middleware style of Laravel / ThinkPHP (or PSR-15). Use it if it helps, ignore it otherwise.

How it is wired (the options go in the application's `$options`):

```php
$options = [
    'ext' => [\DuckPhp\Ext\MyMiddlewareManager::class => true],
    // from outside in: **the first in the list is the outermost**
    'middleware' => [
        \MyProj\Middleware\AuthMiddleware::class . '@handle',   // goes through the Class::_() singleton
        \MyProj\Middleware\LogMiddleware::class . '->handle',   // goes through new Class()
        'MyProj\Middleware\StaticMiddleware::handle',           // a native callable string
        function ($request, \Closure $next) { /* a callable works too */ },
    ],
];
```

The middleware signature is `handle($request, \Closure $next)`: `$request` is handed to you by the manager (an empty `\stdClass` by default, which a subclass can change for a request object of its own), and `$next($request)` means "continue inward"; the return value is the result of the inner layer.

Once mounted, it sits at the innermost layer of the built-in hooks (mounted with exactly the [`RouteHookManager::_()->attachPreRun()->append()`](../reference/Ext-RouteHookManager.md) of §3). The measured onion order is as follows (the X/Y/Z middleware in `tests/Ext/MyMiddlewareManagerTest.php`):

```
P1>  P2>  CTRL#1  P2<  P1<        ← the first in the list is outermost; the controller runs only once
```

**Short-circuiting (intercepting a request) works now**: if a middleware **does not call `$next` and returns a response directly**, the manager outputs that response and tells the router "the request has been handled" — the controller will not run again.

| How the middleware is written | Controller | What the user sees | `doHook()` |
|---|---|---|---|
| No `$next`, `return 'content'` | **does not run** | the `content` output by the manager | `true` |
| No `$next`, `return true` (you already `echo`/`Show302`ed the content yourself) | **does not run** | what you output | `true` |
| No `$next`, `return null` / `false` | runs as usual | the controller output (or 404) | `false` |
| Calls `$next` and returns its result | runs once | the controller output | `defaultResult` |

Three boundaries to remember:

- **A response is handled as a string only** (`echo`ed out); to hand it to another response object, override `outputResponse()`.
- **Once the inner layer has run, a middleware's return value takes no part in output**: the controller echoes as it goes, so by the time the chain returns the content is already out. To post-process a response, wrap `$next()` in your middleware with an output buffer yourself.
- `null`/`false` means "I did not handle it" — a cushion for "forgot to call `$next`" so that it never suddenly becomes a blank page.

Therefore:

| What you want to do | What to use |
|---|---|
| Symmetric handling around the request (timing, logs, a shared response header) | onion middleware (its strength) |
| Intercept a request (return/redirect as soon as authorization fails) | a middleware short-circuit (the first two rows above), or returning `true` in a route hook's `prepend-outter` ([Chapter 2-4](route-hooks.md)) |
| Finer control (swapping the request/response objects, your own teardown) | extend [`MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md) and override `getRequest()`/`getResponse()`/`runSelfMiddleware()`/`onPostMiddleware()`/`outputResponse()` |

### 5. `Ext\RouteLister`: listing the route table

[`RouteLister`](../reference/Ext-RouteLister.md) provides two things: `listAll()` scans out every route (while debugging, `print_r(\DuckPhp\Ext\RouteLister::_()->listAll())` is enough, [Chapter 1-6](debugging.md)), and the CLI command `command_routes()` (`php bin/cli.php routes`) — **this command is not among the framework's own seven**, so it must be registered:

```php
// src/System/App.php
public $options = [
    'cmd' => [
        \DuckPhp\Ext\RouteLister::class => true,   // true = use the default method prefix command_
    ],
];
```

Afterwards `php bin/cli.php routes --with_children=0 --only_admin=1` works (the arguments are on the reference page); the value of `cmd` may also be a prefix string (such as `'command_'`), and `false` or removing it switches it off. **`Ext\PermissionMenu` depends on it too** (§6): the menu is the routes it scans out.

### 6. `Ext\PermissionMenu`: back-office menu and permission tree

[`PermissionMenu`](../reference/Ext-PermissionMenu.md) uses `RouteLister` to scan out the routes of **back-office controllers** (classes implementing [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md); extending `AdminControllerBase` already satisfies it) and builds a menu/permission tree from them. Its three modes (how to annotate, how to persist) are in [Chapter 2-20 Using the admin system](admin.md) §5 — that is the **usage**; here are the things to know about it as an extension:

- the path of the menu file is given by the hidden option `permission_menu_tree_for_admin` (relative to `path_config`);
- `loadAll()` merges the menus of the root application and of each child application into one whole tree (**cross-phase safe**);
- the metadata mode requires controllers to implement [`PermissionMenuMetaInterface`](../reference/Ext-PermissionMenuMetaInterface.md), whose `__permissionMenuMeta()` returns the whole table directly;
- the persistence mode runs `buildAndSaveToConfigJsonFile()` once during deployment or from a scheduled task, and at runtime `loadAdminPermissionMenu()` reads it back, avoiding a route scan on every request.

### 7. `Ext\SqlDumper` + `SqlDumperSupporter*`: export schema/data as SQL

To export table schema or data as SQL (installers, backups), use [`Ext\SqlDumper`](../reference/Ext-SqlDumper.md); the driver details come from a [`SqlDumperSupporter*`](../reference/Ext-SqlDumperSupporter.md):

| Class | Supports |
|---|---|
| `Ext\SqlDumper` | the generic exporter (the table prefix is written as the `{prefix}` placeholder) |
| [`ByMysql`](../reference/Ext-SqlDumperSupporterByMysql.md) / [`BySqlite`](../reference/Ext-SqlDumperSupporterBySqlite.md) / [`ByPgsql`](../reference/Ext-SqlDumperSupporterByPgsql.md) | one dialect per driver; all three **are in the default mapping already** (`database_driver_SqlDumperSupporter_map` in `src/Ext/SqlDumperSupporter.php`, keyed by the part of the DSN before the `:`) |

To support another driver (or to swap out one dialect implementation), extend `SqlDumperSupporter`, implement the two methods, and override `database_driver_SqlDumperSupporter_map`. In the exported SQL, `{prefix}` stands for the table prefix, which the web installation flow ([Chapter 3-6](installer.md)) replaces with the real prefix when it runs.

### 8. Three ways to replace the view implementation: `CallableView` / `EmptyView` / `JsonView`

The framework's `View` is a singleton that another implementation can replace (an extension does `View::_(static::_())` in its own `init()`, and each carries its own `*_skip_replace` switch). **The view layer is the second public replacement point** (the first is the class replacement of [Chapter 4-3](replace-behavior.md)):

| Extension | What a view looks like | Key options |
|---|---|---|
| [`Ext\CallableView`](../reference/Ext-CallableView.md) | a **function/method**: `Views::main_view($data)` | `callable_view_class`, `callable_view_header/footer`, `callable_view_is_object_call`, `callable_view_prefix` |
| [`Ext\EmptyView`](../reference/Ext-EmptyView.md) | the view name is the string to output (placeholder/degradation) | `empty_view_key_view`, `empty_view_key_wellcome_class`, `empty_view_trim_view_wellcome` |
| [`Ext\JsonView`](../reference/Ext-JsonView.md) | output the data as JSON directly | `json_view_skip_vars` |

How to mount them and how "a view name maps to a callback" is on each reference page; [Chapter 2-6](views.md) keeps only one line, "the view implementation is replaceable, the three alternatives live here". The `viewToCallback()` of [`DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) is a minimal version of the `CallableView` idea ([Chapter 4-4](embed.md)).

### 9. The `RouteHook*` family: all of them must be mounted yourself

| Class | What it does | When to use |
|---|---|---|
| [`RouteHookFunctionRoute`](../reference/Ext-RouteHookFunctionRoute.md) | bind a "function/controller method" straight to a URL (functional routing) | small single-file applications, the style of `demo/public/traditional.php` ([Chapter 4-4](embed.md)) |
| [`RouteHookDirectoryMode`](../reference/Ext-RouteHookDirectoryMode.md) | directory mode (one entry point per directory) | directory-unfolding deployments |
| [`RouteHookApiServer`](../reference/Ext-RouteHookApiServer.md) | expose class methods as an API (`apiserver_*` options) | building a JSON API quickly ([Chapter 4-6](multi-entry.md)) |
| [`RouteHookWebInstaller`](../reference/Ext-RouteHookWebInstaller.md) | the web installation wizard (with `web_installer_*` options) | distributing and installing a third-party application ([Chapter 3-6](installer.md)) |

None of them **is wired automatically**: they take effect only when written into `ext` (or when another extension calls `addRouteHook()` internally).

### 10. `Ext\DuckPhpInstaller`: creating a new project

The implementation of `php vendor/bin/duckphp new|show|help` (source `src/Ext/DuckPhpInstaller.php`), which copies `skeleton/` into your project. It is an **installer CLI**, not an application's command set — for usage see [Chapter 1-2 Installation and minimal example](install.md).

## Compatibility layers (usable, but not on the recommended path)

### 11. `Ext\EventManager`

⚠️ **Do not use it in new code**. Its API resembles [`GlobalEvent`](../reference/Component-GlobalEvent.md) (`OnEvent/FireEvent/AllEvents/RemoveEvent`), but it **has no concept of phases** — a callback runs in whichever phase it called `fire`, so **cross-phase events (the multi-application/child-application scenarios of volume 3) cannot be handled**; the framework itself **references it nowhere**.

For events, use `GlobalEvent` ([Chapter 2-13](events.md)). If you really need "a purely in-process bus without phase switching", writing a few dozen lines of your own is more controllable than pulling this in. The reference page is kept: [DuckPhp\Ext\EventManager](../reference/Ext-EventManager.md).

### 12. `Ext\ExceptionWrapper`

⚠️ **There are no internal users left in the framework; do not use it in new code**. The two problems it solves both have more direct answers now:

- "one call must not blow up the whole flow" → use `Helper::XpCall($cb, ...$args)` (`_XpCall()` in `src/Core/CoreHelper.php`: `try { return ($cb)(...$args); } catch (\Exception $ex) { return $ex; }`, so the exception comes back as a return value);
- "I really do need to handle the exception" → branch with a plain `try/catch` instead of mixing exceptions into normal return values.

Old code already using it may keep doing so: [`ExceptionWrapper`](../reference/Ext-ExceptionWrapper.md) wraps an object, does try/catch inside `__call`, and hands the exception object back as a return value when a `\Exception` is thrown (it catches `\Exception` only, not `\Error`):

```php
$safe = ExceptionWrapper::Wrap($someClient);
$ret  = $safe->request('https://…');   // success → the result; exception → returns $ex
$obj  = ExceptionWrapper::Release();    // get the wrapped object back
```

### 13. `Ext\MyFacadesBase` + `Ext\MyFacadesAutoLoader`: facades produced by eval

[`MyFacadesBase`](../reference/Ext-MyFacadesBase.md) and [`MyFacadesAutoLoader`](../reference/Ext-MyFacadesAutoLoader.md) come as a pair: `MyFacadesAutoLoader` registers `spl_autoload_register()`, and for a class name matching the `facades_namespace` prefix (`MyFacades` by default) or listed in `facades_map` it **`eval`s** a line of `namespace X { class Y extends MyFacadesBase {} }` (lines 55-56 of `src/Ext/MyFacadesAutoLoader.php`); `MyFacadesBase::__callStatic()` then forwards the static call to the real class resolved by `getFacadesCallback()`, throwing `\ErrorException("BadCall")` when it cannot be resolved.

**Why it is obsolete**: what it solved was "static calls have no IDE completion" — and it **evaluated code at runtime** to get that completion. The same need is now met with pure annotations: the `@method static` tag. This repository does exactly that, with 96 `@method` entries each on [`Foundation\Helper`](../reference/Foundation-Helper.md) and [`DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) ([Chapter 2-9](helper.md)).

⚠️ Conversely: **`@method` is only an annotation for the IDE; `method_exists()`/reflection cannot see** those methods. For the set of callable methods, look at the dispatch order of `__callStatic()` ([Chapter 2-9](helper.md) has a measured list of the 96 methods).

### 14. `Ext\ExtendableStaticCallTrait`: registering static methods dynamically

Once a class `use`s [`ExtendableStaticCallTrait`](../reference/Ext-ExtendableStaticCallTrait.md) it gains `AssignExtendStaticMethod($key, $value)` / `GetExtendStaticMethodList()` / `__callStatic()`: the last one's flow is "look in the registry → take the callback → `call_user_func_array()`". A registered value may be an array, a `callable`, or one of two string shorthands — `Class@method` (through `Class::_()`) or `Class->method` (through `new Class()`).

**Why it is obsolete**: it is the low-end version of the facade mechanism above, again using `__callStatic()` to provide "methods reflection cannot see". Nowadays either write `__callStatic()` by hand (what [`Foundation\Helper`](../reference/Foundation-Helper.md) really does) or do not need it at all — real methods are enough. There is no usage site left in the framework's `src/`, only a test fixture still uses it (the aliased `use` at line 755 of `tests/Core/AppTest.php`).

## Obsolete and obscure

### 15. `Ext\HookChain`: a chain that stops on a hit

[`HookChain`](../reference/Ext-HookChain.md) is a small utility class: it packs a series of callbacks into an object, executes them in order on `__invoke()` and **breaks on the first truthy return value**, and it implements `ArrayAccess` so it can be read and written as an array.

```php
use DuckPhp\Ext\HookChain;

$chain = new HookChain();
$chain->add($callback1, true, true);    // (callback, append?, dedupe?)
$chain[] = $callback2;                  // ArrayAccess append
$chain();                               // run in order, break on a truthy value

HookChain::Hook($target, $callback3);   // convenience: merge an existing callback/null and a new callback into one chain, written back to $target
```

It **is not used inside the framework** (the framework's own hooks go through `Route`'s three linked lists); it is an optional tool "for when you want to express 'a set of hooks, stop on a hit' in your own code", and the source already carries `@todo deprecate`. To intercept a request, still use route hooks ([Chapter 2-4](route-hooks.md)).

### 16. `Ext\ThrowOnTrait`: a one-line trait

```php
trait ThrowOnTrait
{
    public static function ThrowOn($flag, $message, $code = 0)
    {
        if (!$flag) { return; }
        throw new static($message, $code);
    }
}
```

It gives an exception class a static "conditional throw" entrance. **There is no usage site inside the framework** (`grep -rn ThrowOnTrait src/` matches only the trait itself); conditional throws all go through the layered [`Helper::ThrowOn()`](../reference/Foundation-Business-BusinessHelper.md) (`ProjectThrowOn`/`BusinessThrowOn`/`ControllerThrowOn`, [Chapter 2-12](exception.md)) — the exception classes are decided centrally by option, and the whole family can be swapped in tests. If you want your own exception class with the same syntax, copying these two lines is clearer than `use`ing it.

### 17. `Ext\StaticReplacer`: moving global state into a component

All three methods of [`Ext\StaticReplacer`](../reference/Ext-StaticReplacer.md) **return by reference**, and the usage is close to the native structures (`$v = &$sr->_GLOBALS('counter'); $v++;`):

| Method | What it emulates |
|---|---|
| `_GLOBALS($k, $v)` | `$GLOBALS[$k]` |
| `_STATICS($name, $value, $parent)` | a function-scope `static` variable (the key is derived from the object hash in `debug_backtrace` plus the class and function name) |
| `_CLASS_STATICS($class, $var)` | a static property of some class (the real value is read by reflection once, after which a local copy is returned) |

**Why it is obsolete**: the source literally says `@todo deprecate` (line 12 of `src/Ext/StaticReplacer.php`), and the file still keeps `//TODO add Replace` (line 20). It was born in the era of "tests need to isolate global state", whereas this framework's answer has become the **phase container + `SingletonExTrait`** ([Chapter 4-1](container-phases.md)): to isolate state, switch phases (`App::Phase()`) or replace the instance with `Class::_(new Class())`; emulating `$GLOBALS` is not needed.

**Two known traps** (also written on the reference page; read them before you touch it): `_CLASS_STATICS()` returns a **copy**, so changing it does not write back to the real class static property; and `_STATICS()`'s slots are distinguished by **call site**, so the same name in two different functions is two different slots.

### 18. A few obscure pages that are "not for business code"

They are **not obsolete**, their purpose is simply fixed:

| Class/file | What it is | Covered in |
|---|---|---|
| [`Ext\RouteHookWebInstallerView`](../reference/Ext-RouteHookWebInstallerView.md) | the installer wizard's **built-in view template** (the file contains no class/function at all; it is `include`d by `RouteHookWebInstaller::show()`) | [Chapter 3-6](installer.md) |
| [`Ext\PermissionMenuMetaInterface`](../reference/Ext-PermissionMenuMetaInterface.md) | the contract for the menu's metadata mode (§6) | [Chapter 2-20](admin.md) |
| [`Ext\RouteHookDirectoryMode`](../reference/Ext-RouteHookDirectoryMode.md) | directory-mode routing (§9) | §9 of this chapter |

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| A component under `Ext\` is written but nothing happens at all | `Ext\` components are not wired automatically | Write it into the application's `ext` (§1, [Chapter 2-4](route-hooks.md)) |
| A middleware returned a response yet the controller still ran | On short-circuiting, the response was placed on `null`/`false` (or the middleware returned nothing) | To short-circuit, **return the response itself** (a string) or `true`; `null`/`false` is always treated as "not handled" and passes through (§4) |
| `php bin/cli.php routes` says the command does not exist | `routes` is provided by `Ext\RouteLister` and is not among the seven built-in commands | Register it into `cmd` (§5) |
| The back-office menu is empty | The controller does not implement `AdminControllerInterface`, or the menu file path is not configured | Extend `AdminControllerBase`; configure `permission_menu_tree_for_admin` (§6) |
| The page did not change after replacing the view implementation | `*_skip_replace` was forgotten, or `View::_(static::_())` is missing from `init()` | See each extension's reference page (§8) |
| Isolation via `$GLOBALS`/static properties still leaks between tests | The old `Ext\StaticReplacer` approach was used | Switch phases or replace the instance ([Chapter 4-1](container-phases.md)) |
| Writing `DuckPhp\Ext\MiniRoute` / `DuckPhp\Ext\Misc` in `ext` → startup throws `ext [...] not exists` | Those two classes **have been deleted** (the author judged nobody uses them / they are not needed) | Remove that line from `ext`: for routing use `Core\Route` ([Chapter 2-3](routing.md)); to pull in a library file use Composer, to escape use `__h()`, for shared instances use the phase container |

## Next steps

- [Chapter 4-2 Developing components and extensions](custom-component.md): the values `ext` accepts, the `EXT_*` modes, and writing your own extension.
- [Chapter 2-4 Route hooks](route-hooks.md): the hook mechanism itself (this chapter only covers the `Ext\RouteHookManager` layer).
- [Chapter 2-9 Helper and global functions](helper.md): the recommended way to do `@method` and `__callStatic`.
- [Chapter 4-10 Design trade-offs and known pitfalls](design-notes.md): which things that "look like they should change" are deliberate.
- Reference manual: one page per `Ext\*` class, see the [reference manual index](../reference/index.md); [DuckPhp\Ext\MyMiddlewareManager](../reference/Ext-MyMiddlewareManager.md), [Ext\RouteHookManager](../reference/Ext-RouteHookManager.md), [Ext\RouteLister](../reference/Ext-RouteLister.md), [Ext\PermissionMenu](../reference/Ext-PermissionMenu.md), [Ext\SqlDumper](../reference/Ext-SqlDumper.md), [Ext\CallableView](../reference/Ext-CallableView.md).
