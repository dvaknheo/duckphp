# 1-3 Directory Structure and the Four Layers

> What this solves: laying out the project "the way the framework expects" (directories, naming, coding rules), and learning what each of the four layers owns, who may not call whom, and what you lose after a layer violation.
> Prerequisites: [Chapter 1-2](install.md). About 25 minutes.
> This chapter matches the structure of `skeleton/` (the project the scaffold actually generates); the skeleton fragments below are taken from `skeleton/`.
> The start of Volume 2 has one more page, [Chapter 2-1 The Four Layers and the Calling Rules](layers.md) — that is a **signpost page**; the content lives only in this chapter.

## Minimal example

The entire skeleton of the four layers is the five fragments below (taken from `skeleton/`; every file is very short — this is all of it):

```php
// public/index.php — the entry point does one thing: hand the request to the application class
\YourProjectName\System\App::RunQuickly([]);
```

```php
// src/Controller/MainController.php — the controller: take input, call business, produce output
namespace YourProjectName\Controller;

use YourProjectName\Business\DemoBusiness;

class MainController extends Base
{
    public function index()
    {
        $var = __h(DemoBusiness::_()->foo());   // the business layer gives data, __h() does HTML escaping
        Helper::Show(get_defined_vars(), 'main'); // hand over to the view for rendering (data first, view name second)
    }
}
```

```php
// src/Business/DemoBusiness.php — the business layer: orchestrates logic, never touches the request context
namespace YourProjectName\Business;

use YourProjectName\Model\DemoModel;

class DemoBusiness extends Base
{
    public function foo()
    {
        return "<" . DemoModel::_()->foo() . ">";
    }
}
```

```php
// src/Model/DemoModel.php — the model: data access only
namespace YourProjectName\Model;

class DemoModel extends Base
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}
```

```php
<!-- view/main.php — the view: display only -->
<h1><?= $var ?></h1>
```

The call chain is a straight line with no way back:

```
HTTP 请求 → 路由 → MainController::index()
                        │  取输入（Helper::GET/POST/Parameter）
                        ▼
                  DemoBusiness::foo()      ← 业务编排、校验、拼装
                        │
                        ▼
                  DemoModel::foo()         ← 数据访问（Db）
                        │
                        ▼
                  Helper::Show(数据, 视图)  ← 渲染，结束
```

To see the whole picture: every file in `skeleton/` is very short; reading them in order is one complete chain — entry `public/index.php` → application class `src/System/App.php` → `src/Controller/MainController.php` → `src/Business/DemoBusiness.php` → `src/Model/DemoModel.php` → view `view/main.php` (`view/test/done.php` is another equally short chain, mapped to URL `/test/done`).

## How it works

### 1. Standard structure

```
project/
├── public/index.php     ← Web 入口：只有几行「加载 + 启动」，勿改
├── bin/cli.php          ← CLI 入口：同样是「加载 + 启动」，勿改
├── config/              ← 设置文件：数据库/Redis 等敏感信息（第 1-5 章）
├── src/                 ← 代码：System / Controller / Business / Model（下一节讲谁不能调谁）
├── view/                ← 视图（含 _sys/ 错误页）
├── runtime/             ← 日志等可写目录（要可写，别提交进版本库）
└── vendor/
```

**Which concrete files** live in each directory (which are "do not edit", which are examples, which are disabled by default) is maintained in exactly one place — the [`skeleton/AGENTS.md`](../../../skeleton/AGENTS.md) shipped with the project; when you scaffold a project it sits in your project root as the single file-level list. The diagram above only draws the "seven top-level directories" and explains why they are split this way.

**Why the framework can find these things** (four rules):

| What it looks for | Basis                                                         |
| ----- | ---------------------------------------------------------- |
| Controller directory | Derived from the **directory holding the application class file** + `namespace_controller` (default `Controller`) |
| View files  | The app `path` + `path_view` (default `view`) + the view name                    |
| Config files  | The app `path` + `path_config` (default `config`)                     |
| Settings file  | The `setting_file` option (default `config/DuckPhpSettings.config.php`)  |

So: **class files must land in the directory matching their namespace** (`MyProj\Controller\NoteController` → `src/Controller/NoteController.php`, with `path` pointing at the project root).

### 2. Naming conventions

