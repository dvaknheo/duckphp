# 2-17 安全与性能清单

> 解决什么问题：上线前逐项自查——**安全**（不该泄漏的别泄漏、该转义的转义、该自己做的自己做）与**性能**（哪些开关值钱、哪些开关有代价）。
> 前置：[第 1-7 章 上线最小清单](deployment.md)（部署/文档根/权限，本章不重复）、[第 2-11 章 异常与错误处理](exception.md)。
> 用法：上线前把两张清单过一遍，逐条打勾；每条都给了「依据」——能改的改，写「工程侧」的说明框架不提供、必须自己做。

## 最小示例

一份可以直接抄的生产选项（全部是真实存在的选项名，依据在下一节逐条给出）：

```php
namespace MyProj\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public $options = [
        // ── 关掉一切调试口径 ──
        'is_debug' => false,
        'is_maintain' => false,

        // ── 错误页自己接管（别让框架打占位文本）──
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        'error_maintain' => '_sys/error_maintain',

        // ── 关掉不需要的能力 ──
        'path_info_compact_enable' => false,   // 有正规 rewrite 时不需要它
        'data_file_enable' => false,           // 不用 ext 选项持久化就别开
        'use_env_file' => true,                // 敏感项走 .env / 设置文件，不进代码库

        // ── Session ──
        'session_prefix' => 'myproj_',
    ];
}
```

## 机制说明

### 1. 这些事框架已经替你做了

| 事项          | 框架怎么做的                                                                                                 | 依据                                                           |
| ----------- | ------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------ |
| HTML 转义     | `__h()` / `__hl()`（[`CoreHelper::H()`](../reference/Core-CoreHelper.md)）                               | `src/Core/Functions.php`                                     |
| SQL 注入      | 参数占位符 + [`Db::quote()`](../reference/Db-Db.md) / `quoteScheme()`；模型层不对外暴露 `execute()`                  | [第 2-5 章](database.md)、[第 2-6 章](model.md)                   |
| 系统调用可替换     | [`SystemWrapper`](../reference/Core-SystemWrapper.md)（`header`/`setcookie`/`exit`/`session_start` 都走它） | `Helper::system_wrapper_replace()`                           |
| Session 键隔离 | `session_prefix` 前缀，多应用同进程不串键                                                                          | `Foundation/SessionTrait.php`                                |
| 错误信息不泄漏     | `is_debug` 为假时错误页只输出占位文本，不含堆栈/路径                                                                       | [`App::_OnDefaultException()`](../reference/Core-App.md)     |
| 404 不泄漏     | 默认输出 `404 File Not Found` 占位，调试信息只在 `is_debug` 下附加                                                     | `App::_On404()`                                              |
| 维护模式        | `is_maintain` 或设置项 `duckphp_is_maintain` → 渲染 `error_maintain`                                         | `App::initComponents()`                                      |
| 安装门禁        | `installed` 为假时前台/后台控制器构造函数 302 到 `url_install`                                                        | `App::checkInstallToPage()`                                  |
| 隐藏欢迎类路径     | `controller_welcome_class_visible = false` 时 `/Main/xxx` 直接拒绝（E009）                                    | [`Route::adjustClassBaseName()`](../reference/Core-Route.md) |

### 2. 这些事框架不提供，必须自己做

