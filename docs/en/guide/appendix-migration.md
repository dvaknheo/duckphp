# Appendix C · Migrating from Yii2 / CodeIgniter / Laravel / ThinkPHP

> Purpose: for people **coming from another framework**, a cheat sheet — how the concepts map, how the code changes, which default assumptions in your head must be dropped.
> Prerequisites: volume 1 (the positioning comparison in [Chapter 1-1](../guide/intro.md)) and [Chapter 2-1 The Four Layers and the Calling Rules](../guide/layers.md).
> The one-line conclusion: DuckPHP keeps the familiar "routing → controller → view + model" shape, but has **no ORM, no PSR-7/PSR-15, no annotation routes, no service container with auto-injection**; in exchange you get "runnable as a single file, phase isolation, override/replace any layer".

## 1. Concept Mapping

| What you are looking for | Yii2 | CodeIgniter 4 | Laravel | ThinkPHP | DuckPHP |
|---|---|---|---|---|---|
| Entry point (入口) | `web/index.php` | `public/index.php` | `public/index.php` | `public/index.php` (CLI is the `think` file in the root directory) | any entry file → [`App::RunQuickly()`](../reference/Core-App.md) ([Chapter 4-6](../guide/multi-entry.md)) |
| Application class (应用类) | `yii\web\Application` configuration | `Config\App` | `bootstrap/app.php` | `app\AppService` (`boot()`) + `config/app.php` | your own [`MyProj\System\App extends DuckPhp`](../reference/DuckPhp.md) ([Chapter 1-5](../guide/configuration.md)) |
| Routing (路由) | `UrlManager` rules | `Config\Routes` | `routes/web.php` | `route/app.php` (`Route::get()`), annotation routes also supported | conventions (path → `Controller\Xxx::method`) + `route_map` / hooks ([Chapter 2-3](../guide/routing.md)) |
| Controller (控制器) | `yii\web\Controller` | `BaseController` | `App\Http\Controllers\Controller` | `app\controller\Index` (extends `app\BaseController`) | `Controller\XxxController` (extending `Foundation\Controller\Base` recommended) ([Chapter 2-5](../guide/controllers.md)) |
| Request object (请求对象) | `Yii::$app->request` | `$this->request` | `Request $request` injection | `think\Request` (`$this->request->param()`, or the helper functions `input()`/`request()`) | **there is no request object**: `Helper::GET()/POST()/Parameter()` ([Chapter 2-5](../guide/controllers.md)) |
| Response object (响应对象) | `$this->asJson()` etc. | `$this->response` | `response()` / `Response` | `json()` / `redirect()` / `return $this->fetch()` | `Helper::Show()` / `ShowJson()` / `Show302()` / `Show404()` ([Chapter 2-5](../guide/controllers.md)) |
| View (视图) | `$this->render()` + `layouts/` | `view()` + `layout` | Blade `view()` + `@extends` | the Think template engine: `$this->fetch('index')` + `{$var}`/`{volist}` tags (`view/` directory) | PHP files + `Helper::Show()` + `setViewHeaderFooter()` (**no template syntax**) ([Chapter 2-6](../guide/views.md)) |
| Model (模型) | ActiveRecord (`yii\db\ActiveRecord`) | `Model` + `Entity` | Eloquent | ORM (`think\Model`: `where()->select()` / `create()` / relations `hasMany`) | **no ORM**: [`ModelTrait`](../reference/Foundation-Model-ModelTrait.md) provides table name / read-write splitting / CRUD primitives; you write the SQL yourself ([Chapter 2-8](../guide/model.md)) |
| Migration (迁移) | `yii migrate` | `spark migrate` | `artisan migrate` | `php think migrate:run` (needs `topthink/think-migration`) | nothing built in; the table-creation SQL goes in the model's `init()` or in the install flow ([Chapter 2-7](../guide/database.md)) |
| Query builder (查询构造器) | `Yii::$app->db->createCommand()` | `$db->table()` | `DB::table()` | `Db::name('user')->where(...)->select()` (`think\facade\Db`) | [`Helper::Db()->fetchAll($sql, ...)`](../reference/Db-Db.md) + the `` `'TABLE'` `` macro + the fragment assembly of [`DbAdvanceTrait`](../reference/Db-DbAdvanceTrait.md) ([Chapter 2-7](../guide/database.md)) |
| Configuration (配置) | `config/web.php` arrays | `.env` + `Config\*` | `config/*.php` + `.env` | `config/*.php` + `.env` (helper function `config('app.')`) | **two separate sets**: `options` (whitelisted, in code) and `settings` (files/`.env`, read-only) ([Chapter 1-5](../guide/configuration.md)) |
| Middleware (中间件) | `behaviors()` / filters | Filters | Middleware | `app/middleware.php` (`think\middleware\*`) | hook chains are primary; middleware is only a compatibility extension ([Chapter 2-4 Route Hooks](../guide/route-hooks.md)) |
| Events (事件) | `Event::on()` | Events | Events/Listeners | `app/event.php` + `Event::trigger()` | [`GlobalEvent`](../reference/Component-GlobalEvent.md) (callbacks are **bound to a phase**) ([Chapter 2-13](../guide/events.md)) |
| DI / container (依赖注入/容器) | `Yii::$container` | Services | Service Container | `think\Container` (`app()->bind()`, constructor injection / facades supported) | **a singleton container**: `Xxx::_()` / `Xxx::_($new)` (no auto-injection) ([Chapter 4-1](../guide/container-phases.md)) |
| Session / user (会话/用户) | `Yii::$app->user` | `session()` + custom | `Auth` | `Session::set()` / the helper function `session()` (`think\facade\Session`) | [`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) + [`GlobalUser`](../reference/GlobalUser-GlobalUser.md)/[`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md) (configured with callbacks) ([Chapter 2-11](../guide/session.md), [Chapter 2-19](../guide/user.md), [Chapter 2-20](../guide/admin.md)) |
| Validation (验证) | `Model::rules()` | Validation | FormRequest / `validate()` | validator classes `app\validate\*` + `$this->validate($data, 'User')` | the three conventions of the [`Validator`](../reference/Component-Validator.md) component ([Chapter 2-10](../guide/validator.md)) |
| Cache (缓存) | `Yii::$app->cache` | `cache()` | `Cache::` | `Cache::get()/set()` / the helper function `cache()` | `Helper::Cache()` (a null implementation by default; install [`RedisCache`](../reference/Component-RedisCache.md) for it to take effect) ([Chapter 2-14](../guide/cache.md)) |
| Internationalization (国际化) | `Yii::t()` | `lang()` | `__()` | the helper function `lang()` (`lang/zh-cn.php`) | `__l()` / `__hl()` (five-level language detection) ([Chapter 2-15](../guide/i18n.md)) |
| Command line (命令行) | the `yii` command | `spark` | `artisan` | `php think` (`app\command\*`, registered in `config/console.php`) | your own `cli.php` + `command_xxx()` ([Chapter 2-16](../guide/cli.md)) |
| Testing (测试) | Codeception/PHPUnit | PHPUnit | PHPUnit + Pest | PHPUnit (the official test extension is unmaintained; most people wire it up themselves) | PHPUnit (`tests/` + `data_for_tests/`) ([Chapter 2-17](../guide/testing.md)) |
| Queue (队列) | `yii\queue` | none | Queue | the `topthink/think-queue` extension | **nothing built in**: wire up Redis / an external service yourself |
| Scheduling (调度) | console commands + cron | cron | Scheduler | cron + extensions like `think-cron` | cron + your own commands ([Chapter 2-16](../guide/cli.md)) |

