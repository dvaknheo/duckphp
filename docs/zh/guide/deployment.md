# 1-7 上线最小清单

> 解决什么问题：把应用放上真服务器，少踩那几件必然踩的事。
> 前置：[第 1-2 章](install.md)、[第 1-5 章](configuration.md)。预计 15 分钟。

## 一、目录与文档根

**唯一允许对外暴露的目录是 `public/`**：

```
project/
├── public/        ← 文档根（Web 服务器的 root 指向这里）
├── src/ config/ view/ runtime/ bin/   ← 全部在文档根之外，Web 不可直达
└── vendor/
```

把 `src/`、`config/`、`runtime/` 放到 Web 可直达的位置，等于把源码、数据库口令、日志全公开。

## 二、开发用：PHP 内置服务器

**写法 A：只要页面能访问（无后缀的路径都行）**

```bash
php -S 127.0.0.1:8080 -t public
```

PHP 内置服务器对「不像文件」的路径会向上找 `public/index.php`，框架再用 `controller_fix_mistake_path_info` 从 `REQUEST_URI` 补出 PATH_INFO —— 所以 `/`、`/Note/index`、`/Note/show?id=1` 都能跑通。

> ⚠️ **但它只对「不像文件」的路径生效**：像 `/res/main.css` 这种带后缀的 URL，内置服务器**不会**交给 `index.php`，而是直接 404 —— 也就是说**由框架代发的静态资源**（[第 3-3 章](static-resources.md)）在这种启动方式下访问不到。放进 `public/` 的真实文件当然照常由服务器直出。

**写法 B：连框架代发的资源也要能访问（推荐）**

加一个开发用 router 脚本 `public/router.php`：

```php
<?php declare(strict_types=1);
// public/router.php —— 只用于本地开发
$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;                        // 真实文件交给内置服务器直出
}
$_SERVER['PATH_INFO'] = $path;           // 关键：router 模式下 PATH_INFO 是空的，必须自己补
require __DIR__ . '/index.php';
```

```bash
php -S 127.0.0.1:8080 -t public public/router.php
```

实测（`tests/data_for_tests/ZThirdDemo`）：`/`、`/shop/native`、`/res/main.css`（框架代发）、`/shop/res/third.css`（被覆盖的资源）、`/dev-only.css`（真实文件）全部 200。

> 框架自带的 `php bin/cli.php run` 走的就是「写法 A」那条命令（内部执行 `php -S … -t <path_document>`，不带 router），所以它同样只适合页面调试。

## 三、生产用：nginx

```nginx
server {
    listen 80;
    server_name example.com;

    root /var/www/project/public;      # ← 文档根必须是 public/
    index index.php;

    # 静态文件直出；其余全部交给 index.php，并把原始 URI 作为 PATH_INFO 传进去
    location / {
        try_files $uri $uri/ /index.php$request_uri;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # 文档根外的东西一律拒绝（保险丝）
    location ~ /(src|config|runtime|vendor|bin)/ { deny all; }
    location ~ /\.(git|env) { deny all; }
}
```

**为什么是 `/index.php$request_uri` 而不是 `/index.php?$query_string`**：框架读的是 `PATH_INFO`。`try_files … /index.php$request_uri` 让 nginx 内部把请求变成「执行 index.php，PATH_INFO = 原始路径」，框架才能知道用户请求的是 `/Note/index`。用 `?$query_string` 会把路径塞进查询串，路由就丢了（这正是那类「nginx 配好 rewrite 后全站 404」的原因）。
> **框架自带一层兜底**：`Route` 的选项 `controller_fix_mistake_path_info`（默认 `true`，源码 `src/Core/Route.php` 第 388–404 行 `getPathInfo()`）会在 `PATH_INFO` 为空**且** `SCRIPT_NAME` 恰好等于 `/index.php` 时，用 `parse_url(REQUEST_URI, PHP_URL_PATH)` 把路径补回 `PATH_INFO`（并写回 `$_SERVER`/SuperGlobal）。
>
> 也就是说：**「所有请求都丢给 `/index.php`、不带 PATH_INFO」这种通用框架式的 nginx 配置，DuckPhp 也照样能路由**（`try_files … /index.php?$query_string` 那类写法不会全站 404）。
>
> 它只在 `SCRIPT_NAME` 是 `/index.php` 时生效：入口文件改了名（如 `app.php`）、应用挂在子目录、或者你要自己完全掌控 PATH_INFO 时，仍以上面的 `$request_uri` 写法为准；若确认环境正确、想避免它把真实 404 误判成别的路径，可置 `'controller_fix_mistake_path_info' => false`。
## 四、生产用：Apache

