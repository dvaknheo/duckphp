# Routing System

DuckPHP's routing is implemented by `src/Core/Route.php`, following a **convention over configuration** design.

## Default Routing Rules

URL path format:

```
/{ControllerClassName}/{actionMethodName}
```

Mapped to controller class methods:

```
/Main/index        → Namespace\Controller\MainController::action_index()
/user/profile      → Namespace\Controller\UserController::action_profile()
/admin/user/list   → Namespace\Controller\Admin\UserController::action_list()
```

### Key Conventions

| Convention | Default Value | Description |
|---|---|---|
| Controller class suffix | `Controller` | `FooController` |
| Method prefix | `action_` | `action_index()` |
| Welcome class | `Main` | Routes `/` or `/index` to |
| Welcome method | `index` | Controller default method |
| Namespace | Auto-detect | The `Controller` segment in the project |
| Class name case adjustment | Empty | By default, `user` in URL is not converted to `User`; configure `controller_class_adjust` |

### Controller Class Name Case in URL

By default, `controller_class_adjust` is empty, and URL segments are concatenated into the class name as-is. For example:

```
/user/profile → Namespace\Controller\userController::action_profile()  # Class not found, 404
```

If controller classes use PascalCase (e.g., `UserController`), enable automatic first-letter capitalization in `App::$options`:

```php
public $options = [
    'controller_class_adjust' => 'uc_class',
];
```

After enabling:

```
/user/profile → Namespace\Controller\UserController::action_profile()
```

### Accessing Default Methods of Non-Welcome Controllers

The default welcome class is `Main`.

```php
__url('user/index');   // UserController::action_index()
__url('user/add');     // UserController::action_add()
```

### POST Method Special Handling

If the request is POST and an `action_do_{methodName}()` method exists, it is called with priority:

```
POST /user/login
→ UserController::action_do_login()   # Priority
→ UserController::action_login()      # Fallback
```

This feature is controlled by the `controller_prefix_post = 'do_'` option.

## URL Generation

Generate URLs in controllers or views:

```php
__url('')            // Current controller base URL
__url('user/login')  // /user/login
__url('?page=2')     // Current path + query parameters
__url('#section')    // Current path + anchor
__url('/absolute/path') // Absolute path

// Resource URL (when controller_resource_prefix is set)
__res('css/style.css')  // /res/css/style.css or CDN address
```
If you write `__url('user')` directly, it will be parsed as `MainController::action_user()`, not `UserController::action_index()`. To access the default page of a sub-controller, write the full path:
## Advanced Topic: Route Hook System

The routing process is串联 by hooks. Hook execution order:

```
prepend-outter → prepend-inner → default route → append-inner → append-outter
```

Built-in hooks (by registration position):

| Hook | Position | Function |
|---|---|---|
| `RouteHookCheckStatus` | prepend-outter | Check maintenance mode/installation status |
| `RouteHookRewrite` | prepend-outter | URL rewriting |
| `RouteHookRouteMap` (important) | prepend-inner | Priority route mapping matching |
| `RouteHookRouteMap` (normal) | append-outter | Normal route mapping matching |
| `RouteHookResource` | append-outter | Static resource handling |

### Adding Custom Hooks

```php
use DuckPhp\Core\Route;

// In App::onInit() or any initialization phase
Route::_()->addRouteHook(function ($path_info) {
    if ($path_info === '/special') {
        echo "Special route handled!";
        return true; // Returning true means handled, subsequent hooks will not execute
    }
    return false;
}, 'prepend-inner');
```

## URL Rewriting

Map public URLs to internal URLs via `rewrite_map`:

```php
$options = [
    'rewrite_map' => [
        'article/123' => 'blog/show?id=123',
        '~^/u/(\d+)$' => '/user/profile?id=$1',  // Regex mode, starts with ~
    ],
];
```

- Normal mode: Exact path matching
- Regex mode: Starts with `~`, uses regex matching

## Route Mapping

Bind URLs directly to callable bodies via `route_map` or `route_map_important`:

```php
$options = [
    'route_map_important' => [
        '/' => function () { echo "Home"; },
        '/hello' => 'Namespace\Controller\MainController@action_say',  // @ separates class and method
    ],
    'route_map' => [
        '/blog/{id:\d+}' => function ($params) {
            extract($params);
            echo "Blog post #$id";
        },
        '/page/*' => function ($params) {
            // * matches remaining path, $params is the path segments array
        },
        '@^/api/(\w+)$' => '~Controller\ApiController@action_$1',  // @ at the start indicates regex compilation, ~ is replaced with the controller namespace prefix
    ],
];
```

### Route Mapping Matching Rules

| Pattern | Description | Example |
|---|---|---|
| `/path` | Exact match | `/user/login` |
| `/path/*` | Prefix match, remaining path as parameter | `/blog/2024/01` |
| `@^{regex}$` | Regex match (starts with `@`) | `@^/api/(\w+)$` |
| `~Controller\Xxx` | `~` replaced with the controller namespace prefix | `~Controller\MainController` |


## Route Parameters

When route mapping matches, parameters can be retrieved via `Route::Parameter()`:

```php
// Route mapping: '/user/{id:\d+}' => 'Controller\UserController@action_show'
class UserController
{
    public function action_show()
    {
        $id = Route::Parameter('id');  // Get id parameter
        // Or
        $id = Helper::Parameter('id');
        // Or
        $id = Helper::GET('id');
    }
}
```