The suffix table for each kind of file (`Controller` / `Action` / `Business` / `Service` / `Model` / `Exception` / `Session` / `command_`) with per-suffix examples is maintained only in [`skeleton/AGENTS.md`](../../../skeleton/AGENTS.md). Here are just the three easiest to trip over:

- **The class name `{name}Model` decides the table name**: `NoteModel` → table `note`; if the table name differs, write `protected $table_name = 'notes';` in the model.
- **An Action must have a no-argument `__construct()`**: it overrides the base constructor so it isn't initialized as a controller entry point (see "Common patterns ②").
- **URLs are case-sensitive**: by default `/note/list` is not auto-corrected to `/Note/list`; for lenient matching set `controller_class_adjust` ([Chapter 2-3](routing.md)).

### 3. Layer responsibilities and boundaries

| Layer                                     | Responsibility        | May do                                            | **May not do**                                      |
| ------------------------------------- | --------- | ---------------------------------------------- | --------------------------------------------- |
| **System**                            | Wiring + shared kernel (not part of the four layers) | Configuration, registering events/commands, **calling business via `*Action`**, assembling the application class; the four layers may **reference** definitions inside it (project exception classes, config) | Being called back by the four layers for its **wiring actions** (registering routes/events/commands)                  |
| **Controller**                        | Entry and exit of a request  | Take input, call Business, hand data to the view, redirect, 404                  | Writing business rules, querying the database directly, building SQL                            |
| **Business**                          | Business logic orchestration    | Call Model, call Service, conditionally throw business exceptions                      | Reading/writing `$_GET`/`$_POST`/`$_SERVER`/Session, depending on the current request |
| **Model**                             | Data access      | Call [Db](../reference/Db-Db.md), do per-table CRUD, return arrays/objects | Writing business judgments, throwing business exceptions, calling Business                        |
| **[View](../reference/Core-View.md)** | Display        | Output via Helper and global functions, read the data the controller gave                      | Querying the database, calling Business, writing business logic                         |

> **Coding rule**: in the four layers `Controller`, `Business`, `Model`, `View`, except for Helper and the global functions, **do not directly `use` `DuckPhp\*` framework classes**; framework-related calls are concentrated in the `System` layer, or done by `Helper` on your behalf. The point of this rule shows up in Volume 3: package assembly (`ext`, overriding, phases) all happens in the `System` layer, so the business code can be reused wholesale.