| 事项                  | 为什么框架不管                   | 怎么做                                                                              |
| ------------------- | ------------------------- | -------------------------------------------------------------------------------- |
| **CSRF 防护**         | 框架没有表单令牌机制                | 自己发令牌（Session 存 + 表单隐藏域 + 校验），至少给写操作加 SameSite Cookie                            |
| **文件上传校验**          | 只提供 `Helper::FILES()` 取数据 | 校验 MIME/扩展名/大小、重命名存储、**别放进可执行目录**（[第 3-3 章](static-resources.md)）                |
| **越权（水平/垂直）**       | 权限体系只解决「是不是登录/是不是管理员」     | 每条业务都要判断「这个资源是不是他的」——放 Business 层                                                |
| **限流/防刷**           | 无内置                       | Redis 计数（[第 2-13 章](cache.md)）或网关层做                                              |
| **强制 HTTPS / HSTS** | 无内置中间件                    | 用 pre 路由钩子判断 `$_SERVER['HTTPS']` 后 `Helper::Show302()`（[第 2-10 章](lifecycle.md)） |
| **请求体大小/超时**        | PHP-FPM/nginx 的职责         | 在服务器配置里限制                                                                        |
| **依赖与版本**           | `composer.lock` 是你的责任     | 上线前 `composer audit`，锁定版本再部署                                                     |

### 3. 性能：值钱的开关与有代价的开关

| 开关/做法                                                                                | 方向     | 说明                                                                                                |
| ------------------------------------------------------------------------------------ | ------ | ------------------------------------------------------------------------------------------------- |
| `is_debug = false`                                                                   | ⬆ 变快   | 调试模式会拼错误页、附加堆栈                                                                                    |
| OPcache（PHP 侧）                                                                       | ⬆ 变快   | 框架大量小文件，OPcache 收益最大；生产务必开                                                                        |
| `ext` 只装需要的                                                                          | ⬆ 变快   | 每个 ext 都要初始化；[第 3-5 章](overriding.md)之外用不到的别声明                                                    |
| [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) 的 `@compile`/通配规则 | ⬆ 变快   | 规则**只编译一次**（首次匹配时），但规则条数多仍要按「常用在前」排                                                               |
| `database_list_try_single = true`                                                    | ⬆ 变快   | 单连接场景别绕多连接逻辑                                                                                      |
| `local_database = false`（默认）                                                         | ⬆ 变快   | 子应用独占连接会多建一套连接池（[第 3-4 章](component-sharing.md)）                                                  |
| `path_info_compact_enable = true`                                                    | ⬇ 变慢   | 兼容模式要走查询串解析；服务器配置好 rewrite 就关掉                                                                    |
| `data_file_enable = true`                                                            | ⬇ 变慢   | 每次 init 多一次文件 IO（[`ExtOptionsLoader`](../reference/Component-ExtOptionsLoader.md)），还会多落一个 JSON 文件 |
| `default_exception_do_log = true`（默认）                                                | ⬇ 磁盘涨  | 每次异常都写日志；确保 `runtime/` 有轮转，别把日志写到内存盘并撑满                                                           |
| `use_output_buffer = true`                                                           | ⚠ 语义变化 | 会改变「何时发 headers」，不是越快越好，按需开（[第 2-10 章](lifecycle.md)）                                             |
| `view_skip_notice_error = true`（默认）                                                  | ⚠ 掩盖问题 | 视图里未定义变量不报警；开发期可临时关掉找 bug                                                                         |
|                                                                                      |        |                                                                                                   |

### 4. 日志与隐私

- 日志默认落在 `path_log`（默认 `runtime`），文件名模板 `log_%Y-%m-%d_%H_%i.log`；
- **不要让日志进 Web 根**（`runtime/` 不应可被直接访问，见[第 1-7 章](deployment.md)）；
- 异常日志默认带堆栈（`default_exception_do_log = true`）——里面可能有 SQL、路径、参数，属于敏感信息；
- 别把密码/令牌写进日志：[`Logger`](../reference/Core-Logger.md) 只记录你给它的内容（[第 1-6 章](debugging.md)）。

## 上线前清单

### 安全