## 2. Side by Side: a "List + Create" Feature

**Laravel version (for comparison, illustrative)**

```php
Route::get('/notes', [NoteController::class, 'index']);
Route::post('/notes', [NoteController::class, 'store']);

class NoteController extends Controller {
    public function index(Request $r) {
        return view('notes.index', ['notes' => Note::paginate(10)]);
    }
    public function store(Request $r) {
        $data = $r->validate(['title' => 'required|max:64']);
        Note::create($data);
        return redirect()->route('notes.index');
    }
}
```

**ThinkPHP version (for comparison, illustrative)**

```php
// route/app.php
Route::get('notes', 'Note/index');
Route::post('notes', 'Note/save');

// app/controller/Note.php
namespace app\controller;

use app\model\Note;

class Note extends BaseController
{
    public function index()
    {
        $notes = Note::order('id', 'desc')->paginate(10);       // ORM + built-in pagination
        return $this->fetch('index', ['notes' => $notes]);      // Think template: {volist name="notes" id="vo"}…
    }
    public function save()
    {
        $data = $this->request->only(['title' => '']);
        $this->validate($data, 'NoteValidate');                 // a failed validation throws directly
        Note::create($data);
        return redirect((string)url('notes'));
    }
}
```

**The DuckPHP version (same feature)**

