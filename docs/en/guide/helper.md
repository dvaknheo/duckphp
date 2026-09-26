# 2-9 Helpers and Global Functions

> What this solves: what the Helpers of the four layers are, which one to use, how to write your own Helper in a project, how to choose between `Helper::` and the global functions, and why "this layer's Helper has no such method" is usually telling you that you have crossed a layer.
> Prerequisites: [Chapter 2-1 The Four Layers and Calling Rules](layers.md). About 15 minutes.
> Examples: `demo/src/{Controller,Business,Model}/Helper.php`, `skeleton/src/*/Helper.php`, `tests/data_for_tests/ZThirdDemo/src/Controller/Helper.php` (all thin shell classes of a few lines), `src/Core/Functions.php` (global function definitions).

## Minimal example

A project Helper is really this short (the whole of `demo/src/Controller/Helper.php`):

```php
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\Controller\ControllerHelper;

class Helper extends ControllerHelper
{
}
```

Each of the four layers has one such class, and business code uses them directly:

```php
// in a controller
Helper::Show(get_defined_vars(), 'note/list');
// in the business layer
Helper::BusinessThrowOn(!$note, '便签不存在', 404);
// in a model
$rows = Helper::DbForRead()->fetchAll($sql);
// in a view (global function)
<h1><?= __h($note['title']) ?></h1>
```

## How it works

### 1. Four layers, four classes, each in charge of its own turf

The framework splits the convenience methods into four classes by layer (`src/Foundation/<layer>/`). **One layer's Helper never contains another layer's exclusive methods**:

| Layer | Ready-made class | Representative methods |
| --- | --- | --- |
| Controller | [`DuckPhp\Foundation\Controller\ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md) | `Show()` `ShowJson()` `Show302()` `Show404()` `Render()` `GET()` `POST()` `REQUEST()` `Parameter()` [`Pager()`](../reference/Component-Pager.md) `PageHtml()` `header()` `setcookie()` `ControllerThrowOn()` `UserId()` `AdminId()` |
| Business | [`DuckPhp\Foundation\Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md) | `Setting()` `Config()` `AppOptions()` `XpCall()` `BusinessThrowOn()` [`Cache()`](../reference/Component-Cache.md) [`Validator()`](../reference/Component-Validator.md) `ValidatorFilter()` `FireGlobalEvent()` `OnGlobalEvent()` `AdminService()` `UserService()` `PathOfProject()` `PathOfRuntime()` |
| Model | [`DuckPhp\Foundation\Model\ModelHelper`](../reference/Foundation-Model-ModelHelper.md) (thin shell; the methods all live in [`Model\ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md)) | [`Db()`](../reference/Db-Db.md) `DbForRead()` `DbForWrite()` `SqlForPager()` `SqlForCountSimply()` `DatabaseDriver()` |
| Application/wiring (System) | [`DuckPhp\Foundation\System\SystemHelper`](../reference/Foundation-System-SystemHelper.md) | `addRouteHook()` `replaceController()` `assignRoute()` `assignImportantRoute()` `assignRewrite()` `Redis()` `SESSION()` `getCliParameters()` `isRunning()` `isInException()` `system_wrapper_replace()` |
|  |  |  |

The two "full versions":

- [`DuckPhp\Foundation\Helper`](../reference/Foundation-Helper.md): **declares no methods of its own**; it uses `__callStatic` to find the first layer that has the method in the order **System → Controller → Business → Model** and forwards there; its source carries 96 `@method` annotations (visible only to the IDE / static analysis — invisible to `method_exists()` and reflection).
- [`DuckPhp\DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md): the same dispatch, cramming the whole application into one class.

The project-side `Xxx\Helper` has two ways of being written — pick either:

```php
// Pattern A (what demo / skeleton use): extend the class of this layer, then you can add your own methods
use DuckPhp\Foundation\Controller\ControllerHelper;

class Helper extends ControllerHelper
{
    // dynamic method: added to your own Helper's singleton
    public function money(float $v): string
    {
        return '¥' . number_format($v, 2);
    }
}

Helper::_()->money(12.5);   // the call goes through the singleton (_()), not a static call
```

```php
// Pattern B: when call sites want to keep the name `Helper::`, import with an alias
use DuckPhp\Foundation\Controller\ControllerHelper as Helper;

Helper::Show(get_defined_vars(), 'note/list');
```

**Why a project Helper is worth writing on its own**: it is your project's **dynamic extension point** — add tool methods as **dynamic methods (instance methods)**, and reach them via **singleton calls**:

```php
Helper::_()->money(12.5);   // ✅ dynamic method: take the singleton first, then call the method
Helper::money(12.5);        // ❌ Error: Non-static method ... cannot be called statically
```

`_()` comes from [`SingletonExTrait`](../reference/Core-SingletonExTrait.md) (`src/Core/SingletonExTrait.php`): `PhaseContainer::GetObject(static::class)`, so what you get is **your own Helper instance in the current phase** — methods can read and write instance state (`$this->…`), and you don't compete with the framework's static methods for names. The framework's own methods (`Show()` / `Db()` / `Setting()`) are all **static**; call them directly as `Helper::Show()` as usual.

> ⚠️ **Dynamic methods can only be called through the `_()` of "your project's class"**: the union class [`DuckPhp\Foundation\Helper`](../reference/Foundation-Helper.md) has no `_()` of its own; `DuckPhp\Foundation\Helper::_()` is dispatched by `__callStatic()` to the first stop, [`SystemHelper`](../reference/Foundation-System-SystemHelper.md) — **verified: what you get back is a `SystemHelper` instance, not your Helper**; and `DuckPhp\Foundation\Helper::money()` throws `Call to undefined method` (your methods are not in those 96 `@method` annotations either). So write the full class name for your own methods: `MyProj\Controller\Helper::_()->money(12.5)`.

**Outside `System/`, project code must not directly `use` `DuckPhp\*`**: when you need framework capability, go through **the project Helper of this layer** (framework methods stay static calls, your own tool methods become dynamic methods), or extend **this layer's Base** to get the `_()` singleton entry point; `System/` is the wiring layer (assembling `ext`, attaching hooks, registering commands all happen there), and only it may freely reference framework classes ([Chapter 2-1](layers.md)).

**Don't mix up the roles of dynamic and static methods**: write your own new tool methods as **dynamic methods** (`Helper::_()->x()` — they can carry instance state and don't occupy a name); **static methods** exist to **override the parent implementation** — to change some layer Helper's behavior, no framework surgery is needed:

```php
namespace MyProj\Controller;

use DuckPhp\Foundation\Controller\ControllerHelper;

class Helper extends ControllerHelper
{
    public static function Show($data = [], $view = '')
    {
        $data['page_title'] = $data['page_title'] ?? 'MyProj';   // fill in the title uniformly
        return parent::Show($data, $view);                       // then defer to the framework's original implementation
    }
}
```

Two points must be clear:

- **Only takes effect for calls "through your project's class name"**: `Helper::Show(...)` hits your implementation; but some places inside the framework **call the framework class name directly** (`ControllerHelper::checkInstall()`, `assignViewData()`, `Show302()` inside `UserControllerBase` / `AdminControllerBase`, source `src/Foundation/Controller/UserControllerBase.php` lines 18-31) — those calls **will not** change because you overrode a subclass; static methods have no virtual dispatch. To change that kind of behavior, use a ready-made hook like `onNeedPermission()`, or see [Chapter 4-3 Replacing Framework Behavior](replace-behavior.md).
- An override must be **signature-compatible** (same-named static method, parameters and defaults agree), otherwise PHP throws a fatal error when the class loads: `Declaration of Helper::Show() must be compatible with ...`; if you want to keep the original behavior, fall back with `parent::`.

The model base class takes another road: [`DuckPhp\Foundation\Model\Base`](../reference/Foundation-Model-Base.md) uses both `ModelTrait` and [`ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md), so in model subclasses `$this->Db()` and `$this->getList()` all work (`$model->Db()` — "a static method called via an instance" — also holds).

### 2. Helpers are static facades; underneath are replaceable system wrappers

The methods of `Helper::` are all thin, forwarding to component singletons:

```php
public static function Show($data = [], $view = '')       { return App::_()->_Show($data, $view); }
public static function GET($key = null, $default = null)  { return SuperGlobal::_()->_GET($key, $default); }
public static function Db($tag = null)                    { return DbManager::_()->Db($tag); }
```

The benefit is **replaceability**: [`SuperGlobal`](../reference/Core-SuperGlobal.md), [`SystemWrapper`](../reference/Core-SystemWrapper.md) (`header()`/`setcookie()`/`exit()` and the like), [`Runtime`](../reference/Core-Runtime.md) can all be swapped out in tests or long-running processes without touching business code.

### 3. "This layer has no such method" = the framework reminding you of a layer violation

```php
// trying to output a page in Business:
Helper::Show($data, 'x');     // ❌ the inheritance chain of Business's Helper has no Show()
```

`BusinessHelper` has no `Show()`, `ModelHelper` has no `GET()` — this is not an omission but **a boundary expressed with the type system**: the business layer should not output directly, the model layer should not read requests. Each layer's Helper is a class split exactly along this boundary; "which layer's Helper you extend" in a project amounts to declaring "which layer I belong to". The correct moves:

| What you want to do | Don't use | Use instead |
|---|---|---|
| Read request data in Business | `Helper::GET()` | Let the controller fetch the value and pass it in as a parameter |
| Output a page in Business | `Helper::Show()` | Return data; the controller does the output |
| Throw a business exception in a Model | business exception | Return data/`false`; leave the judgment to Business |
| Read config/settings from any layer | read files directly | `Helper::Setting()` / `Helper::Config()` (usable in the business layer) |

### 4. Global functions: the "Helper" of the view layer

`src/Core/Functions.php` defines a set of global functions, prepared for templates and casual calls:

| Function | Purpose |
| --- | --- |
| `__h($str)` | HTML escaping |
| `__l($str, $args, $fallback)` | Translation ([Chapter 2-15](i18n.md)) |
| `__hl($str, $args)` | Translation + escaping |
| `__langtext($desc, $args)` | Placeholder translation of `[[key\|fallback]]` inside a piece of text |
| `__json($data, $options)` | JSON encoding |
| `__url($url)` / `__domain($use_scheme)` / `__res($url)` | URL / domain / resource address ([Chapter 2-3](routing.md), [Chapter 3-3](static-resources.md)) |
| `__display(...)` | Debug output |
| `__var_dump()` / `__var_log()` / `__trace_dump()` / `__debug_log()` | Debugging and logging ([Chapter 1-6](debugging.md)) |
| `__logger()` | Get the logger |
| `__is_debug()` / `__is_real_debug()` / `__platform()` | Environment detection |
|  |  |

Most natural inside views:

```php
<h1><?= __h($note['title']) ?></h1>
<a href="<?= __url('note/show?id=' . (int)$note['id']) ?>">详情</a>
<p><?= __l('welcome', ['name' => __h($user['name'])]) ?></p>
```

The division of labor between `Helper::` and the global functions:

| Scenario | Use |
|---|---|
| View templates | Global functions (`__h`/`__url`/`__l`) |
| PHP code in controllers/business/models | The corresponding layer's `Helper::` |
| System calls that must be replaceable and testable | `Helper::` (goes through the system wrapper) |

## Common patterns

**① A project Helper only extends the class of its own layer** (see the thin-shell pattern at the start) — **this is part of the boundary**.

**② When you need "import once, usable in all four layers"**

```php
use DuckPhp\Foundation\Helper;      // union facade: __callStatic dispatches across the four layers

Helper::Setting('page_size', 20);   // → Business\BusinessHelper
Helper::Db()->fetch($sql);          // → Model\ModelHelper
Helper::Show($data, 'note/list');   // → Controller\ControllerHelper
```

Know the cost: the union class's methods **do not exist at the reflection level** (`method_exists()` is false; the IDE can only rely on `@method` annotations), and same-named methods win by a fixed order (see [the reference manual](../reference/Foundation-Helper.md#caveats)). Projects with clear layering are better served by pattern A per layer.

**③ Config and settings always go through Helpers; never read files directly**

```php
$limit = Helper::Setting('page_size', 20);        // DuckPhpSettings.config.php / .env
$key   = Helper::Config('payment', 'app_id');     // config/payment.php
```

**④ To replace system calls (tests, long-running processes), use `system_wrapper_replace()`**

```php
Helper::system_wrapper_replace([
    'header' => function ($output, $replace = true, $code = 0) { /* 记录而不真的发送 */ },
    'exit'   => function ($code = 0) { throw new \Exception('exit(' . $code . ')'); },
]);
```

**⑤ Wiring calls like routing/rewrite belong to the System layer**

```php
// inside onInited() of src/System/App.php
Helper::addRouteHook($cb, 'prepend-inner');
Helper::assignRewrite('/legacy', 'home/index');
```

## Common errors

| Symptom | Cause | Fix |
| --- | --- | --- |
| `Call to undefined method ...::Show()` | You are using the business/model layer's Helper (its inheritance chain has no output methods) | Put output in the controller; or check which layer's Helper you `extends` |
| Union `Helper::Foo()` throws `Call to undefined method` | None of the four layers has the method (`__callStatic` reports an error when it finds nothing); **dynamic methods you added to your project Helper are not in the union either** | Check the reference pages of the four layer Helpers, or confirm the name in the `@method` annotations in source; call your project's own dynamic methods via your project Helper's `_()` |
| Your own method `Helper::money()` throws `Non-static method ... cannot be called statically` | You added a **dynamic method** (e.g. `public function money()`) but call it statically | Write `Helper::_()->money(...)` ([§1](#1-four-layers-four-classes-each-in-charge-of-its-own-turf)); if you want static calls, declare the method `static` — but that is the "override the parent implementation" road |
| Union `Helper::Setting()` behaves unexpectedly | Same-named methods are resolved in the order System → Controller → Business → Model | When you need precise semantics, use that layer's class directly (`Business\BusinessHelper::Setting()`) |
| Reflection/IDE can't find the union class's methods | Under the `__callStatic` scheme methods are annotations, not declarations | Use an IDE that supports `@method` annotations; or depend on the four layer classes directly |
| `assignRewrite('article/123', …)` has no effect | The rewrite key **lacks the leading `/`**: the hook compares against `'/'.$path_info` | Write `'/article/123'` |
| `Helper::` in a view says class not found | The view did not import Helper | Use global functions in views (`__h`/`__url`/`__l`) |
| `Helper::GET()` errors in the business layer | Layer violation | Let the controller fetch the parameter and pass it in |
| Swapped the system wrapper but business behavior unchanged | Business code used native functions or `new` directly | Route everything through `Helper::` |
| One "big Helper" shared by all layers | The boundary fails: business can reach output/request methods | Each layer extends only its own layer's class; if you really need the full set, use `Foundation\Helper` and know the cost |
| Business layer writes raw SQL with `Helper::Db()` | Layer violation: bypassing the model layer | Move SQL into models ([Chapter 2-8](model.md)) |
| Can't find some global function | It really isn't defined (spelling/version differences) | Take `src/Core/Functions.php` as authoritative, or use the corresponding `Helper::` method |

## Next steps

- [Chapter 2-10 Forms and Validation](validator.md): usage of `Helper::Validator*`.
- [Chapter 2-19 Using the User System](user.md) / [Chapter 2-20 Using the Admin System](admin.md): `Helper::UserId()` / `AdminService()` etc.
- [Chapter 4-13 `Ext\*` Extension Classes](ext-classes.md): the dynamic static-call schemes before `@method` (`MyFacades*`, `ExtendableStaticCallTrait`) and their replacements.
- [DuckPhp\Core\Functions (global function reference)](../reference/Core-Functions.md): the full function list and signatures.
- The reference manual: [`Controller\ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md), [`Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md), [`Model\ModelHelper`](../reference/Foundation-Model-ModelHelper.md), [`Model\ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md), [`System\SystemHelper`](../reference/Foundation-System-SystemHelper.md), [`Foundation\Helper` (the union)](../reference/Foundation-Helper.md).
