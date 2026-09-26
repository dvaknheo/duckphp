# 3-3 Static resources and the document root

> What this solves: where CSS/JS/images live, what their URLs look like, how several apps avoid stepping on each other, and how to hand them to the web server in production.
> Prerequisites: [Chapter 3-2 Mounting an external application](mount-app.md). About 12 minutes.
> Examples: `tests/data_for_tests/ZThirdDemo` (the main app's `res/main.css`, the third-party app's `res/third.css` and `res/native.css`).

## Two routes: the web server serves them, or the framework does

| Way | Who sends the file | When to use it |
|---|---|---|
| **Document root direct** | nginx/Apache reads files under `public/` | Production (fastest). Put resources in `path_document` (default `public/`), or copy them in at deploy time |
| **Framework-served** | [`RouteHookResource`](../reference/Component-RouteHookResource.md) reads the file on request and outputs it | Development, when you do not want to configure rewrites; or when resources must live outside `public/`, split per app |

Framework-served mode relies on two options:

| Option | Description |
|---|---|
| `path_resource` | The resource **source directory** (default `res/`, relative to the app's own `path`) |
| `controller_resource_prefix` | Which prefix the resource URLs appear under |

## How URLs map to disk paths

The prefix is built like this (`RouteHookResource::_Hook()`):

```
prefix = '/' . controller_url_prefix . controller_resource_prefix
URL  /<prefix>/<file>   ->   <app path>/<path_resource>/<file>
```

So an app's `res/main.css` can be exposed like this:

```php
// ZThirdDemo/src/System/MainApp.php -- the root app
'controller_resource_prefix' => '/res/',     // prefix = '' + '/res/' -> URL: /res/main.css
```

```php
// ZThirdDemo/third/System/ThirdApp.php -- the mounted child app
'controller_resource_prefix' => 'res/',      // prefix = '/shop/' + 'res/' -> URL: /shop/res/third.css
```

Measured result (the three assertions in `ZThirdDemoTest.php`):

```
GET /res/main.css            -> the main app's res/main.css
GET /shop/res/native.css     -> the child app's third/res/native.css
GET /shop/res/third.css      -> the parent app's res/shop/third.css (it overrides the child app's file of the same name, see Chapter 3-5)
```

> ⚠️ **The slashes here are a real trap — follow these two rules**:
> - a root app writes `'/res/'` (with a **leading** slash) — because the requested path_info carries a leading `/` itself, and the root app's `controller_url_prefix` is the empty string;
> - a child app writes `'res/'` (**without** a leading slash) — because its `controller_url_prefix` (`'shop/'`) already ends with `/`; adding another one builds `/shop//res/`, which never matches.

> ⚠️ **These URLs hold in three environments**: nginx/Apache (rewrite sends every request to `index.php`), in-process calls (`ZThirdDemoTest` asserts them straight on `serve()`), and `php -S` **with a router script** (see [Chapter 1-7 §2](deployment.md)).
> The one exception is `php -S … -t public` **without** a router script (including the framework's `bin/cli.php run`): the built-in server does not route URIs with a suffix to `index.php`, so `/res/main.css` 404s — in development either use a router script or put the resources in `public/` for the server to serve directly.

## Organising resources with several apps

Splitting directories per app is recommended, to avoid same-name clashes:

```
public/                 <- the document root (direct serving in production, cloneResource target)
res/                    <- the main app's resource source directory
├── main.css
└── shop/               <- * a directory named after the mounted app = override its resources (Chapter 3-5)
    └── third.css
third/res/              <- the third-party app's own resources
├── third.css
└── native.css
```

Conventions:
- one URL prefix segment per app (`/res/`, `/shop/res/`); do not crowd everything onto the root;
- cache busters/versions go in the query string (`/res/main.css?v=20260918`); framework-served mode takes the file purely by path and ignores the query string;
- never put `.php` into a resource directory: `RouteHookResource` **rejects** `.php` and paths containing `../` (that is its security check).

## Going live: deploy `res/` to the document root

Production usually lets the web server serve directly; sync the resource directories to the matching place under the docroot:

```php
RouteHookResource::_()->cloneResource();        // force=false: existing files are not overwritten
RouteHookResource::_()->cloneResource(true);    // force=true: overwrite
```

Where `cloneResource()` lands is decided by those two options, and it **depends on the phase**: whichever app phase you are in, that app's resources are copied; a CDN prefix (`http://…`) is skipped.

Generate the matching URLs with the global function/Helper:

```php
__res('main.css');                 // build a resource URL from controller_resource_prefix
Helper::Res('main.css');           // the equivalent (Chapter 2-9)
__url('shop/');                    // build an ordinary URL
```

## Using it together with the built-in HTTP server

The document root of [`HttpServer`](../reference/HttpServer-HttpServer.md) (Chapter 4-5) is `path_document`:

```php
HttpServer::RunQuickly([
    'path' => __DIR__ . '/../',
    'path_document' => 'public',    // -> docroot = <path>/public
]);
```

So "document root direct" and "framework-served" coexist in development: files such as `/doc.css` are served straight by the built-in server, while `/res/…` or `/shop/res/…` are served by the framework.

## Common errors

| Symptom                   | Cause                                                        | Fix                                                |
| -------------------- | --------------------------------------------------------- | ------------------------------------------------- |
| A resource 404s although the file clearly exists | The prefix slashes are wrong (one too many or one missing) | Root app `'/res/'`, child app `'res/'`; use the Chapter 3-5 method to confirm which file was matched |
| A child app's resource 404s while the main app is fine | The child app did not set `controller_resource_prefix` (it defaults to `''`, which makes every path a possible resource) | Set a resource prefix explicitly for every app |
| You want to serve `.php` or a `../` path | The security check rejects it | Do not do it; put dynamic content in a controller |
| Every resource 404s in production | `res/` was not deployed to the docroot | Use `cloneResource()`, or `cp -r res/* public/` in your build script |
| Two apps' same-named resources overwrite each other | They share one prefix directory | Split prefixes per app; only use the same name on purpose (Chapter 3-5) |
|                      |                                                           |                                                   |

## Related references

- [DuckPhp\Component\RouteHookResource](../reference/Component-RouteHookResource.md) (including the `cloneResource()` details)
- [Chapter 2-19 Using the user system](user.md) — `__use_logined_view_data` (the post-login view)
- [Chapter 3-5 Overriding and replacement](overriding.md): the `res/<name>/` override rule