```php
// routing: nothing to configure (/notes → Controller\notesController::index; or write a route_map to bind it explicitly)
// Controller
namespace MyProj\Controller;

class NoteController extends Base
{
    public function index()
    {
        $page = (int)Helper::GET('page', 1);
        [$total, $notes] = NoteBusiness::_()->paginate($page, 10);   // the business layer owns pagination
        $pager = Helper::PageHtml($total);
        Helper::Show(get_defined_vars(), 'notes/index');
    }
    public function store()
    {
        $errors = Helper::Validator()->init(['title' => 'required|maxLen:64'])->valid(Helper::POST());
        if ($errors) {
            Helper::ShowJson(['code' => 1, 'errors' => $errors]);    // or re-display the form
            return;
        }
        NoteBusiness::_()->create(Helper::POST());
        Helper::Show302(Helper::Url('notes'));
    }
}
```

Ways of thinking you must drop (which framework they come from in parentheses):

1. **There is no `Request`/`Response` object** (Laravel and ThinkPHP both have them): input via `Helper::GET/POST/Parameter()`, output via `Helper::Show*/Show302`;
2. **There is no helper-function family** (ThinkPHP's `input()`/`db()`/`cache()`/`session()`/`config()`/`lang()`): the counterparts are `Helper::GET()`, `Helper::Db()`, `Helper::Cache()`, `Controller\Session` ([Chapter 2-11](../guide/session.md)), `Helper::Setting()`/`Helper::Config()`, `__l()`;
3. **There is no ORM** (Eloquent, `think\Model`): `where()->select()`, `create()`, relations, built-in pagination — none of these exist. Hand-write SQL in the model; paginate with `Helper::PageHtml()` ([Chapter 2-7](../guide/database.md), [Chapter 2-8](../guide/model.md));
4. **Where validation lives**: Laravel's `$r->validate()` and ThinkPHP's `$this->validate()` both "throw on failure"; here the better fit is "the business layer throws + the controller hands the error array to the view" ([Chapter 2-10](../guide/validator.md));
5. **The business layer is a mandatory middle link**: do not call `Model::create()` straight from a controller the way many Laravel/ThinkPHP projects do ([Chapter 2-1](../guide/layers.md));
6. **"Multi-app" is not phases** (ThinkPHP's multi-app mode): there, multiple apps share one container; in DuckPHP every app has its own instance space, and fetching things across apps must go through the phase API ([Chapter 3-1](../guide/advanced-phase.md)).

## 3. Migration Steps (suggested order)

1. **Get one page running first**: install dependencies → create `src/System/App.php` + one controller + one view ([Chapter 1-2](../guide/install.md), [Chapter 1-4](../guide/quickstart.md));
2. **Move the routes**: write the old framework's route table against `route_map` (skip whatever the conventions already hit) ([Chapter 2-3](../guide/routing.md));
3. **Move the data layer**: rewrite ORM calls as "model methods + hand-written SQL", with the `` `'TABLE'` `` macro handling the table prefix ([Chapter 2-7](../guide/database.md), [Chapter 2-8](../guide/model.md));
4. **Fill in the business layer**: move business decisions out of controllers down into Business; controllers keep only input and output ([Chapter 2-1](../guide/layers.md));
5. **Move the views**: swap template syntax for PHP; escape everything with `__h()` ([Chapter 2-6](../guide/views.md));
6. **Move the cross-cutting concerns**: middleware/filters → hooks or (when necessary) [`Ext\MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md) ([Chapter 2-4 Route Hooks](../guide/route-hooks.md));
7. **Sessions and login**: sessions in [Chapter 2-11](../guide/session.md); using the user/admin systems in [Chapter 2-19](../guide/user.md) and [Chapter 2-20](../guide/admin.md); implementing the integration in [Chapter 4-11](../guide/impl-user.md) and [Chapter 4-12](../guide/impl-admin.md);
8. **Fill in what you implement yourself**: CSRF, uploads, rate limiting, queues (not provided by the framework) ([Chapter 2-18](../guide/security-performance.md));
9. **Write smoke tests**: follow `ZAllDemoTest` — boot the built-in server and curl each entry point ([Chapter 2-17](../guide/testing.md)).

## 4. The Differences You Are Most Likely to Trip On

| Difference | Explanation |
|---|---|
| **The options whitelist** | A key not declared in the component's `$options` ⇒ what you passed is **silently dropped**, no error ([Chapter 1-5](../guide/configuration.md)) |
| **No ORM / no Active Record** | Relations, lazy loading, automatic timestamps — you write them all yourself; `ModelTrait` only gives primitives ([Chapter 2-8](../guide/model.md)) |
| **No PSR-7 / PSR-15** | Do not expect `$request->input()` or the standard middleware signature; the request in `Ext\MyMiddlewareManager` is just a `stdClass` ([Chapter 2-4 Route Hooks](../guide/route-hooks.md)) |
| **URLs must go through the generator** | A hand-written `/note/show` 404s under subdirectory deployment; always use `__url()` ([Chapter 2-3](../guide/routing.md)) |
| **The four layers are a convention** | Layer violations raise no error, but overriding / multi-app / test reuse quietly stops working ([Chapter 2-1](../guide/layers.md)) |
| **Singletons are globally mutable** | `Xxx::_($new)` replaces the instance in the container — powerful, but watch the blast radius ([Chapter 4-1](../guide/container-phases.md)) |
| **Multi-app is "multiple phases in one process"** | Not multi-process / multi-site; which layer gets shared is an explicit decision ([Chapter 3-4](../guide/component-sharing.md)) |
| **Error pages must be configured** | Without `error_404`/`error_500` configured you get English placeholder text ([Chapter 2-12](../guide/exception.md)) |
| **The debug switch has two levels** | `is_debug` and `duckphp_is_debug` (a setting), combined as "root OR child app" ([Chapter 2-18](../guide/security-performance.md)) |

## 5. Habits Worth Taking With You on a Reverse Migration (from DuckPHP to another framework)

- **The Business layer**: concentrating business rules in stateless classes is the easiest thing to move verbatim when switching frameworks;
- **The `__h()` escaping discipline**: keep explicitly escaping user data when you move to Blade/Twig;
- **The URL-generator habit**: the target framework also has `route()`/`url()`; do not slide back into hard-coded paths.

---

> Companion reading: Appendix D (look up by symptom), Appendix B (copyable snippets), [Chapter 4-9 Troubleshooting Manual](../guide/troubleshooting.md).
