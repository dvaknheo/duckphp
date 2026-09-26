# DuckPhp\Ext\PermissionMenu

## Introduction

`PermissionMenu` is the builder of the "back-office permission menu tree": it uses [DuckPhp\Ext\RouteLister](Ext-RouteLister.md) to scan out the routes of **back-office controllers**, then organises them into a multi-level menu tree according to the annotations on the controller classes/methods (or metadata the class supplies itself), for the admin back end to render its sidebar and permission points.

Three ways to use it:

- **Annotation mode**: write annotations such as `@menu_directory`, `@menu`, `@menu_action`, `@menu_permission` on controller classes/methods and `build()` parses them into a menu tree;
- **Metadata mode**: a controller implements [DuckPhp\Ext\PermissionMenuMetaInterface](Ext-PermissionMenuMetaInterface.md) (`__permissionMenuMeta()` returns an array); that class's annotations are no longer parsed and the whole table is taken over by it;
- **Persistence mode**: `buildAndSaveToConfigJsonFile()` writes the tree into a configuration file and `loadAdminPermissionMenu()` reads it back, avoiding a route scan + reflection on every request.

It also works with `App::Phase()` / [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md): `loadAll()` merges the menus of the root application and of each child application into one whole tree.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class PermissionMenu extends DuckPhp\Core\ComponentBase`
- This class **has no `$options` of its own** (it follows `ComponentBase`'s empty table), so this page has no "Options" section: what it reads are **the options of the owning App**, `permission_menu_tree_for_admin` and `controller_url_prefix` (and `app`, `path_config` and so on, which the App itself consumes).
- The singleton and initialisation abilities such as `_()` / `init()` / `context()` are provided by `ComponentBase` and are not repeated here.

## Usage

```php
// 1) rebuild the menu from the annotations and persist it (run once from a back-office "rebuild menu" button or a CLI command)
\DuckPhp\Ext\PermissionMenu::_()->buildAndSaveToConfigJsonFile();

// 2) fetch the tree at runtime: with a menu file configured it reads the file (and adds the mounting prefix); without one it builds on the spot
$tree = \DuckPhp\Ext\PermissionMenu::_()->loadAdminPermissionMenu();
$tree = \DuckPhp\Ext\PermissionMenu::_()->loadAdminPermissionMenu(true); // force an immediate build

// 3) merge the child applications in as well (calling it under a child application's Phase is safe too; the Phase is restored before returning)
$all = \DuckPhp\Ext\PermissionMenu::_()->loadAll();

// 4) want only the "sidebar" shape (dropping actions/permission points and empty directories)
$side = \DuckPhp\Ext\PermissionMenu::_()->permissionMenuTreeToSideMenuTree($all);
```

The minimal annotation-mode form:

```php
use DuckPhp\GlobalAdmin\AdminControllerInterface;

/**
 * @menu_directory Admin\System
 * @menu_directory_url admin/system
 * @menu_icon fa fa-folder
 * @menu_weight 10
 */
class AdminController implements AdminControllerInterface
{
    /**
     * @menu Dashboard Panel
     * @menu_icon home
     * @menu_weight 5
     */
    public function action_index() { }

    /**
     * @menu_action User List
     * @menu_directory Users
     * @menu_directory_url users
     */
    public function action_list() { }

    /**
     * @menu_permission #edit Edit User
     * @menu_permission /abs/place Abs Place
     */
    public function action_edit() { }

    /** a method without annotations still becomes a type=2 action node, named after the method */
    public function action_plain() { }
}
```

Metadata mode:

```php
use DuckPhp\Ext\PermissionMenuMetaInterface;

