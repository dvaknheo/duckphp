# 3-5 Overriding and replacement

> What this solves: how to swap a mounted application's views, config, resources, controllers — even URLs — **without changing a single line of its code**.
> Prerequisites: [Chapter 3-2](mount-app.md), [Chapter 3-4](component-sharing.md). About 20 minutes.
> Examples: `tests/data_for_tests/ZThirdDemo` — both the overridden and the non-overridden paths are asserted by `ZThirdDemoTest.php`.

## Five levels, one table

| Level      | Means                                              | What it overrides              | Where in this volume                                                             |
| ------- | ----------------------------------------------- | ---------------- | ------------------------------------------------------------------ |
| **File level** | The parent app creates a subdirectory named after the child app's name                            | The child app's views / config / resources | `view/shop/index.php`, `config/shop/greet.php`, `res/shop/third.css` |
| **Class level**  | `controller_class_map` (the parent app can inject it into the child app)             | The implementation of one controller class        | `src/Override/ShopControllerOverride.php`                          |
| **Route level** | [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md) / [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md)        | Where a URL points          | the `/legacy-shop` entry in `MainApp::onInit()`                              |
| **View level** | `__use_logined_view_data` (plus `__use_logined_header_footer_file`) view data; the base class sets it automatically     | How `_Show()` renders  | Chapter 2-19                                                             |
| **Component level** | The `ext` table (`true` / array / `'@method'` / an option key / `EXT_*`) | How components and extensions are assembled         | Chapter 4-2                                                             |

## File-level override: first, who wins

The file lookup order is decided by `getOverrideableFile()`, which walks the phase chain **from the root outwards**:

```
current phase ':shop' -> explode(':') = ['', 'shop'], so it tries in order:
  1. root phase + name suffix:  <parent app path>/<path_sub>/shop/<file>   <- the parent app wins if it puts it here
  2. itself (child phase):      <child app path>/<path_sub>/<file>         <- used when the parent app has none
```

So the rule in one sentence: **the parent app creates a subdirectory named after the child app's name in its own directory and drops the same-named file in — that overrides the child's copy**; if it does not, the child app's own file is used.

The three `path_sub` mappings:

| What it overrides                          | Put it in the parent app as                  | It overrides the child app's           |
| ----------------------------- | ----------------------- | ---------------- |
| Views                            | `view/<name>/<view name>.php` | `view/<view name>.php` |
| Config ([`Configer`](../reference/Component-Configer.md), file name is `<name>.php`) | `config/<name>/<name>.php` | `config/<name>.php` |
| Resources ([`RouteHookResource`](../reference/Component-RouteHookResource.md))       | `res/<name>/<file>`       | `res/<file>`       |

Measured (ZThirdDemo, the child app has `name => 'shop'`):

```
GET /shop/            -> PARENT-OVERRIDE-VIEW index   <- the parent app's view/shop/index.php wins
GET /shop/native      -> CHILD-OWN-VIEW native        <- nobody overrides it, the child app's own view is used
the visit action reads config -> own_config=child-own-config  <- only greet was overridden, this one is still the child's
                        greet=parent-greet            <- the parent app's config/shop/greet.php wins
GET /shop/res/third.css  -> parent-overridden-third-css  <- the parent app's res/shop/third.css wins
GET /shop/res/native.css -> child-owned-native-css    <- the child app's own file
```

**Troubleshooting**: to find out which file a lookup actually hit, use the query with `$must_exist` (Chapter 1-7 covers that argument):

```php
App::_()->getOverrideableFile('view', 'index.php', true, true);   // returns the path on a hit, null otherwise
App::_()->getConfigFile('greet.php', true);
```

## Class-level override: replace a controller implementation

Inject a mapping into the child app from the parent app's `app` option (**without touching any of the child app's files**):

```php
ThirdApp::class => [
    'name' => 'shop',
    'controller_url_prefix' => 'shop/',
    'controller_class_map' => [
        ThirdMainController::class => ShopControllerOverride::class,
    ],
],
```

```php
// ZThirdDemo/src/Override/ShopControllerOverride.php
namespace ZThirdDemo\Override;

class ShopControllerOverride extends MainController   // extends its original controller
{
    public function index()
    {
        // call parent::index() first if you want to keep the original logic; here we give new behaviour outright
        Helper::Show([...], 'index');
    }
}
```

Key points:
- the mapping happens at **route dispatch** time ([`Route::getRouteCallback()`](../reference/Core-Route.md)), so the URL is unchanged and only the class that runs differs;
- the override class is **deliberately kept outside `Controller/`**, otherwise it would itself be treated as one of the parent app's controllers and gain an extra route;
- the same trick replaces a child app's business class (`ShopBusiness` → your subclass), as long as it takes effect where that class is fetched with `::_()`.