| ✔ | 检查项 | 怎么改 | 依据 |
|---|---|---|---|
| ☐ | `is_debug` 为 `false`，且**没有任何子应用**把它打开 | 全局搜 `'is_debug' => true`；注意根应用与子应用是「或」关系 | `App::_IsDebug()` |
| ☐ | 设置里 `duckphp_is_debug` 没被打开 | 查 `DuckPhpSettings.config.php` / `.env` | `_Setting('duckphp_is_debug')` |
| ☐ | `error_404`/`error_500` 指向自己的错误页 | 视图里**用 `__is_debug()` 包住调试块** | [第 2-11 章](exception.md) |
| ☐ | `installed` 已置 `true`（用安装流程的项目） | 或确保安装入口 `url_install` 不可公开访问 | `checkInstallToPage()` |
| ☐ | `is_maintain` 为 `false`（除非正在维护） | 维护时置真，并配 `error_maintain` 视图 | `App::initComponents()` |
| ☐ | 所有用户数据输出都过 `__h()` | 视图里搜 `<?=` 逐个看 | `CoreHelper::H()` |
| ☐ | SQL 全部使用占位符 | 搜 `fetchAll(` / `execute(` 里的拼串 | [第 2-5 章](database.md) |
| ☐ | 写操作有 CSRF 防护 | **框架不提供**：自己发令牌 + 校验 | 工程侧 |
| ☐ | 上传做了类型/大小/路径校验 | **框架不提供**：自己校验，存储目录不可执行 | 工程侧 |
| ☐ | 越权判断写在 Business 层 | 每条资源访问都判断归属 | [第 2-1 章](layers.md) |
| ☐ | Cookie 设了 `Secure`/`HttpOnly`/`SameSite` | 用 `Helper::setcookie(...)` 显式传参 | [`Controller\ControllerHelper::setcookie()`](../reference/Foundation-Controller-ControllerHelper.md) |

| ☐ | Session 前缀不与其他应用冲突 | 配 `session_prefix` | `Foundation/SessionTrait.php` |
| ☐ | HTTPS 强制跳转 + HSTS | 用 pre 路由钩子实现 | [第 2-10 章](lifecycle.md) |
| ☐ | 敏感配置不在代码库 | 走 `.env`（`use_env_file`）或设置文件，且该文件不进 git | [第 1-5 章](configuration.md) |
| ☐ | `runtime/`、`config/` 不可被 Web 直接访问 | 文档根指向 `public/` | [第 1-7 章](deployment.md) |
| ☐ | 依赖已审计/锁版本 | `composer audit` + 提交 `composer.lock` | 工程侧 |

### 性能

| ✔ | 检查项 | 怎么改 | 依据 |
|---|---|---|---|
| ☐ | OPcache 已开（`opcache.enable=1`、合理 `memory_consumption`） | php.ini / FPM 池配置 | 服务器侧 |
| ☐ | `ext` 里没有用不到的扩展 | 删掉多余声明 | [`DuckPhp::initComponentsOfInner()`](../reference/DuckPhp.md) |
| ☐ | 有 rewrite 就关掉 `path_info_compact_enable` | 置 `false` | [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) |
| ☐ | 不用 ext 选项持久化就关 `data_file_enable` | 置 `false` | `ExtOptionsLoader` |
| ☐ | 路由映射规则按命中频率排序、能用精确匹配就别用正则 | 调整 `route_map_important` 顺序 | `RouteHookRouteMap::matchRoute()` |
| ☐ | 数据库：常用查询有索引；只读查询走读连接 | 建模 + `Helper::DbForRead()` | [第 2-5 章](database.md) |
| ☐ | 热点数据有缓存且设了 TTL | [`Helper::Cache()->set($k, $v, $ttl)`](../reference/Component-Cache.md) | [第 2-13 章](cache.md) |
| ☐ | 日志有轮转、级别不过度 | 调 `Logger` 选项与轮转策略 | `src/Core/Logger.php` |
| ☐ | `use_output_buffer` 的取舍已确认 | 不确定就别开 | [`Runtime`](../reference/Core-Runtime.md) 选项 |
| ☐ | 上线前跑过一轮压测/慢查询日志 | 开 `database_log_sql_query` 观察后关掉 | [`DbManager`](../reference/Component-DbManager.md) 选项 |

## 常见写法

**① 强制 HTTPS（pre 路由钩子，框架不提供中间件）**

