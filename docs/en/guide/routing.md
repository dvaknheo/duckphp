# 2-3 Routing in depth

> What this solves: how a URL turns into "some controller method"; where the parameters come from; how to rewrite old links and how to bind a URL to a given Class@method; and how multi-application prefixes take part in matching.
> Prerequisites: [Chapter 2-1 The four-layer architecture and calling rules](layers.md). About 20 minutes.
> Examples: `demo/public/demo.php` (a real route: the URL `about/me` → `aboutController::me()`). How to run it:

```bash
php -S 127.0.0.1:8080 -t demo/public
# open http://127.0.0.1:8080/demo.php and click "go to about/me" on the page
```

## Minimal example

These two pieces of code really exist in `demo/public/demo.php`, and together they form the smallest complete routing loop:

```php
namespace MySpace\Controller
{
    class MainController
    {
        public function index()
        {
            $url_about = __url('about/me');   // generate a URL
            Helper::Show(get_defined_vars(), 'main_view');
        }
    }
    class aboutController
    {
        public function me()                  // the last segment of the URL is the method name
        {
            Helper::Show(get_defined_vars()); // no view name → the current route path is the view name (about/me)
        }
    }
}
```

When you visit `/about/me`: `about/me` is split into "class path `about` + method `me`" → the class name is assembled as `MySpace\Controller\aboutController` (the `Controller` suffix is added automatically) → `me()` is called. With no view name, the view name is the route path `about/me` (that is, the view file `view/about/me.php`).

## How it works

### 1. The default URL → Class@method rules

The behaviour of [`Route::pathToClassAndMethod()`](../reference/Core-Route.md) and `adjustClassBaseName()` fits in one table (`namespace_controller` defaults to `Controller`, `controller_class_postfix` to `Controller`, `controller_welcome_class` to `Main`, and `controller_welcome_method` to `index`):

| URL (PATH_INFO) | How it is split | Where it lands |
|---|---|---|
| `/` (empty) | lands on the welcome class | `Controller\MainController::index()` |
| `/about` | a single segment → that segment is **a method of the welcome class** | `Controller\MainController::about()` |
| `/about/me` | the last segment is the method, what precedes it is the class path | `Controller\aboutController::me()` |
| `/test/done` | the same | `Controller\testController::done()` |
| `/admin/user/list` | the class path may have several levels | `Controller\admin\userController::list()` |

A few switches (written in the application options):

```php
$options = [
    'namespace_controller' => 'Controller',      // the sub-namespace the controllers live in (the default)
    'controller_class_postfix' => 'Controller',  // class-name suffix, added automatically by default
    'controller_method_prefix' => 'action_',     // method prefix (this is how demo/src/System/App.php configures it)
    'controller_welcome_class' => 'Main',        // the welcome class
    'controller_welcome_method' => 'index',      // the welcome method
    'controller_welcome_class_visible' => false, // when false, explicit addresses such as /Main/xxx are rejected (E009)
    'controller_path_ext' => '',                 // set it when you need a suffix such as .html (a mismatch is E008)
    'controller_class_adjust' => '',             // extra adjustments, e.g. 'uc_method;uc_class'
    'controller_class_map' => [                  // swap out the implementation of one controller outright
        'MyProj\Controller\UserController' => 'MyProj\Controller\UserControllerV2',
    ],
];
```

`controller_class_map` can also be appended at runtime with `Helper::replaceController($old, $new)` — it is one of the cornerstones of the overriding mechanism ([Chapter 3-5](overriding.md)).

### 2. Where PATH_INFO comes from

The framework always reads it through `Route::PathInfo()`, whose actual source is `$_SERVER['PATH_INFO']`. It handles two real-world problems for you:

- **Nginx/PHP-FPM has no PATH_INFO by default**: pass it as `?_r=about/me` instead, by turning on the [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) extension (see "Common patterns ②").
- **The PHP built-in server**: `controller_fix_mistake_path_info` (default `true`) recovers the path from `REQUEST_URI` when `SCRIPT_NAME === '/index.php'` and PATH_INFO is empty — which is why things just work under `php -S` ([Chapter 1-7](deployment.md)).