class MetaController implements PermissionMenuMetaInterface
{
    public function __permissionMenuMeta(): array
    {
        return [
            ['name' => 'Meta Menu', 'type' => 1, 'url' => 'Meta/index', 'weight' => 3],
            ['name' => 'Meta Action'],   // type defaults to 1, url to null, weight to 0
        ];
    }
}
```

## Configuration example

```php
class MyApp extends \DuckPhp\DuckPhp
{
    public $options = [
        'namespace' => 'MyProj',

        // the menu-tree configuration file (relative to path_config, config/ by default):
        // .json goes through json_decode, any other suffix is included as a php file (which must return an array)
        // without it, every loadAdminPermissionMenu() builds on the spot
        'permission_menu_tree_for_admin' => 'menu.json',

        'ext' => [
            // optional: register it as an extension; this class has no options of its own, and PermissionMenu::_() works without registering
            \DuckPhp\Ext\PermissionMenu::class => true,
        ],
    ];
}
```

> `permission_menu_tree_for_admin` is **an App option** (this class's own `$options` is empty), so it must be declared in the application class as above; stuffing it into `App::_()->options` at runtime has no effect.

## The annotations at a glance

| Annotation | Where it may be written | What it does |
|---|---|---|
| `@menu_directory Name` | class / method | The directory name; supports `\` layering (the value runs to the end of the line and may contain spaces) |
| `@menu_directory_url Url` | class / method | The directory node's own url; at class level the default is "the directory part of the first method url + `/#`" |
| `@menu_icon Icon` | class / method | The icon name (the value runs to the end of the line, so spaced values such as `fa fa-folder` work too) |
| `@menu_weight N` | class / method | The sort weight among siblings (larger comes first; negative values allowed), removed from the output before it is written |
| `@menu Name` | method | A menu node, `type=1` |
| `@menu_action Name` | method | An action node, `type=2` |
| `@menu_permission #url Name` | method | A permission point, `type=3`; starting with `#` it is appended to the method's url, otherwise the given url is used |

Node types: `0` = directory, `1` = menu, `2` = action, `3` = permission point.

## Caveats

