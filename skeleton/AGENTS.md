# AGENTS.md — DuckPHP project conventions

> Audience: humans and AI coding agents working in **this project** (a DuckPHP application).
> Project-local conventions live here. The framework docs ship inside the package, both Chinese:
> `vendor/dvaknheo/duckphp/docs/zh/guide/` (how-to, 47 chapters) and
> `vendor/dvaknheo/duckphp/docs/zh/reference/` (per-class API and option defaults).
> **Never copy signatures or option tables into this file** — they drift. Keep it under ~200 lines.

## 1. Layout

```text
project/
├── public/index.php                  # web entry — do not edit
├── bin/cli.php                       # CLI entry — do not edit
├── config/DuckPhpSettings.config.php # DB / Redis settings (secrets live here)
├── AGENTS.md                         # this file
├── CLAUDE.md                         # pointer to AGENTS.md (for Claude Code)
├── runtime/                          # writable: logs, caches (not in version control)
├── src/
│   ├── System/
│   │   ├── App.php                   # the app class: every option is set here
│   │   ├── * ProjectException.php    # project exception base (disabled by default)
│   │   ├── * BusinessException.php   # thrown by Helper::BusinessThrowOn() (disabled by default)
│   │   └── * ControllerException.php # thrown by Helper::ControllerThrowOn() (disabled by default)
│   ├── Controller/
│   │   ├── Base.php                  # do not edit
│   │   ├── Helper.php                # do not edit (or use the framework's ControllerHelper)
│   │   ├── MainController.php        # welcome page and short routes
│   │   ├── Session.php               # every session read/write goes through this class
│   │   ├── * AppAction.php           # Action that the System layer calls
│   │   ├── * CommandAction.php       # CLI commands (disabled by default)
│   │   ├── * ExceptionAction.php     # exception reporter (disabled by default)
│   │   ├── * SomeAction.php          # sample Action — delete me
│   │   └── * testController.php      # sample controller, route /test/done — delete me
│   ├── Business/
│   │   ├── Base.php                  # do not edit
│   │   ├── Helper.php                # do not edit
│   │   ├── * DemoBusiness.php        # sample — delete me
│   │   └── * SomeService.php         # sample Service — delete me
│   └── Model/
│       ├── Base.php                  # do not edit; brings Db() / find() / add() / getList()
│       └── DemoModel.php             # sample — delete me
└── view/
    ├── main.php                      # sample view for the welcome page
    ├── test/done.php                 # sample view for the route /test/done
    └── _sys/
        ├── error_404.php
        └── error_500.php
```

A `*` marks a sample file or a feature that is **disabled by default**: delete what you do not use,
or enable one by uncommenting the matching option in `src/System/App.php`. `runtime/` must stay
writable.

## 2. Naming

| Kind | Pattern | Example | Notes |
|---|---|---|---|
| Controller | `{Name}Controller` | `NoteController` | URL segment = method name |
| Controller method | `{action_prefix}{name}` | `index()` | prefix is empty by default, see §4 |
| Action (controller-layer reuse) | `{Name}Action` | `ExportAction` | needs an empty `__construct()` |
| CLI method | `command_{name}` | `command_sync()` | `php bin/cli.php sync` |
| Business | `{Name}Business` | `NoteBusiness` | entry point of business logic |
| Service (business-layer reuse) | `{Name}Service` | `MailService` | called by Business, not by Controller |
| Model | `{Name}Model` | `NoteModel` | class name picks the table: `NoteModel` → `note` |
| Session | `Session` | `Session` | the only place that reads/writes the session |
| Exception | `{Name}Exception` | `ProjectException` | project exceptions live in `System/` |

## 3. Layering rules

### 3.1 Who may call whom

| Caller ↓ / Callee → | Controller | Business | Model |
|---|---|---|---|
| **System** | ✅ **only via `*Action`** | ❌ — go through an Action | ❌ — go through an Action |
| **Controller** | ⚠️ same layer, via `*Action` | ✅ | ❌ |
| **Business** | ❌ | ⚠️ same layer, via `*Service` | ✅ |
| **Model** | ❌ | ❌ | ⚠️ cross-database model only |

Things the table cannot show:

- **`Db`** — only Model touches the database: `Model\Base` gives you `Db()`, `find()`, `add()` and
  `getList()`. A `Helper::Db()` call or raw SQL in Controller / Business / Service / View is out of
  bounds; System only does connection-level work while wiring (e.g. `Helper::DbCloseAll()`).
- **`Session`** — only the Controller layer, through `Controller\Session`: its trait's
  `get()/set()/unset()` are **protected**, so add a public method there and call
  `Session::_()->yourMethod()` from elsewhere. System touches it only while wiring the login
  systems; Business / Service / Model / View never do.