```php
// src/System/App.php 的 onInited() 里
Helper::addRouteHook(function (string $path_info) {
    if (App::_()->isCli()) { return false; }
    if (!empty($_SERVER['HTTPS'])) { return false; }
    Helper::Show302('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    return true;                      // 拦住：控制器不执行（第 2-10 章）
}, 'prepend-outter');
```

**② 会话 Cookie 加固**

```php
Helper::session_start([
    'cookie_secure'   => true,        // 仅 HTTPS 传输
    'cookie_httponly' => true,        // 禁止 JS 读取
    'cookie_samesite' => 'Lax',       // 缓解 CSRF
]);
```

（`Helper::session_start()` 走 `SystemWrapper`，可被测试替换；Session 前缀用 `session_prefix` 选项。）

**③ 错误页里显式区分调试/生产**

```php
<!-- view/_sys/error_500.php -->
<h1>服务器开小差了</h1>
<?php if (__is_debug()): ?>
    <pre><?= __h($class . ': ' . $message) ?></pre>
    <pre><?= __h($trace) ?></pre>
<?php endif; ?>
```

**④ 缓存热点数据（带 TTL，避免"缓存永不失效"）**

```php
public function hotProducts(): array
{
    $key  = 'hot_products';
    $data = Helper::Cache()->get($key);
    if ($data === null) {
        $data = ProductModel::_()->getHot(20);
        Helper::Cache()->set($key, $data, 300);     // 5 分钟
    }
    return $data;
}
```

**⑤ 上线前开一次 SQL 日志，确认没有全表扫描**

```php
// 临时排错用，观察完记得关
$options = ['database_log_sql_query' => true, 'database_log_sql_level' => 'debug'];
```

## 常见错误

| 现象                         | 原因                                                                                                                                                                                                 | 改法                                  |
| -------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------- |
| 生产环境看到堆栈和路径                | `is_debug` 为真，或某个**子应用**开着它                                                                                                                                                                        | 全局搜索并关掉；错误视图里用 `__is_debug()` 包住调试块 |
| 错误页是 `Internal Error` 占位文本 | 没配 `error_500`（同理 404 是 `404 File Not Found`）                                                                                                                                                      | 配 `error_500`/`error_404` 指向自己的视图   |
| 后台/前台突然 302 到安装页           | `installed` 为 `false`（[`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md)/[`AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) 构造函数会检查） | 置 `true`（[第 3-6 章](installer.md)）   |
| 页面出现「Maintaining.」占位       | `is_maintain` 或设置项 `duckphp_is_maintain` 为真且没配 `error_maintain`                                                                                                                                    | 关掉开关，或配 `error_maintain` 视图         |
| 用户输入被原样输出到 HTML            | 视图里直接 `<?= $x ?>`                                                                                                                                                                                  | 一律 `__h($x)`                        |
| 上线后变慢                      | 调试开关没关、OPcache 没开、`path_info_compact` 还开着                                                                                                                                                          | 对照上面「性能」清单                          |
| 日志目录把磁盘写满                  | 异常日志默认开启且无轮转                                                                                                                                                                                       | 配轮转；必要时调低日志级别                       |
| 认为「用了框架就防住了 CSRF」          | 框架不提供 CSRF 机制                                                                                                                                                                                      | 自己发令牌校验（清单里那条）                      |
|                            |                                                                                                                                                                                                    |                                     |
|                            |                                                                                                                                                                                                    |                                     |

## 下一步

- [第 1-7 章 上线最小清单](deployment.md)：部署、文档根、目录权限——本章的前置。
- [第 2-11 章 异常与错误处理](exception.md)：错误页与异常报告的完整机制。
- [第 4-9 章 性能调优与排错手册](troubleshooting.md)：症状 → 排查路径。
- 参考手册：[DuckPhp\Core\App](../reference/Core-App.md)、[options 速查](../reference/options.md)、[DuckPhp\Core\Logger](../reference/Core-Logger.md)。
