# DuckPhp\Component\RouteHookRewrite

Gives Route a "rewrite map (rewrite_map)" capability: through a (pretty front-end url → internal path) list, incoming paths are rewritten/re-targeted to their internal form before the default routing.

## Introduction

`RouteHookRewrite extends ComponentBase` mainly serves "pretty front-end URLs separated from the real internal path". Configure something like:

```php
'rewrite_map' => [
  '/news'         => '/controller/news/',
  '/user/@[0-9]+'? => ...   // (a `~` prefix marks a regex template)
]
```

Behavior:

- `init()` attaches a rewrite hook as `prepend-outter` (`RouteHookDirectoryMode` can also reuse `filteRewrite`);
- On hook, `path_info + query` is taken as the input url and run through `filteRewrite`;
  - Exact match: template == path (prefix stripped, no extra processing); on hit, swap to the target (new query merged);
  - `~regex` templates: treated as a whole-pattern regex substitution of the input path producing a new URL (`$`/numbered groups usable) — all follow the same query-parameter merging.
- On hit, `changeRouteUrl()` (saves the original GET, puts the new query into the context/global) and sets `PathInfo` to the new path for the default routing to use; it still returns false to Route (later hooks keep running).

DuckPhp's default ext already installs it.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class RouteHookRewrite extends ComponentBase`
- Usually mounted `prepend-outter`, ahead of the default routing.

## Options

`RouteHookRewrite::$options`:

| Option | Default | Description |
|---|---|---|
| `controller_url_prefix` | `''` | Optional url prefix (stripped before matching, re-added after rewriting). |
| `rewrite_map` | `[]` | Map of `match template => internal url`; templates starting with `~` are treated as regexes. |

At runtime you can also append with `assignRewrite(key/array)` and inspect with `getRewrites()`.

## Usage

```php
use DuckPhp\Component\RouteHookRewrite;
RouteHookRewrite::_()->init([
  'rewrite_map' => [
     '/about'          => '/site/about',
     '~/user/([0-9]+)' => '/user/index?id=$1',
  ],
], App::_());
// GET /about -> internal /site/about
```

## Caveats

- Rewrites happen only on exact or regex matches; a hit rewrites, otherwise this component returns false and later routing keeps running; a miss is also false (the original path stands).
- The query is preserved and merged while you rewrite (filter/new merge); on a hit the old `$_GET` is archived to `_SERVER[init_get]` and replaced with the matched url's query set, so later route-parameter code reads it consistently.
- URL matching is based on the path (which may carry a query); the leading/trailing prefix is handled per controller_url_prefix.
- DirectoryMode etc. call filteRewrite() to reuse this conversion (without giving the hook a second change).

## Methods

### Public methods

    public static function Hook($path_info)
Route entry (triggered by the hook attached to Route): static shell, forwards to instance `doHook`.

    public function assignRewrite($key, $value = null)
Add one rewrite (or batch-add with an array) to the internal `rewrite_map`.

    public function getRewrites(): array
Returns the current rewrite map.

    public function replaceRegexUrl($input_url, $template_url, $new_url)
Regex substitution for templates starting with `~`: rewrites the input's path and merges the query; returns `null` on no match.

    public function replaceNormalUrl($input_url, $template_url, $new_url)
Templates without `~`: hits only when the paths are fully equal; builds `new_path` and merges the query.

    public function filteRewrite($input_url)
Tries each entry in turn (normal first, then regex), returning the first hit or `null`.

### Protected methods

    protected function initOptions(array $options): void
Merge the `rewrite_map` option into the internal map.

    protected function initContext(object $context): void
Attach a `prepend-outter` hook (`[static::class,'Hook']`) to `Route`.

    protected function changeRouteUrl(string $url): void
Save the old GET into `init_get` and set the new query (context/global).

    protected function doHook(string $path_info): ?bool
Trim the prefix, build the url from the input query → `filteRewrite`; on hit, change the URL + `PathInfo` and return `false` (routing continues).


## Related links

- [DuckPhp\Core\Route](Core-Route.md) (hook mounting)
- [DuckPhp\Component\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) (calls filteRewrite)
