# DuckPhp\Component\RouteHookResource

A hook component that lets Route intercept and directly emit static resources (res); it also ships the `cloneResource` utility that copies `res/` into the publishing directory (document root).

## Introduction

`RouteHookResource extends ComponentBase` has two sides:

1. As a route hook (`initContext` attaches an `append-outter` `Hook` to `Route` when a resource prefix is detected): if a `path_info` falls under the resource prefix and maps to a real file `res/{file}` (and is not `.php`, and contains no `..`), it sends the file's "mime + content" directly and `return true` (hit). This is how static resources from the framework-internal res directory get "published through DuckPhp as well";
2. `cloneResource($force,&$info)`: a console helper — deploys/copies the `res/` content into the resource-prefix target directory under the document root (so a real static server can serve it without the framework), backed by a set of small protected tools for recursive copy / mkdir / overwrite protection.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class RouteHookResource extends ComponentBase`
- Delivery surface: resource URLs can be relative (e.g. point at a CDN/subdirectory via `controller_resource_prefix`, or leave empty for the default res-relative form).

## Options

`RouteHookResource::$options`:

| Option | Default | Description |
|---|---|---|
| `path` | `''` | Project root path (base for relative paths). |
| `path_resource` | `'res'` | Resource source directory (default `res`). |
| `path_document` | `'public'` | Publishing root directory name (clone target). |
| `controller_url_prefix` | null | Route resource URL prefix segment (optional). |
| `controller_resource_prefix` | `''` | Access prefix (e.g. `res/` or `//cdn/…`); decides hook/clone behavior. |

## Usage

When mounting routes, `RouteHookResource` is usually paired with `controller_resource_prefix`/CDN choices. If you want static files emitted without relying on an extra web server, point the URL/prefix at the local location and enable the hook, then visit `…/{prefix}x.png`:

```php
RouteHookResource::_()->init([
    'path' => '',
    'path_resource' => 'res',
    'controller_resource_prefix' => 'res/',
], App::_());
// afterwards GET /res/logo.png -> read from <path>/res/logo.png and output with mimeHeader
```

One-stop deployment (push the res content to the matching place under the doc root, avoiding manual diffs):

```php
RouteHookResource::_()->init([], App::_());
RouteHookResource::_()->cloneResource();   // force=false; existing files are not overwritten
RouteHookResource::_()->cloneResource(true, $info); // force overwrite; $info collects the copy log
```

## Caveats

- Only serves "real files that exist"; `.php` and `..` traversal return false (handed to later Route/404).
- Cloning res content avoids manual syncing; the document_root target is usually exactly where the web can read directly.
- In multi-target (CDN/remote) scenarios, when the resource prefix is `//`/`https://`, no local hooking happens — the CDN serves directly; this is decided from the prefix (see code).

## Methods

### Public methods

    public static function Hook($path_info)
Static hook entry: forwards to instance `_Hook`.

    public function _Hook($path_info)
The route hook implementation: decode, prefix check, traversal/php guard; if the file exists, output it with content-type and return true; otherwise false.

    public function cloneResource($force = false, &$info = '')
Copy the <path>/<path_resource> content (creating the corresponding prefix under docroot) into document_root; force decides whether existing files are skipped.

    protected function initContext(object $context): void (protected)
When the resource prefix is enabled, attach an `append-outter` hook to Route.

### Protected helpers (used by cloneResource)

    protected function get_dest_dir(string $path_parent, string $path): string
Create and return the target directory per path level (skip if it exists).

    protected function copy_dir($source, $dest, $force=false, &$info='')
Recursively copy files from source into dest; with force=false an existing file aborts and outputs `File Exsits`.

    protected function check_files_exist(string $source, string $dest, array $files, string &$info): bool
Scan: true if some dest already exists.

    protected function create_directories(string $dest, array $files, string &$info): bool
Pre-create directories from the files' relative paths; returns false when mk fails.

## Related links

- [DuckPhp\Core\Route](Core-Route.md) hook append-outter
- route resources: controller_resource_prefix (see Core-Route options)
- cloning is used from the app CLI (Component/DuckPhpInstaller etc.)