Each of the four layers has a framework base class to extend (each does only one thing: give the layer's classes the singleton entry `_()`): controller [`Controller\Base`](../reference/Foundation-Controller-Base.md), business [`Business\Base`](../reference/Foundation-Business-Base.md), model [`Model\Base`](../reference/Foundation-Model-Base.md) (the model base has one extra layer: it also `use`s [`ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md), so `$this->Db()` works directly inside models). For the division of labor among Helpers, see §7 of this section.

### 4. The iron rules of cross-layer calls

```
System        框架相关调用、异常定义、应用配置
  ↑
Controller    请求入口：收输入、出输出（可调 Action / Business / Helper / Session；禁止直接调 Model、Service）
  ↑
Business      业务逻辑（纯无状态；可调 Model / Service / Helper；禁止读写 Session、禁止碰 $_GET/$_POST）
  ↑
Model         数据访问（纯无状态；只做数据存取；禁止业务逻辑、禁止抛异常）
View          只做展示（只用全局函数）
```

**Violation examples** (these patterns make code unreusable in CLI / tests):

```php
// ❌ the controller calls a Model directly, bypassing the business layer
class NoteController extends Base
{
    public function index()
    {
        $list = NoteModel::_()->getList();      // violation: should go through NoteBusiness
    }
}

// ❌ the business layer reads superglobals (it breaks under CLI / queues)
class NoteBusiness extends Base
{
    public function save()
    {
        $title = $_POST['title'];               // violation: the controller should pass it in as a parameter
    }
}

// ❌ the model throws a business exception (a model shouldn't know business rules)
class NoteModel extends Base
{
    public function addNote($t)
    {
        if (!$t) { throw new \Exception('标题不能为空'); }   // violation: leave it to the Business layer
    }
}
```

The correct way to throw: `Helper::ThrowOn(...)` ([Chapter 2-12](exception.md)).

### 5. The layer-violation matrix (the most important table in this chapter)

The "caller" in the left column calls the "callee" in the right column; ✅ allowed, ⚠️ conditional, ❌ forbidden:

| Caller ↓ / Callee →  | Controller           | Business             | Model                    |
| -------------- | -------------------- | -------------------- | ------------------------ |
| **System**     | ✅ **only via `*Action`**    | ❌ (must go through Action)          | ❌ (must go through Action)              |
| **Controller** | ⚠️ same-layer reuse goes through Action     | ✅                    | ❌                        |
| **Business**   | ❌                    | ⚠️ same-layer reuse extracted as `*Service`   | ✅                        |
| **Model**      | ❌                    | ❌                    | ⚠️ cross-database models are the exception                |

`Db` / `Session` / `Service` / `View` are not "layers that call each other", so they take no row or column; their rules are spelled out in these sentences:

- **`Db`**: only Models touch the database — [`Model\Base`](../reference/Foundation-Model-Base.md) already provides `Db()` / `find()` / `add()` / `getList()`; `Helper::Db()` or raw SQL appearing in any other layer is a layer violation. The System layer only does connection-level actions while wiring (e.g. `Helper::DbCloseAll()`).
- **`Session`**: read and written only in the Controller layer via `Controller\Session` — [`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md)'s `get()`/`set()`/`unset()` are `protected`, so add public methods in that class and call them from outside as `Session::_()->yourMethod()`; the System layer only touches it when assembling the login system; Business / Service / Model / View never touch it (why Business must be stateless: see §6).
- **`Service`**: not a fifth layer, but reuse **inside the business layer** — called by `*Business`; **controllers do not call Service directly** (go through Business).
- **View**: the controller hands data over with `Helper::Show($data, $view)`; the view only displays (global functions like `__h()`, `__url()`), never queries the database or calls Business.

The matrix rows are assigned by **directory + suffix**: `*Controller` and `*Action` under `Controller/`, `*Business` and `*Service` under `Business/` (`*Service` belongs to the business layer), `*Model` under `Model/` (the directory and suffix list is in the project's `AGENTS.md`). **A class without these suffixes and outside these directories does not belong to the four layers** — it has no "home layer" to rely on, so it cannot be called cross-layer by other layers; it can only live in `System/` (the wiring site) or in the same layer as its caller.

The `System` row is a **hard requirement**: it **must** call business through the Controller layer's `*Action` — CLI commands ([Chapter 2-16](cli.md)), exception reporters ([Chapter 2-12](exception.md)), event callbacks, and route hooks all sit at this position; the skeleton's `AppAction`, `CommandAction`, `ExceptionAction` are exactly that. Do not skip Action and read Business/Model directly, or the "request entry" responsibility bleeds into the wiring layer.

**An Action called by the System layer may reference System-layer things** (project exception classes, config, assembly code) — that is its difference from an ordinary controller: an Action is part of the wiring. Conversely, **ordinary controllers and business code must not call back into System's wiring actions** (registering routes, events, commands), or you get circular assembly.

The three easiest to misremember:

- **Controllers don't touch Db/Model**: one casual "quick query" is a layer violation, because it bypasses the business rules (validation, permissions, transaction boundaries all live in Business).
- **Business doesn't touch the request context**: everything `Business` gets should come in as parameters. Reasons in the next section.
- **Views read but never write**: querying the database or calling business in a view turns rendering into a second business execution.

### 6. Why Business must be stateless

This isn't purism; there are two very concrete consequences:

1. **The same Business is reused by many entry points**: Web requests, CLI commands ([Chapter 2-16](cli.md)), scheduled tasks, tests ([Chapter 2-17](testing.md)) all call it. The moment it reads `$_GET` or the Session, it breaks under CLI.
2. **Testability**: stateless + parameters in, return values out, is what lets you unit-test without booting a server (that's how the business/model tests in [Chapter 2-17](testing.md) are written).

So the convention is: **the request context is only allowed in the Controller layer and in Helpers** (`Helper::GET()`, `Helper::Parameter()`; the session is wrapped in public methods inside `Controller\Session`), and all Business inputs are passed explicitly.

### 7. Helper layering: one per layer

The framework also splits "which convenience methods you may use" by layer — `Helper` is not one grab-bag, but four classes split by layer:

| Layer          | Project-side class (`YourProjectName\<layer>\Helper`) | Corresponding framework class                                                                                                                                                                      | Typical methods                                                   |
| ---------- | ----------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| Controller | `Controller\Helper`                 | [`DuckPhp\Foundation\Controller\ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md)                                                                   | `Show()`, `ShowJson()`, `Show302()`, `GET()`, `POST()`     |
| Business   | `Business\Helper`                   | [`DuckPhp\Foundation\Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md)                                                                           | `Setting()`, `Config()`, `BusinessThrowOn()`, `XpCall()`  |
| Model      | `Model\Helper`                      | [`DuckPhp\Foundation\Model\ModelHelper`](../reference/Foundation-Model-ModelHelper.md) (thin shell; the methods live in [`Model\ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md)) | `Db()`, `DbForRead()`, `SqlForPager()`                   |
| App/wiring      | `System\Helper`                     | [`DuckPhp\Foundation\System\SystemHelper`](../reference/Foundation-System-SystemHelper.md)                                                                                   | `addRouteHook()`, `OnGlobalEvent()`, `FireGlobalEvent()` |