- **`Service`** — not a layer: business-layer reuse, called by `*Business`, never by a Controller.
- **View** — the controller hands data over with `Helper::Show($data, $view)`; a view only displays
  (globals such as `__h()`, `__url()`), it never queries the database or calls Business.

### 3.2 The System layer must go through an Action

CLI commands, the exception reporter, event listeners and route hooks all live in Controller-layer
`*Action` classes (`AppAction` / `CommandAction` / `ExceptionAction` here), and the System layer
**must** reach business code through them — never read Business or Model straight from
`src/System/`, or "request entry point" duties leak into the wiring layer.

### 3.3 Actions called by the System layer may reference the System layer

Such an Action (CLI command, exception reporter, event listener) **may** use System-layer things —
project exception classes, configuration, wiring code; that is what sets it apart from an ordinary
controller. The reverse does not hold: ordinary controllers and business code must not call
System-layer wiring actions (registering routes, events, commands), or the wiring becomes circular.

### 3.4 Violations

```php
// ✗ Controller reaching into the Model — Business owns the rules
class NoteController extends Base {
    public function index() { $list = NoteModel::_()->getList(); }        // ✗ go through Business
}
// ✗ Business reading the request context — breaks CLI, queues and tests
class NoteBusiness extends Base {
    public function save() { $title = $_POST['title']; }                  // ✗ pass it in instead
}
// ✗ Model deciding business rules and throwing
class NoteModel extends Base {
    public function addNote($t) { if (!$t) { throw new \Exception('x'); } }  // ✗ belongs to Business
}
```

## 4. Routing and key defaults

| Thing | Default | Meaning |
|---|---|---|
| Controller class suffix | `Controller` | `FooController` |
| Method prefix | empty | URL segment is the method name; set `controller_method_prefix => 'action_'` to get `action_index()` |
| Welcome class | `Main` | `/` and `/index` land here |
| Welcome method | `index` | |
| Welcome class in URL | `false` | `/Main/...` is rejected (E009); set `controller_welcome_class_visible => true` to allow it |
| Class name adjust | empty | `controller_class_adjust`; `/user/profile` does not become `UserController` unless configured |

`/` → `Controller\MainController::index()`, `/foo` → `MainController::foo()`,
`/user/profile` → `Controller\userController::profile()`; URLs are case sensitive by default.

## 5. Add a feature in 4 steps

1. **Model** — one class per table, data access only:

```php
namespace MyProj\Model;
class NoteModel extends Base {
    public function getList() { return $this->fetchAll('SELECT * FROM note'); }
}
```

2. **Business** — orchestration; no request context, no superglobals:

```php
namespace MyProj\Business;
class NoteBusiness extends Base {
    public function getList() { return NoteModel::_()->getList(); }
}
```

3. **Controller** — take input, call Business, hand data to the view:

```php
namespace MyProj\Controller;
class NoteController extends Base {
    public function index() {
        $list = NoteBusiness::_()->getList();
        Helper::Show(get_defined_vars(), 'note/index');
    }
}
```

4. **View** — `view/note/index.php`, display only:

```php
<ul><?php foreach ($list as $row): ?><li><?= __h($row['title']) ?></li><?php endforeach; ?></ul>
```

## 6. Traps that break things

| Trap | Why it breaks | Do this instead |
|---|---|---|
| `new DemoBusiness()` | bypasses the container, so overriding, sharing and app phases stop working | `DemoBusiness::_()` |
| Business reading `$_GET` / `$_POST` / the session | CLI, queues and tests have no request context | pass the values in as arguments |
| Controller calling a Model directly | skips business rules; overriding stops working | go through Business |
| `Helper::Show('main', $data)` | the signature is `Show($data, $view)` | `Helper::Show($data, 'main')` |
| `Session::_()->set('k', $v)` from a controller | the trait setters are protected | add a public method in `Controller\Session` |
| Editing `Base.php` / `Helper.php` | they hold the layer conventions | extend them in your own classes |

## 7. Errors and exceptions

- Throw conditionally: `Helper::ThrowOn($flag, 'message', $code)`, or `ControllerThrowOn()` /
  `BusinessThrowOn()` to use the exception class of that layer.
- Project exceptions live in `src/System/` (`ProjectException`, plus `BusinessException` /
  `ControllerException` once enabled); error pages are app options (`error_404` / `error_500` are
  view names, `is_debug = true` renders stack traces). Details: guide chapters 1-6 and 2-12.
- Custom reporting: uncomment `exception_reporter` in `src/System/App.php` and implement
  `on{Something}Exception($ex)` in `Controller\ExceptionAction`.

## 8. Keep or delete

Documentation only: deleting this file changes nothing at runtime. `CLAUDE.md` beside it points
Claude Code at the same conventions.