文档根同样指 `public/`，在 `public/.htaccess` 里：

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

`index.php/$1` 这种写法产生的就是 PATH_INFO。

**如果你的服务器环境实在拿不到 PATH_INFO**（部分 CGI/FastCGI 配置），开一个兼容模式即可，路由改从查询串解析：

```php
'path_info_compact_enable' => true,     // 由 RouteHookPathInfoCompat 接管（第 2-2 章）
```

## 五、权限

| 目录/文件      | 要求                     |     |
| ---------- | ---------------------- | --- |
| `runtime/` | **Web 用户可写**（日志、缓存）    |     |
| `config/`  | 可读即可；含口令，**不可** Web 直达 |     |
| `public/`  | 只读                     |     |
| 代码目录       | 只读                     |     |

```bash
chown -R www-data:www-data runtime
chmod -R 755 runtime
```

日志写不进去时框架**不会报错**（[`Logger::log()`](../reference/Core-Logger.md) 静默返回 `false`），所以这条一定要单独检查（第 1-6 章）。

## 六、生产选项清单

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'is_debug' => false,                 // ★ 关调试：不泄露堆栈
        'error_404' => '_sys/error_404',     // ★ 配好错误页
        'error_500' => '_sys/error_500',
        'installed' => true,                 // ★ 安装完成后置真（第 3-6 章）
    ];
}
```

数据库/Redis 口令放设置文件或 `.env`，别写进代码库（第 1-5 章）。

## 七、上线检查清单

- [ ] 文档根指向 `public/`，`src/`/`config/`/`runtime/`/`.env`/`.git` 不可直达
- [ ] `is_debug` = `false`，且设置文件里没有 `duckphp_is_debug = true`
- [ ] `error_404` / `error_500` 视图就位，页面文案是给用户看的
- [ ] `installed` = `true`（或安装入口已加鉴权/移除）
- [ ] `runtime/` 可写，日志真的在落盘（看一眼文件）
- [ ] nginx/apache 的 rewrite 正确（随便访问一个深路径，确认不是全站 404）
- [ ] 静态资源：放进 `public/` 由服务器直出，或确认 rewrite 后框架代发正常（第 3-3 章）
- [ ] 日志轮转（`log_file_template` 按天/小时 + 外部 logrotate）
- [ ] HTTPS 与 HSTS；Cookie 的 secure/httponly 按需（第 2-9 章）
- [ ] 部署后跑一遍冒烟：首页、一个列表页、一个 POST、一个 404

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 全站 404（连首页都打不开） | 文档根指错，或 rewrite 没配 | 确认 root 是 `public/`；用 `try_files … /index.php$request_uri` |
| 首页正常，深路径 404 | rewrite 把路径塞进了查询串 | 改成 `index.php$request_uri`；或开 `path_info_compact_enable` |
| 框架代发的资源 404（nginx 下不该出现） | rewrite 没生效 / 资源前缀不匹配 | 第 3-3 章的斜杠规则；`res/` 内容可用 `cloneResource()` 部署到 docroot |
| 页面 500 但看不到原因 | `is_debug=false` 且没配 `error_500` | 先看 `runtime/` 日志；临时开 `is_debug` 复现 |
| 日志没生成 | `runtime/` 不可写 | 给 Web 用户写权限（见上） |
| 部署后跳安装页 | `installed` 仍是 `false` | 置 `true`（第 3-6 章） |

## 下一步

- 第一卷到此结束。接着看[第二卷 · 单一应用](../guide/index.md)：从[第 2-1 章 四层架构与调用规范](layers.md) 起，一路读到第 2-17 章。
- 相关：[第 3-6 章 安装器与 Web 安装流程](installer.md)、[第 4-6 章 多入口·多域名·多 SAPI](multi-entry.md)
