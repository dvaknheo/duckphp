# 2-5 Controllers

> What this solves: how a controller reads input, how it sends the result out (four ways), how it redirects and reports a 404, and what must **not** be written in a controller.
> Prerequisites: [Chapter 2-1 The four-layer architecture and calling rules](layers.md), [Chapter 2-3 Routing in depth](routing.md). About 20 minutes.
> Examples: `skeleton/src/Controller/MainController.php` (a real controller from the five-layer skeleton) and `demo/public/demo.php` (the single-file version, including login and redirects).

## Minimal example

The whole controller from `skeleton/src/Controller/MainController.php` (this is the template for "how thin a controller should be"):

```php
namespace YourProjectName\Controller;

use YourProjectName\Business\DemoBusiness;

class MainController extends Base
{
    public function __construct()
    {
        $this->initController();
    }
    protected function initController()   // the hook the skeleton leaves you: per-controller shared initialisation
    {
    }
    public function index()
    {
        $var = __h(DemoBusiness::_()->foo());          // business supplies the data, __h() escapes it
        Helper::Show(get_defined_vars(), 'main');       // hand it to the view
    }
}
```

`Base` is the project's own controller base class (`skeleton/src/Controller/Base.php`), which is nothing but [`use SingletonTrait;`](../reference/Foundation-SingletonTrait.md) — **so that `MainController::_()` can be used as a singleton**.

## How it works

### 1. A controller does three things

1. **Read input**: take parameters from the request (`Helper::GET()`/`POST()`/`REQUEST()`, route parameters via `Helper::Parameter()`);
2. **Call business**: tidy the parameters up and hand them to Business (the next chapters all cover this line: [Chapter 2-7 Databases](database.md), [Chapter 2-8 The model layer](model.md), [Chapter 2-9 Helper](helper.md));
3. **Produce output**: hand the result to a view / JSON / a redirect.

Business rules, SQL and permission checks do not belong to this layer (for what a layer violation costs you, see the violation matrix in [Chapter 2-1](layers.md)).

### 2. Classes and base classes

| Base class | Location | Purpose |
|---|---|---|
| [`DuckPhp\Foundation\Controller\Base`](../reference/Foundation-Controller-Base.md) | `src/Foundation/Controller/Base.php` | The minimal one: it only `use SingletonTrait` |
| [`DuckPhp\Foundation\Controller\ActionBase`](../reference/Foundation-Controller-ActionBase.md) | the same directory | The base class for Actions |
| [`DuckPhp\Foundation\Controller\UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md) | the same directory | Front-end user controllers ([Chapter 2-19](user.md)) |
| [`DuckPhp\Foundation\Controller\AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) | the same directory | Back-office admin controllers ([Chapter 2-20](admin.md)) |

The method-name prefix is decided by `controller_method_prefix` (default `''`). `demo/src/System/App.php` sets `'action_'`, so demo methods are written `action_login()`; the `skeleton/` set configures no prefix, so they are `index()`. **Change the prefix and the URLs stay the same, but the controller method names must follow**.

### 3. Reading input

| What you want            | How to write it                                                                                          | Notes                                                    |
| --------------- | ------------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| GET parameters          | `Helper::GET('id')` / `Helper::GET()` (all of them)                                                   | Underneath it is [`SuperGlobal`](../reference/Core-SuperGlobal.md), which tests can replace                              |
| POST parameters         | `Helper::POST('name')`                                                                      | The same                                                    |
| GET+POST merged     | `Helper::REQUEST('q')`                                                                      | With PHP's `$_REQUEST` semantics                                |
| Cookie / Server | `Helper::COOKIE('k')` / `Helper::SERVER('HTTP_HOST')`                                       |                                                       |
| **Route parameters**        | `Helper::Parameter('id')`                                                                   | Parameters captured by route maps: `{id}`, the `*` wildcard, regex groups ([Chapter 2-3](routing.md)) |
| Request type          | `Helper::IsPost()` / `Helper::IsAjax()`                                                     |                                                       |
| Current route information          | `Helper::getRouteCallingClass()` / `Helper::getRouteCallingMethod()` / `Helper::PathInfo()` | Common in troubleshooting and logs                                               |

> Do not read `$_GET`/`$_POST` directly in a controller: the whole point of the `SuperGlobal` wrapper is that "the data source can be replaced under CLI, tests and daemons".

### 4. The four ways to output

| Way           | How to write it                                             | Use it for                                |
| ------------ | ---------------------------------------------- | --------------------------------- |
| **① A view**     | `Helper::Show($data, $view)`                   | Ordinary HTML pages; when `$view` is omitted the current route path is the view name |
| **② Render to a string** | `$html = Helper::Render('mail/body', $data);`  | Email bodies, fragment caching, further processing before output                |
| **③ JSON**   | `Helper::ShowJson($data)` (may take `$flags`)         | APIs                                |
| **④ Output directly**   | `echo` / `Helper::header()` / `Helper::exit()` | Minimal APIs, streaming, health checks                    |

Two more "exit" actions:

```php
Helper::Show302('/user/login');   // a 302 redirect (remember to build site URLs with Helper::Url())
Helper::Show404();                // end with a 404 straight away (the framework's default 404 machinery is Chapter 2-12)
```

### 5. How data reaches the view

Three ways, chosen by scenario:

```php
Helper::Show(get_defined_vars(), 'main');     // (1) hand every variable in the current scope to the view (the skeleton idiom)
Helper::Show(['title' => $t], 'main');        // (2) an explicit array
Helper::assignViewData('site_name', 'MyProj'); // (3) a preset variable, carried by every later Show
```

The header/footer (layout) is set with `Helper::setViewHeaderFooter('header', 'footer')`, usually in the controller's `__construct()` — which is exactly what `MainController::__construct()` in `demo/public/demo.php` does (the render order is header → view → footer; see [Chapter 2-6](views.md)).

### 6. Actions: reusing "orchestration" between controllers

When two controllers need the same sequence (read input → call several Business classes → store in the session), extract it into an Action instead of **making the controllers inherit from each other**:

```php
namespace MyProj\Controller;

class NoteAction extends ActionBase      // calls only Business and Session, never a Model directly
{
    public function save(array $post): array
    {
        $note = NoteBusiness::_()->save($post);
        Session::_()->setLastNoteId((int)$note['id']);   // public methods wrapped in the session class (Chapter 2-11)
        return $note;
    }
}
```

The controller keeps only input and output: `$note = NoteAction::_()->save(Helper::POST());` (layering rules in [Chapter 2-1](layers.md); a standard action such as login needs no Action of your own — use `Helper::User()->login()` from [Chapter 2-19](user.md)).

## Common patterns

**① Form submission with validation errors echoed back**

```php
public function create()
{
    $post = Helper::POST();
    if (!Helper::IsPost()) {
        Helper::Show([], 'note/form');       // GET: render the form only
        return;
    }
    $errors = Helper::Validator()->init([
        'title' => 'required|maxLen:64',
    ])->valid($post);
    if ($errors) {
        Helper::Show(['errors' => $errors, 'post' => $post], 'note/form');  // echo the input back
        return;
    }
    $id = NoteBusiness::_()->create($post);
    Helper::Show302(Helper::Url('note/show?id=' . $id));
}
```

(The three validation styles are in [Chapter 2-10](validator.md).)

**② A JSON API**

```php
public function list()
{
    $page = (int)Helper::GET('page', 1);
    Helper::ShowJson(['code' => 0, 'data' => NoteBusiness::_()->paginate($page)]);
}
```

**③ One method serving both Ajax and a page**

```php
public function show()
{
    $note = NoteBusiness::_()->get((int)Helper::GET('id'));
    if (Helper::IsAjax()) {
        Helper::ShowJson($note);
        return;
    }
    Helper::Show(get_defined_vars(), 'note/show');
}
```

**④ Taking a resource id from a route parameter (not the query string)**

```php
// route: '/note/{id:\d+}' => '...\NoteController@show' (Chapter 2-3)
public function show()
{
    $id = (int)Helper::Parameter('id');       // <- the parameter captured by the route
    // ...
}
```

**⑤ Emails/fragments: render first, process after**

```php
$body = Helper::Render('mail/welcome', ['user' => $user]);
Mailer::_()->send($user['email'], 'Welcome', $body);
```

## Common errors

| Symptom                                         | Cause                                          | Fix                                                |
| ------------------------------------------ | ------------------------------------------- | ------------------------------------------------- |
| `Helper::Show('main', $data)` cannot find the view / blank page   | The arguments are swapped: the real signature is `Show($data = [], $view = '')` | Data first, view name second; the idiom is `Show(get_defined_vars(), 'main')`  |
| `$var` is undefined in the view                             | The variable never entered `Show()`'s data                          | Give it explicitly with `get_defined_vars()` or `assignViewData()`   |
| Everything 404s after changing `controller_method_prefix`      | URLs are unchanged, but method names need the prefix                             | Add the prefix to method names (e.g. `action_index`), or set the prefix back to `''`              |
| `Helper::Parameter('id')` cannot see `?id=1`      | It only provides **route-captured** parameters                              | Use `Helper::GET('id')` for the query string                          |
| A controller does `DemoModel::_()->...` to query the database              | A layer violation: it skipped the business layer                                   | Move it into Business ([Chapter 2-1](layers.md))                   |
| `Helper::Show302('/user/login')` redirects wrongly after deploying into a subdirectory | A hand-written absolute path                                      | Use `Helper::Url('user/login')`                     |
| `Helper::header()` after `echo` reports "output already sent"   | Output has already started                                      | Send headers before output; or use [`Runtime`](../reference/Core-Runtime.md)'s output buffering ([Chapter 2-2](lifecycle.md)) |
| `exit()` inside a controller method breaks tests/CLI                | It ends the process outright                                      | Use `Helper::exit()` (the replaceable system wrapper) or return normally          |

## Next steps

- [Chapter 2-6 Views and templates](views.md): how view files are located, the header/footer, escaping.
- [Chapter 2-10 Forms and data validation](validator.md): the three validation styles.
- [Chapter 2-19 Using the user system](user.md) / [Chapter 2-20 Using the admin system](admin.md): `UserControllerBase` / `AdminControllerBase` and Actions.
- Reference manual: [DuckPhp\Foundation\Controller\ControllerHelper](../reference/Foundation-Controller-ControllerHelper.md), [DuckPhp\Foundation\Controller\Base](../reference/Foundation-Controller-Base.md), [DuckPhp\Core\SuperGlobal](../reference/Core-SuperGlobal.md).
