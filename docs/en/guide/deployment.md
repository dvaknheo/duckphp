# 1-7 Minimal Go-Live Checklist

> What this solves: putting the app on a real server while avoiding the few things everyone inevitably hits.
> Prerequisites: [Chapter 1-2](install.md), [Chapter 1-5](configuration.md). About 15 minutes.

## 1. Directories and document root

**The only directory allowed to be publicly exposed is `public/`**:

```
project/
├── public/        ← 文档根（Web 服务器的 root 指向这里）
├── src/ config/ view/ runtime/ bin/   ← 全部在文档根之外，Web 不可直达
└── vendor/
```

Putting `src/`, `config/`, `runtime/` within Web reach means publishing your source code, database passwords, and logs.

## 2. Development: the PHP built-in server

**Style A: just make pages reachable (any extension-less path works)**

```bash
php -S 127.0.0.1:8080 -t public
```

For paths that "don't look like files", the PHP built-in server falls back to `public/index.php`, and the framework then reconstructs PATH_INFO from `REQUEST_URI` via `controller_fix_mistake_path_info` — so `/`, `/Note/index`, and `/Note/show?id=1` all work.

> ⚠️ **But it only works for paths that "don't look like files"**: a URL with an extension like `/res/main.css` is **not** handed to `index.php` by the built-in server — it 404s directly. In other words, **static resources served by the framework** ([Chapter 3-3](static-resources.md)) are unreachable under this startup style. Real files placed in `public/` are of course served directly by the server as usual.

**Style B: framework-served resources must also be reachable (recommended)**

Add a development router script `public/router.php`:

```php
<?php declare(strict_types=1);
// public/router.php — for local development only
$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;                        // real files are served directly by the built-in server
}
$_SERVER['PATH_INFO'] = $path;           // key point: in router mode PATH_INFO is empty, you must fill it yourself
require __DIR__ . '/index.php';
```

```bash
php -S 127.0.0.1:8080 -t public public/router.php
```

Verified (`tests/data_for_tests/ZThirdDemo`): `/`, `/shop/native`, `/res/main.css` (framework-served), `/shop/res/third.css` (an overridden resource), `/dev-only.css` (a real file) — all 200.

> The bundled `php bin/cli.php run` runs the "Style A" command (it internally executes `php -S … -t <path_document>` without a router), so it is likewise only suitable for page debugging.

## 3. Production: nginx

```nginx
server {
    listen 80;
    server_name example.com;

    root /var/www/project/public;      # ← the document root must be public/
    index index.php;

    # static files served directly; everything else goes to index.php with the original URI passed as PATH_INFO
    location / {
        try_files $uri $uri/ /index.php$request_uri;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # deny everything outside the document root (a fuse)
    location ~ /(src|config|runtime|vendor|bin)/ { deny all; }
    location ~ /\.(git|env) { deny all; }
}
```