To fake a request path under CLI or in tests: `Route::_()->PathInfo('about/me')`.

### 3. URL generation: the rules of `__url()`

`__url()` (= `Helper::Url()` = `Route::_()->Url()`) is not string concatenation; the rules are as follows (`defaultUrlHandler()`):

| What you pass in | The result |
| --- | --- |
| `'/other/app.php'` (starting with `/`) | **returned as is** (use this for cross-application/absolute paths) |
| `''` | the current basepath (under a subdirectory deployment, that subdirectory) |
| `'?page=2'` / `'#top'` | the current path plus that suffix |
| `'about/me'` | basepath + `/about/me` |

So an "other page inside the site" is written `__url('about/me')`; to emit an absolute path as is, write `__url('/res/logo.png')`, or more explicitly `__res('logo.png')` ([Chapter 3-3](static-resources.md)).

To take over URL generation completely (hooking up a CDN, custom rules): `Route::_()->url_handler` is a replaceable callback, and once it is set, `Url()` calls it directly.

### 4. Rewriting: `RouteHookRewrite`

Map "the URL the user sees" onto "the internal route", leaving the address bar unchanged:

```php
$options = [
    'ext' => [\DuckPhp\Component\RouteHookRewrite::class => true],
    'rewrite_map' => [
        '/legacy-shop' => 'shop/',            // exact match
        '~^/old/(\d+)$~' => 'article/$1',     // starting with ~ = a regex template
    ],
];
// appended at runtime
Helper::assignRewrite('/promo', 'activity/index');
```

> ⚠️ **The key must keep its leading `/`**. Inside, the hook compares `'/'.$path_info` with the template, so writing `'legacy-shop'` can never match — check here first when a rewrite does not take effect.

### 5. Route maps: `RouteHookRouteMap`

To bind a URL straight to a "Class@method" (not following the default naming rules), use a route map:

```php
$options = [
    'ext' => [\DuckPhp\Component\RouteHookRouteMap::class => true],
    'route_map_important' => [                        // matched before the default routing
        '/health'            => 'MyProj\Controller\HealthController@check',
        '/user/{id:\d+}'     => 'MyProj\Controller\UserController@show',
        '^/api/v(\d+)/ping$' => 'MyProj\Controller\ApiController@ping',
    ],
    'route_map' => [                                  // the fallback when default routing misses
        'legacy*' => 'MyProj\Controller\LegacyController@dispatch',
    ],
];
```

The matching rules (`matchRoute()`):

| Pattern form | Meaning |
|---|---|
| `health` / `/health` | exact match (the leading `/` is optional) |
| `legacy*` | prefix wildcard: the rest of the path is split on `/` into parameters passed to the callback |
| `^/api/v(\d+)/ping$` | starting with `^` = a regex; the capture groups become the parameters in order |
| `/user/{id:\d+}` | the `{name:rule}` placeholder form, compiled into a named capture group (`:rule` may be omitted, defaulting to `\w+`; `{id?}` means optional) |

Callback forms (`adjustCallback()`): `Class@method` (uses the `::_()` singleton), `Class->method` (uses `new`), or any callable; **the static string form `Class::method` is not supported**.

`route_map_important` hangs off the pre chain (`prepend-inner`) and `route_map` off the post chain (`append-outter`) — for positions and short-circuit semantics see [Chapter 2-4](route-hooks.md).

### 6. Multi-application prefixes: `controller_url_prefix`

A child application carries its own `controller_url_prefix` when it is mounted ([Chapter 3-2](mount-app.md)). The matching rule is "**the prefix must match exactly**": `pathToClassAndMethod()` compares the prefix first, and on a mismatch it fails right away and writes the reason into `route_error` (`E001`), which is what gives the parent application the chance to hand the request to another child application.

You can set it inside a single application too; the effect is "every URL of this application must carry that prefix".

### 7. Troubleshooting: why did the route not match