The project-side `Xxx\Helper` class itself is extremely short: one line `extends` the framework's Helper for that layer, plus whatever you add on demand. But **write custom methods as dynamic methods** — `public function foo()`, called as `Helper::_()->foo()`; **do not add static methods**, because the framework's own Helper methods are all static; mixing them both invites name collisions and loses the instance-side semantics of "phase / being overridden" (`skeleton/src/Controller/Helper.php` contains such an example method). You can also use the framework's ready-made classes directly; to merge the four layers into one entry point, use [`DuckPhp\Foundation\Helper`](../reference/Foundation-Helper.md) (`__callStatic` dispatch, see [Chapter 2-9](helper.md)). **The converse matters more**: when a method is missing from your layer's Helper, that is usually the framework telling you "this thing should not be done in this layer".

Views use **global functions** (defined in `src/Core/Functions.php`, see the [global functions reference](../reference/Core-Functions.md)):

| Function                                         | Purpose                                        |
| ------------------------------------------ | ----------------------------------------- |
| `__h($str)`                                | HTML-escaped output (the first line of defense against XSS)                     |
| `__url($url)` / `__domain()`               | Generate an in-site URL / domain prefix                           |
| `__res($url)`                              | Generate a static-resource URL ([Chapter 3-3](static-resources.md)) |
| `__json($data)`                            | JSON encoding                                   |
| `__l($str)` / `__langtext()` / `__hl()`    | i18n combined with escaping ([Chapter 2-15](i18n.md))               |
| `__logger()`, `__debug_log()`, `__var_log()` | Logging ([Chapter 1-6](debugging.md))                 |

### 8. The framework doesn't actually enforce these conventions

To be clear: DuckPHP has **no** runtime interceptor stopping you from writing `new DemoModel()` in a controller. The price of violating the conventions is not an error, but these capabilities silently breaking —

- Overriding ([Chapter 3-5](overriding.md)) breaks: overriding works by "replacing the class / replacing the file", and a layer-violating direct call chain bypasses the replacement point;
- Multi-app ([Chapter 3-1](advanced-phase.md)) breaks: a cross-phase direct call gets the instance of another phase (or the root phase);
- Testing and CLI reuse become hard ([Chapters 2-16](cli.md), [2-17](testing.md));
- Business rules get a second implementation: validated in Business on one side, validated again in the controller on the other.

So this chapter's iron rules boil down to: "to keep the framework's killer features, hold the boundaries".

## Common patterns

**① Service: logic shared by multiple Business classes**
A Service has no dedicated base class or registration mechanism; it is just "a plain class placed under `Business/`, called by multiple Business classes, and never touching the request context" — `skeleton/src/Business/SomeService.php` sits in exactly that position (a scaffold example file; delete it when done). The shape is roughly:

```php
namespace MyProj\Business;

class CommonService
{
    public function writeAuditLog(string $action, array $data): void
    {
        // does one thing: writes the audit log into the model layer
    }
}
```

**② Action: orchestration shared by multiple Controllers**
**Orchestration** (not business rules) reused between controllers is extracted into an Action, avoiding controllers inheriting from each other; `skeleton/src/Controller/SomeAction.php` is the template (a no-argument `__construct()` is mandatory). The key point: an Action **may only call Business and Session, never Model directly**.