1. **Only back-office controllers are collected**: `getRoutes()` goes through `RouteLister::listAll(false, true, true)` (equivalent to `only_admin`), so a controller must `implements AdminControllerInterface`; ordinary controllers never appear in the menu.
2. **The two modes are mutually exclusive**: as long as a controller has `__permissionMenuMeta()` returning a non-null array, its annotations are not parsed at all; when that method throws or returns null it **falls back to annotation mode** (it is called on a "constructor-less instance", so do not depend on constructor-initialised properties inside it).
3. **An annotation value runs to the end of the line and is trimmed**: the name in `@menu_action User List` is `User List`; do not put a second argument inside the value. The class-level `@menu_directory` and `@menu_directory_url` are two independent tags and never swallow each other.
4. **Methods without annotations** also become `type=2` actions, named after the method (including `controller_method_prefix`, such as `action_orders`), with their weight forced to 0. (**A feature**: the menu name matches the method name, which makes it easy to trace the code back from the menu, so the prefix is not stripped.)
5. **`\` layering and merging**: `@menu_directory Admin\System` is split into two levels, `Admin/` → `System/`, with the leaf name taking only the last segment; directories of the same name at the same level are **merged into one node** (when merging, the `url`/`icon` of the one created first wins). The grouping directory produced by a method-level `@menu_directory` hangs at the **top level**, not inside the directory of the controller it belongs to.
6. **When a controller has no `@menu_directory`**, the directory name is the **basename of the controller class name** (`...\Controller\AdminController` → `AdminController`); only when the class name itself is empty (such as a fake route with `controller => ''` reaching `build()`) does it fall back to `NoName`. See `getDefaultDirectoryName()`.
7. **`@menu_directory_url` de-duplication**: when several entries target the same directory, **only the first is used** (one url per directory is enough).
8. **The weight only applies among siblings**: after `sortTree()` each level is re-sorted by descending `weight`, and the `weight` field is deleted from the output.
9. **The url prefix**: when `loadAdminPermissionMenu()` reads the configuration file it uses `resolveUrls($tree, '/'.controller_url_prefix)` to add the mounting prefix to nodes that have a url; whereas what `build()` / `loadAdminPermissionMenu(true)` produce are **relative urls**, and you call `resolveUrls()` yourself when needed (`buildAndSaveToConfigJsonFile()` persists the relative urls precisely so the prefix can change later).
10. **The Phase semantics of `loadAll()`**: it loads the **current Phase**'s menu first, then returns to the root Phase and recursively merges each child application, and finally restores the original Phase. Called from the **root** Phase, the root menu is loaded only once (it is not duplicated); called from a **child** Phase, that child application itself is not loaded twice, but its own child applications are still merged in. Entries in `app` that cannot be switched into (such as a `false` value, or one that never registered `__phase__`) are skipped.
11. **Permission points carry no icon** (**a feature**: it is a permission marker rather than a navigation item, so `@menu_icon` has no effect on it and its output `icon` is always `null`); `permissionMenuTreeToSideMenuTree()` keeps only the navigable nodes (**a feature**: it drops the actions and permission points of `type > 1` as well as **empty directories with no children**, and a missing `type` is treated as a directory `0`).
12. `recordsetToTree()` / `treeToRecordset()` have nothing to do with the menu itself; they are the accompanying "flat table ↔ tree" utilities: records missing the `id` field are dropped, and when `pid` points at a non-existent `id` the record is treated as a root node.
13. The `catch (\Throwable)` around `ReflectionClass` in `getClassDoc()` is an unreachable defensive branch (a `class_exists()` check has already happened above); it does not affect behaviour.

## Methods

### Public methods

    public function buildAndSaveToConfigJsonFile()
Builds the menu tree on the spot from the routes (the urls have had `controller_url_prefix` trimmed and are relative) and writes it as JSON into the configuration file named by `permission_menu_tree_for_admin`; with that option unset it does nothing.

    public function loadAdminPermissionMenu(bool $force_build = false)
Gets the back-office menu tree: with `$force_build` truthy it `build()`s on the spot and returns directly; otherwise it reads the menu configuration file (`.json` goes through `json_decode`, any other suffix is included as a php file, and a non-array degrades to an empty tree), and finally adds the mounting prefix with `resolveUrls()`.

    public function loadAll(bool $force_build = false)
Gets the menu of the "whole application tree": it takes the current Phase's menu first, then switches back to the root Phase and recursively merges each child application (skipping the current Phase itself to avoid duplication), and finally restores the original Phase.

    public function build(array $routes): array
The core method: it groups the route rows by controller, parses `__permissionMenuMeta()` or the annotations one by one, generates "directory → menu/action/permission point" nodes, then does the `\` layer split and the sibling weight sort.

    public function resolveUrls(array &$tree, string $prefix): array
Adds the mounting prefix to the nodes in the tree that have a `url` (modifying in place and returning the same tree); nodes whose `url` is `null` or `''` **stay without a url** (a prefix alone is not a valid page).

    public function walkTree(array &$nodes, callable $callback): array
Walks the whole tree depth-first, calling `$callback(&$node, $depth)` for every node (the root has depth=0 and the callback may modify the node directly), modifying in place and returning the same tree.

    public function permissionMenuTreeToSideMenuTree(array $nodes): array
Converts it into a sidebar menu tree: it recursively filters out `type > 1` (actions/permission points) and empty directories with no children, keeping `name`/`url`/`icon`/`type` and `children`.

    public function recordsetToTree(array $recordset, string $idField = 'id', string $pidField = 'pid', int $rootPid = 0): array
Turns a flat record set into a tree: it indexes by `id`, and a record whose `pid` equals `$rootPid` or points at a non-existent `id` becomes a root; records missing `id` are discarded.

    public function treeToRecordset(array $tree, string $idField = 'id', string $pidField = 'pid', int $rootPid = 0): array
Flattens the tree into a record set: each node loses `children` and gains a `pid` (the root nodes' `pid` is `$rootPid`).

### Protected methods

    protected function getMenuJsonFileConfig()
Reads `App::_()->options['permission_menu_tree_for_admin']`, returning `null` when it is not set.

    protected function mergeAppsMenus(array &$tree, string $ignore_phase, bool $force_build): void
Recursively merges the menus of the application tree: only when the current Phase differs from `$ignore_phase` does it load this application's menu into `$tree`, after which it switches into each child application in turn and recurses, returning to this Phase every time.

    protected function getRoutes(bool $trim_url = false)
Gets `RouteLister::listAll(false, true, true)`; with `$trim_url` truthy it trims the url into a relative path by the length of `App::_()->options['controller_url_prefix']`.

    protected function getDefaultDirectoryName(string $controller): string
The directory name when a controller has no `@menu_directory`: the basename of the class name (`...\Controller\AdminController` → `AdminController`); `NoName` is returned only when the class name is empty.

    protected function getClassMenuMeta(string $controller): ?array
When a controller has `__permissionMenuMeta()` it instantiates it without the constructor and calls it; when the class does not exist, the method is missing, it throws or it returns null, it returns `null` (meaning fall back to annotation mode).

    protected function processMenuMetaForController(array $meta, array $methods): array
Normalises the entries of `__permissionMenuMeta()` into nodes: `name`, `url` (`null` by default), `type` (`1` by default), `weight` (`0` by default).

    protected function collectMethodItems(string $controller, string $method, string $url): array
Parses one method's annotations: it settles the node name and `type` (`@menu` → 1, `@menu_action` → 2, and with neither the method name is used with the weight zeroed), carries `directory`/`directory_url`/`icon`/`weight`, and appends the several `@menu_permission` entries as `type=3` nodes.

    protected function parseMultiAnnotatedLine(string $doc, string $tag): array
Parses annotations that may repeat (such as several `@menu_permission`): each line is split into "the first token" and "the rest of the line, trimmed", returned line by line.

    protected function splitSubLevels(array $nodes): array
The first post-processing pass: it clears the child nodes' temporary fields (`directory`/`directory_url`), turns the grouping `directory` of methods into directory nodes, and splits and merges them into the tree by the `\` in their names.

    protected function mergeNode(array &$tree, array $parts, array $node): void
Merges a node into the tree along the `$parts` path: when a directory of the same name already exists at that level it is merged into its `children` (only the child node is merged), otherwise directories are created level by level; as a leaf, only the last segment of the name is used.

    protected function sortTree(array &$nodes): void
The second post-processing pass: after a descending `uasort` by `weight` within each level it re-indexes, recurses into the child levels, and deletes the `weight` field before output.

    protected function getClassDoc(string $class): string
Reads the class docblock; it returns `''` when the class name is empty or the class does not exist.

    protected function getMethodDoc(string $class, string $method): string
Reads the method docblock; it returns `''` when the class name is empty, the class does not exist or the method does not exist.

    protected function parseAnnotatedLine(string $doc, string $tag): ?string
Parses a single annotation: the value runs to the end of the line and is `trim`med; it returns `null` when the annotation is missing or the value is empty.

    protected function parseWeight(string $doc): int
Parses `@menu_weight N` (negative values are supported), returning `0` when that annotation is absent.

    protected function walkTreeRecursive(array &$nodes, callable $callback, int $depth): void
The recursive implementation of `walkTree()`: it calls back on the node first, then processes `children` with `depth + 1`.

    protected function treeToRecordsetRecursive(array $nodes, array &$recordset, string $idField, string $pidField, int $pid): void
The recursive implementation of `treeToRecordset()`: it writes the current node (with the passed-in `pid`), then processes the child level with this node's `id` as the `pid`.

## Related links

- [DuckPhp\Ext\RouteLister](Ext-RouteLister.md) — the route source (`listAll()`)
- [DuckPhp\Ext\PermissionMenuMetaInterface](Ext-PermissionMenuMetaInterface.md) — the metadata-mode interface
- [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) — the marker interface for back-office controllers that get collected into the menu
- [DuckPhp\Core\App](Core-App.md) — `getConfigFile()` and where the options come from
- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) — the Phase mechanism `loadAll()` depends on
- guide: [advanced-phase](../guide/advanced-phase.md)