```php
Route::_()->getRouteError();          // the reason for the most recent failure (E001/E003/E008/E009…)
Route::_()->getRouteCallingClass();   // the class that matched
Helper::getRouteCallingMethod();      // the method that matched (available inside a controller)
Route::_()->PathInfo();               // the path the framework actually got
```

Error-code cheat sheet: `E001` prefix mismatch, `E003` the controller class does not exist (reflection failed), `E008` the path suffix does not satisfy `controller_path_ext`, `E009` an explicit address named the invisible welcome class.

## Common patterns

**① Clean URLs via route maps, backwards compatibility via rewrites**

```php
Helper::assignRewrite('/p/42', 'product/show?id=42');                  // old link → new route
Helper::assignImportantRoute('/product/{id:\d+}', 'MyProj\Controller\ProductController@show');
Helper::assignRoute('sitemap*', 'MyProj\Controller\SitemapController@dispatch');
```

**② A server with no PATH_INFO: turn on the compatibility extension**

```php
$options = [
    'ext' => [\DuckPhp\Component\RouteHookPathInfoCompat::class => 'path_info_compact_enable'],
    'path_info_compact_enable' => true,
    'path_info_compact_action_key' => '_r',   // URLs look like /index.php?_r=about/me
];
```

**③ Swap out one controller implementation without touching the original file**

```php
Helper::replaceController(
    \MyProj\Controller\UserController::class,
    \MyProj\Controller\UserControllerEx::class
);
```

**④ Always generate URLs, never hand-write the strings**

```php
// in a view
<a href="<?=__url('about/me')?>">About</a>
// in code
Helper::Show302(Helper::Url('user/login'));
```

Under a subdirectory deployment, a hand-written `/about/me` 404s (the subdirectory prefix is missing), while `__url('about/me')` adds it automatically.

## Common errors

| Symptom | Cause | Fix |
| --- | --- | --- |
| `assignRewrite('legacy', …)` never takes effect | The key is missing its leading `/`, while the hook compares `'/'.$path_info` | Write `'/legacy'` |
| Visiting `/about` reports a missing class | A single-segment path is treated as **a method of the welcome class**, not a controller | A controller needs at least two segments: `/about/me`; for a single-segment need use `route_map` |
| `/Main/index` is rejected (E009) | `controller_welcome_class_visible` defaults to `false` | Visit the welcome page as `/`; set it to `true` if you really need the explicit path |
| `Class::method` in a route map does not work | The `::` form is **not supported** | Use `Class@method` (`::_()`) or `Class->method` (`new`) |
| A rule in the ordinary `route_map` loses to default routing | The positions differ: important runs **before** default routing, the ordinary map is the **fallback** | Put what must match first into `route_map_important` |
| A child application returns 404 with error code E001 | The URL does not carry the child application's `controller_url_prefix` | Add the prefix to the URL, or adjust the child application's configuration ([Chapter 3-2](mount-app.md)) |
| All in-site links 404 after deploying into a subdirectory | A `/xxx` absolute path was hand-written | Always generate them with `__url()`/`Helper::Url()` |
| After enabling `_r=` compatibility mode, every original PATH_INFO route stops working | In compatibility mode the path is read from the query string instead | Only enable it on servers without PATH_INFO ([Chapter 1-7](deployment.md)) |

## Next steps

- [Chapter 2-5 Controllers](controllers.md): once a route matches, how to write the controller.
- [Chapter 2-4 Route hooks](route-hooks.md): hook positions, short-circuit semantics, and the complete "who matches first" order.
- [Chapter 3-5 Rewriting and overriding](overriding.md): the whole overriding mechanism behind `controller_class_map`.
- Reference manual: [DuckPhp\Core\Route](../reference/Core-Route.md), [DuckPhp\Component\RouteHookRewrite](../reference/Component-RouteHookRewrite.md), [DuckPhp\Component\RouteHookRouteMap](../reference/Component-RouteHookRouteMap.md), [DuckPhp\Component\RouteHookPathInfoCompat](../reference/Component-RouteHookPathInfoCompat.md).