**③ The System layer only wires**
Configuration, exception/error pages, command registration, event registration, and the child-app declarations in `app` all live in `src/System/`: `skeleton/src/System/App.php` is the complete wiring template (options, error pages, commented examples for `cmd`/`exception_reporter` etc., `app`).

**④ In-layer reuse goes through the `::_()` singleton, not `new`**

```php
DemoBusiness::_()->foo();   // ✅ the current phase's singleton, can be overridden/replaced
new DemoBusiness();         // ❌ bypasses the container: overriding and sharing both break
```

**⑤ Cross-phase calls (Volume 3 material; laying down the rule here first)**
When you truly need something from another app, use the phase API or a phase proxy from [Chapter 3-1](advanced-phase.md), not `new` on another app's class.

## Common errors

| Symptom                                       | Cause                                           | Fix                                                               |
| ---------------------------------------- | -------------------------------------------- | ---------------------------------------------------------------- |
| New controller gives 404                                | File name / namespace / suffix mismatch          | `src/Controller/NoteController.php` + `class NoteController` + default suffix `Controller` |
| View not found                                    | View name and file path disagree             | `Helper::Show($data)` uses the current route path; when specifying explicitly write `'note/index'` → `view/note/index.php` |
| `Helper::Show('main', $data)` gives a blank page or "view not found" | Argument order reversed: the real signature is `Show($data = [], $view = '')` | Change to `Helper::Show($data, 'main')`; passing current variables with `get_defined_vars()` is the easiest |
| Framework errors after editing `Base.php`/`Helper.php`          | Those are framework convention files                | To extend, add to your own subclass; don't edit the base classes |
| `DemoModel::_()` or SQL appears in a controller            | Layer violation: skipped the business layer                                    | Move the query into Business; the controller only calls Business                                    |
| `$_GET['id']` reports "undefined" in Business          | Business read the request context; those superglobals don't exist under CLI/tests              | The controller reads the value and **passes it in as a parameter**                                        |
| View queries the database; page gets slow or data inconsistent                       | The view ran the business a second time                                   | Data is prepared by the controller; the view only renders                                                   |
| Overriding a class/file has no effect                             | There's a `new` in the call chain, or an instance is fetched from another phase directly                      | Use `::_()` throughout; use the phase API across phases ([Chapter 3-1](advanced-phase.md))                                   |
| More and more [`use DuckPhp\Core\App;`](../reference/Core-App.md) in controllers        | Framework details are seeping into the business layer                                   | Move framework calls into the System layer or the layer's Helper                                      |
| The same business rule written once in the controller and once in Business              | The boundary wasn't held; the rule got a second implementation                               | Keep rules only in Business; the controller only shapes parameters                                         |
| Model throws business exceptions, writes permission checks                       | The model layer is doing the business layer's job                                   | Keep judgments in Business; Model only returns data (exception handling: see [Chapter 2-12](exception.md))           |

## Next steps

- [Chapter 1-4 Your First Page](quickstart.md): write your first complete feature on this structure.
- [Chapter 1-5 Options and Settings](configuration.md): the difference between options in `App.php` and settings in `config/`.
- [Chapter 2-2 Request Lifecycle](lifecycle.md): how this layering is assembled at runtime.
- [Chapter 2-3 Advanced Routing](routing.md): how a request lands on a controller method.
- [Chapter 2-5 Controllers](controllers.md) / [Chapter 2-6 Views and Templates](views.md): how to take input, and the ways to produce output.
- [Chapter 2-7 Database](database.md) and [Chapter 2-8 The Model Layer](model.md): everything below the model column.
- The reference manual: [DuckPhp\Foundation\Helper](../reference/Foundation-Helper.md), [DuckPhp\Foundation\Controller\ControllerHelper](../reference/Foundation-Controller-ControllerHelper.md), [DuckPhp\Foundation\Business\BusinessHelper](../reference/Foundation-Business-BusinessHelper.md), the four layer base classes [Controller\Base](../reference/Foundation-Controller-Base.md) / [Business\Base](../reference/Foundation-Business-Base.md) / [Model\Base](../reference/Foundation-Model-Base.md).
