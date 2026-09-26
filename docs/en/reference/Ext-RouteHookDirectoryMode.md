# DuckPhp\Ext\RouteHookDirectoryMode

## Introduction

`RouteHookDirectoryMode` implements "directory/file mode" routing: it makes a URL locate the controller by "a real .php file under the document root" (`/Foo/Bar.php/act` → controller `Foo/Bar`, action `act`) instead of pure PATH_INFO namespace mapping. It hangs off `prepend-outter` and takes over URL generation as well (`setUrlHandler`), so that `__url()` emits paths carrying `.php`.

Good for: the traditional site style where PHP files are the entry points and directories are namespaces.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class RouteHookDirectoryMode extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Meaning |
|---|---|---|
| `mode_dir_basepath` | `''` | The site root directory (used to convert file paths into controller paths). |

## Usage

```php
\DuckPhp\Ext\RouteHookDirectoryMode::_()->init([
    'mode_dir_basepath' => __DIR__ . '/',
], $app);

// request /admin/User.php/edit → PATH_INFO is converted to something like admin/User/edit
// afterwards __url('admin/User/edit') generates /admin/User.php/edit
```

## Caveats

- `adjustPathinfo()`: takes `DOCUMENT_ROOT + REQUEST_URI`, subtracts `basepath` to get the path, and treats the `.php` segment as the controller file (dropping `.php`); when no further segments follow it appends `index`.
- `onUrl()` (as the URL handler): tries to match a `.php` file that really exists against the URL path, and on a hit generates the new URL `base_url + class path.php[/action]`, keeping the original query parameters.
- `_Hook` writes the converted PATH_INFO back into `Route::PathInfo()` and returns `false` (so the rest of route processing continues).

## Methods

### Public methods

    public static function Url($url = null)
The static URL-generation entry point, forwarding to `onUrl`.

    public function onUrl(?string $url = null): ?string
Converts an application URL into a "directory mode" URL (carrying `.php`, matching a real file).

    public static function Hook($path_info)
The static hook entry point, forwarding to `_Hook`.

    public function _Hook(string $path_info): bool
Converts PATH_INFO (directory/file → controller path), writes it back into Route and returns `false`.

### Protected methods

    protected function initOptions(array $options): void
Reads `mode_dir_basepath`.

    protected function initContext(object $context): void
Hangs `Hook` on `prepend-outter` and sets `Url` as Route's URL handler.

    protected function adjustPathinfo(string $basepath, string $path_info): string
Converts the request URL into a controller-style PATH_INFO (handling the `.php` segment and the index completion).

## Related links

- [DuckPhp\Core\Route](Core-Route.md) — the hook and URL-generation host