## Route-level override: changing where a URL points

```php
// ZThirdDemo/src/System/MainApp.php :: onInit()
RouteHookRewrite::_()->assignRewrite('/legacy-shop', 'shop/');
```

Measured: `GET /legacy-shop` renders the child app's home page (that is, the view the parent app overrode).

> ⚠️ **Keys in the rewrite table must carry a leading `/`**: internally the hook compares `'/'.$path_info` against your key. Writing `'legacy-shop'` never matches (an easy trap; `Helper::assignRewrite()` behaves the same).
>
> Another common use is binding a URL straight to "class@method": `RouteHookRouteMap::_()->assignRoute($url, $callback)`, and for important routes `assignImportantRoute()`; they take effect before route matching (Chapter 2-3).

## View-level override: changing how it renders, plus header/footer

```php
// the switches are view data, not app options; set them in your own controller base
Helper::assignViewData('__use_logined_view_data', true);          // _Show takes the "post-login view" branch
Helper::assignViewData('__use_logined_header_footer_file', true);  // and wraps the header/footer views around it
```

> When you extend `Foundation\Controller\UserControllerBase` / `AdminControllerBase` these two lines are already done for you.

The condition for a hit is "the current route's controller implements [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md) / [`UserControllerInterface`](../reference/GlobalUser-UserControllerInterface.md)"; the header/footer files are named by `globaladmin_view_file_header/footer` and `globaluser_view_file_header/footer` (their values are view names relative to `<app path>/view/`, and they are **phase-overridable**, so a third app can replace the back office's header and footer too). Both the switch test and the rendering live in [`DuckPhp::_Show()`](../reference/DuckPhp.md): it reads `__use_logined_view_data` first, and on an interface hit calls `mergeViewData()` on [`Admin::_()`](../reference/GlobalAdmin-Admin.md) / [`User::_()`](../reference/GlobalUser-User.md) to inject `__logined_*` and render the header/footer, finally calling `View::setViewHeaderFooter()` according to `__use_logined_header_footer_file`. An empty view name is filled in by `App::_Show()` with the current route path; to **skip only** the header/footer while still injecting `__logined_*`, set the view data `__logined_render_header_footer` to `false`.

## Component-level override: changing the assembly

```php
'ext' => [
    JsonView::class => true,                     // switch it on
    RouteHookRewrite::class => '@myRewriteOptions',   // take the options from a method of this app
    PermissionMenu::class => 'permission_menu_on',    // decide with the value of an option (on/off/config)
    DbManager::class => App::EXT_SKIP_INIT,       // instantiate only, do not init
    Logger::class => App::EXT_FOLLOW_APP,         // initialise following the app options
],
```

For the `EXT_*` constants and the value shapes see [Chapter 4-2 Developing components and extensions](custom-component.md) and the [reference manual](../reference/Core-KernelTrait.md).

## Precedence and conflict hunting

1. **The same resource within one phase chain**: the first hit wins, in the order "the parent app's `<name>` subdirectory → the child app itself".
2. **Same-named directories merging within one layer** (a menu tree, say): the **first creator's** `url`/`icon` wins (see the caveats in [Ext-PermissionMenu](../reference/Ext-PermissionMenu.md)).
3. **Route hooks** are ordered by registration position: outer pre → inner pre → default route → inner post → outer post.
4. Tools: [`PhaseContainer::_()->dumpAllObject()`](../reference/Core-PhaseContainer.md) (which instances exist in a phase), `getOverrideableFile(..., true)` (which file a lookup hit), [`RouteLister::_()->listAll()`](../reference/Ext-RouteLister.md) (which routes exist now, Chapter 2-3).

## The price of overriding: three rules of discipline

- **Do not edit `vendor/` or the mounted app's source**: every override entry point lives in the parent app's options and its own directories, so an upstream upgrade is a directory swap.
- **Leave a trace**: keep one central comment/list in the parent app (which file overrides whose what), otherwise a year later nobody knows why the child app's view "looks wrong".
- **Re-run after upgrades**: if the other side renames a view variable or config key, your override files do not follow automatically — put the smoke checklist from Chapter 3-2 (the `ZThirdDemoTest` assertions) into CI.

## Next steps

- [Chapter 3-6 The installer and the web install flow](installer.md)
- [Chapter 4-2 Developing components and extensions](custom-component.md): the `ext` table and the `EXT_*` constants
