# DuckPhp\Ext\RouteLister

Route enumeration: it organises the current application's route set (Rewrite / the important map / automatic controller routing / the ordinary map, plus child applications) into one uniform set of records — for permission export and route display (such as the CLI `routes`).

## Introduction

`RouteLister extends ComponentBase` provides the ability to "list every URL the system can answer", outputting rows of this shape:

```php
['url','phase','controller','method',
 'is_admin','is_user',
 'route_map','route_map_important','rewrite_map'];
```

- The order is fixed: rewrite_map → route_map_important → controller methods → route_map;
- Controller URLs are derived backwards from "every controller class in the configuration-file directory + their reflected public methods" (see `pathInfoFromClassAndMethod`);
- Each row carries admin/user marks; it can be filtered with `only_controller/only_admin/only_user`;
- With `with_children` on, it recurses into the child applications' rows, and each one's phase shows up correctly too.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class RouteLister extends ComponentBase`
- The CLI display is implemented by this class's `command_routes()` (it prints the route table as highlighted coloured lines), and the CLI command of `DuckPhp\Component\Command` forwards to it.

## Options

`RouteLister::$options`:

| Option | Default | Meaning |
|---|---|---|
| `classes_to_get_controller_path` | `[]` | Extra "classes/controller files to try" candidates: used only to **locate the controller directory** (the welcome class and `path_config` are candidates too; a candidate file that does not exist means trying the next), after which the `.php` files under that directory are enumerated recursively to decide which are Controllers. |

In addition: it does not take on the judgement of "which methods are routed" (that is decided by `controller_class_postfix`, `controller_method_prefix` and `controller_class_base` according to `Route`'s configuration); it only derives the URL path for a controller. The welcome class / `Helper` / `Base` are also tried as candidates, with priority, when locating the directory.

## Usage

```php
use DuckPhp\Ext\RouteLister;
$rows = RouteLister::_()->listAll();
// CLI: for coloured output use RouteLister::_()->command_routes().
$rows_admin = RouteLister::_()->listAll(true, true, true, false);  // admin controllers only
```

## How the URL is generated

A controller's "one-line URL" is derived backwards by `pathInfoFromClassAndMethod(full name, action name)`: after the class postfix and the method prefix are handled, the first segment comes from the path `namespace_controller` names and the last segment is the action name (the welcome class/welcome method get special treatment), and the URL string is assembled at the end (carrying `controller_path_ext` and `controller_url_prefix`). (Internally the method also reverses `controller_class_adjust`.)

## Caveats

- The controller directory must be reflectable to file names (the real class must be autoloaded) for reflection to work; a row whose reflection fails returns null and is skipped.
- The `only_admin`/`only_user` filters cannot both be true (it throws `InvalidArgumentException`).
- Controller rows are always present in the list result (`only_controller` means "output rows only"), while the three route blocks (rewrite / important map / ordinary map) are skipped by `only_controller`.

## Methods

### Public methods

    public function command_routes(bool $with_children = true, bool $only_controller = false, bool $only_admin = false, bool $only_user = false): void
Prints the result of `listAll()` to the command line in blocks by URL/controller/route-map/admin-user/phase, with colours (the `command_routes` command of `Command` forwards to it).

    public function pathInfoFromClassAndMethod($class, $method, $adjuster = null)
Derives the URL this route can be written as from the controller's full name + method (the welcome class/welcome method get special treatment; it returns null when it cannot be derived). Internally it reverses `controller_class_adjust`.

    public function listAll(bool $with_children = true,
                   bool $only_controller = false,
                   bool $only_admin = false,
                   bool $only_user = false): array
The core: it assembles rewrite_important routes/rows + controller methods (filterable by admin/user), then the ordinary route_map; with with_children it recursively appends the child applications' records.

### Protected methods

    protected function doControllerClassAdjust(string $first, string $method): array
Restores the path segments: uc_method/uc_class(lcfirst of last)/uc_full_class (each segment lcfirst) and so on.

    protected function getControllerPathByApp($prefix)
Prefers the App class file's location to derive the controller directory (returns null when `$prefix` starts with `\\`; returns null when the directory does not exist).

    protected function getControllerPathByDetected($prefix)
The fallback probe: locates the controller directory by reflecting on candidate classes such as the welcome class / `Helper` / `Base` (returns null on failure).

    protected function getAllControllerClasses(): array
Determines the controller directory from the candidates (first `getControllerPathByApp`, then `getControllerPathByDetected`) and recursively scans the `.php` files in it (dropping the postfix), returning a class=>absfile map.

    protected function getControllerMethods(string $full_class, ?callable $adjuster = null): array
Reflects the public non-static non-constructor methods, reverses them into URLs, and skips those whose pathInfo returns null.

    protected function listControllerRows(bool $only_admin, bool $only_user): array
Walks the classes, filters by interface membership, and synthesises one record per method.

    protected function parseRouteMapCallback(string $callback): array
Splits a route_map target `~Class@method`/`Class@method` into [class,method].

    protected function isSubclassOf(string $class, string $interface): bool
Reflectively decides whether it is a subclass implementing the interface (catching reflection exceptions → false).

## Related links

- The command-side outputter: [DuckPhp\Component\Command](Component-Command.md)
- The two route-map components that are the data source: Rewrite / RouteMap (see the reference in this directory)
- User/admin determination: the GlobalUser / GlobalAdmin interfaces