**Why `/index.php$request_uri` and not `/index.php?$query_string`**: the framework reads `PATH_INFO`. `try_files … /index.php$request_uri` makes nginx internally turn the request into "execute index.php with PATH_INFO = the original path", so the framework knows the user asked for `/Note/index`. Using `?$query_string` stuffs the path into the query string and the route is lost (this is exactly the cause of the classic "whole site 404 after configuring nginx rewrite").
> **The framework ships a fallback**: `Route`'s option `controller_fix_mistake_path_info` (default `true`, source `src/Core/Route.php` lines 387–404, `getPathInfo()`) uses `parse_url(REQUEST_URI, PHP_URL_PATH)` to fill the path back into `PATH_INFO` (writing it back to `$_SERVER`/SuperGlobal) when `PATH_INFO` is empty **and** `SCRIPT_NAME` happens to equal `/index.php`.
>
> In other words: **the generic-framework nginx config of "throw every request at `/index.php` without PATH_INFO" also routes fine under DuckPhp** (the `try_files … /index.php?$query_string` style won't cause a site-wide 404).
>
> It only takes effect when `SCRIPT_NAME` is `/index.php`: if the entry file is renamed (e.g. `app.php`), the app is mounted in a subdirectory, or you want full control over PATH_INFO yourself, stick with the `$request_uri` style above; if you've confirmed the environment is correct and want to stop it from mistaking a real 404 for another path, set `'controller_fix_mistake_path_info' => false`.

## 4. Production: Apache

The document root likewise points at `public/`; in `public/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

The `index.php/$1` pattern is exactly what produces PATH_INFO.

**If your server environment really can't provide PATH_INFO** (some CGI/FastCGI configurations), turn on a compat mode and routing is parsed from the query string instead:

```php
'path_info_compact_enable' => true,     // taken over by RouteHookPathInfoCompat (Chapter 2-3)
```

## 5. Permissions

| Directory/file      | Requirement                     |     |
| ---------- | ---------------------- | --- |
| `runtime/` | **Writable by the Web user** (logs, cache)    |     |
| `config/`  | Readable is enough; contains passwords, **must not** be Web-reachable |     |
| `public/`  | Read-only                     |     |
| Code directories       | Read-only                     |     |

```bash
chown -R www-data:www-data runtime
chmod -R 755 runtime
```

When logs can't be written the framework **does not error** ([`Logger::log()`](../reference/Core-Logger.md) silently returns `false`), so check this item on its own (Chapter 1-6).

## 6. Production options checklist

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'is_debug' => false,                 // ★ debug off: no stack leaks
        'error_404' => '_sys/error_404',     // ★ error pages in place
        'error_500' => '_sys/error_500',
        'installed' => true,                 // ★ set true once installation is done (Chapter 3-6)
    ];
}
```

Database/Redis passwords go in the settings file or `.env`, not into the code repository (Chapter 1-5).

## 7. Go-live checklist

- [ ] Document root points at `public/`; `src/`/`config/`/`runtime/`/`.env`/`.git` are not directly reachable
- [ ] `is_debug` = `false`, and the settings file has no `duckphp_is_debug = true`
- [ ] `error_404` / `error_500` views in place, with wording meant for users
- [ ] `installed` = `true` (or the install entry is authenticated/removed)
- [ ] `runtime/` is writable and logs actually land on disk (look at the files)
- [ ] nginx/apache rewrites are correct (visit any deep path and confirm it isn't a site-wide 404)
- [ ] Static resources: placed in `public/` and served directly by the server, or confirmed to work framework-served after rewrite (Chapter 3-3)
- [ ] Log rotation (`log_file_template` by day/hour + external logrotate)
- [ ] HTTPS and HSTS; Cookie secure/httponly as needed (Chapter 2-11)
- [ ] After deploying, run a smoke pass: homepage, one list page, one POST, one 404

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| Site-wide 404 (even the homepage won't open) | Wrong document root, or rewrite not configured | Confirm root is `public/`; use `try_files … /index.php$request_uri` |
| Homepage fine, deep paths 404 | The rewrite stuffed the path into the query string | Switch to `index.php$request_uri`; or enable `path_info_compact_enable` |
| Framework-served resources 404 (shouldn't happen under nginx) | Rewrite not in effect / resource prefix mismatch | The slash rules in Chapter 3-3; `res/` content can be deployed to the docroot with `cloneResource()` |
| Page 500 with no visible cause | `is_debug=false` and no `error_500` configured | Check the `runtime/` logs first; temporarily enable `is_debug` to reproduce |
| No logs generated | `runtime/` not writable | Give the Web user write permission (see above) |
| Redirected to the install page after deployment | `installed` is still `false` | Set it to `true` (Chapter 3-6) |

## Next steps

- That's the end of Volume 1. Continue with [Volume 2 · Single Application](../guide/index.md): start at [Chapter 2-1 The Four Layers and the Calling Rules](layers.md) and read through to Chapter 2-18.
- Related: [Chapter 3-6 The Installer and the Web Install Flow](installer.md), [Chapter 4-6 Multiple Entries · Multiple Domains · Multiple SAPIs](multi-entry.md)
