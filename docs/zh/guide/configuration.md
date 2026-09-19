# 5 配置与设置

> 解决什么问题：分清「应用选项（options）」与「应用设置（settings）」—— 哪些写在 `App.php`，哪些写在 `config/`，改一个键到底影响谁。
> 前置：[第 2 章](install.md)。预计 15 分钟。

## 两者一句话区别

| | 应用选项 options | 应用设置 settings |
|---|---|---|
| 写在哪 | 应用类 `public $options`（可再被 `init()`/`RunQuickly()` 传入的覆盖） | `config/DuckPhpSettings.config.php`、`.env`、或选项 `setting` 里给的数组 |
| 装什么 | 框架与组件的**行为开关**（路径、错误页、路由规则、扩展列表…） | **敏感/环境相关**的键值（数据库、Redis 密码…） |
| 谁读 | 各组件在自己的 `init()` 里按自己的白名单取 | 根应用 `_Setting()`；组件**显式**去读（如 `DbManager` 读 `database_list`） |
| 作用域 | 每个应用一套（子应用可在 `app` 选项里注入不同的值） | **只有根应用**加载，读的也是根应用的（`static::Root()->setting`） |
| 怎么查 | `App::_()->options`（可 dump 出全部） | `App::_Setting()`（无参返回整个数组）、`Setting('key', $default)` |

## 应用选项

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',            // 项目根：view/ config/ runtime/ 都相对它
        'namespace' => 'MyProj',                  // 下面 Controller/Business/Model 的公共前缀
        'error_404' => '_sys/error_404',          // 视图名（相对 view/）
        'error_500' => '_sys/error_500',
        'is_debug' => true,                       // 上线改 false
        'controller_method_prefix' => '',         // 默认空：方法名就是 URL 段
    ];
}
```

**合并顺序**（后面覆盖前面，`App::__construct()` 里发生）：

```
KernelTrait::$kernel_options → App::$core_options → 入口类的 $common_options → 你子类的 $options
                                                                                    ↓
                                              init($options) / RunQuickly($options) 传入的再覆盖一层
```

所以「临时改一个选项」不必改类文件：

```php
\MyProj\System\App::RunQuickly(['is_debug' => true, 'path_info_compact_enable' => true]);
```

> ⚠️ **应用层的选项不做白名单过滤**，写错键名不会有任何提示；但**组件层的选项是白名单**（`ComponentBase::init()` 里 `array_intersect_key`），给某组件塞它没声明的键会被**静默丢弃**。这是「改了没反应」的最常见原因。
>
> 例：`GlobalUser` 自己声明的是 `user_default_exception_class`；你去设 `admin_default_exception_class` 是无效的（那是 `GlobalAdmin` 的键）。

查选项的三种方式：

```php
var_dump(App::_()->options);                       // 当前生效的全部选项
var_dump(App::_()->options['error_404']);          // 某个键
// 示例应用 ZAllDemo 的首页视图就是把 options / 单例容器直接 dump 出来看的
```

## 应用设置

```php
<?php
// config/DuckPhpSettings.config.php —— 放敏感与环境相关的东西
return [
    'duckphp_is_debug' => true,        // 与选项 is_debug 等效（见下）
    //'duckphp_platform' => 'web-01',  // 多机部署时标识当前机器
    //'duckphp_is_maintain' => false,  // 维护模式（走 error_maintain 页面）

    'database_list' => [
        ['dsn' => 'mysql:host=127.0.0.1;dbname=demo;charset=utf8mb4;', 'username' => 'root', 'password' => 'secret'],
    ],
    'redis_list' => [
        ['host' => '127.0.0.1', 'port' => 6379, 'auth' => 'secret', 'select' => 0],
    ],
];
```

控制它的选项：

| 选项 | 默认 | 说明 |
|---|---|---|
| `setting_file` | `'config/DuckPhpSettings.config.php'` | 设置文件路径（相对 `path`） |
| `setting_file_enable` | `true` | 关掉就不加载设置文件 |
| `setting_file_ignore_exists` | `true` | 文件不存在时不报错 |
| `use_env_file` | `false` | 置真后加载项目根的 `.env`（`parse_ini_file` 格式） |
| `setting` | `[]` | 直接在选项里给一份设置数组（最低优先级） |

加载时机：**根应用**的 `onPrepare()` 阶段（`loadSetting()`），顺序是 `options['setting']` → `.env`（若开）→ 设置文件。

三个以 `duckphp_` 开头的键是框架自己认的：

| 设置键 | 读取处 | 效果 |
|---|---|---|
| `duckphp_is_debug` | `App::IsDebug()` | 与根应用的 `is_debug` 选项**或**关系：任一为真即调试模式 |
| `duckphp_is_maintain` | `prepareServe()` | 为真时进入维护页（`error_maintain` 选项） |
| `duckphp_platform` | `App::Platform()` | 多机部署标识 |

**关键认知**：设置**不会**自动变成「所有组件的选项」。只有**显式去读设置**的地方才会受它影响，目前已内建支持的有：

- `DbManager`：`database_list_reload_by_setting`（默认真）→ 设置里的 `database_list` 生效；
- `RedisManager`：同理的 `redis_list`；
- `App` 自己：上面那三个 `duckphp_*`；
- 你自己写的组件/业务：用 `Setting('key', $default)` 主动读。

其余键放进设置文件里**不会**有任何效果（比如把 `error_404` 写进设置文件是没用的，那是选项）。

## 环境区分（本地 / 生产）

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        'is_debug' => false,          // 默认安全；本地开发用环境变量/入口覆盖成 true
    ];
}
```

```php
// 本地开发入口（或 .env / 本机设置文件）
\MyProj\System\App::RunQuickly(['is_debug' => true]);
```

生产清单（第 7 章有完整版）：`is_debug=false`、错误页就位、敏感信息只在设置文件/`.env` 里、`runtime/` 可写。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 改了选项「没反应」 | 该键不属于你改的那个组件（组件选项是白名单） | 查[参考手册](../reference/index.md)确认键的归属；用 `App::_()->options` 看实际生效值 |
| 设置文件里的键不生效 | 设置≠选项，只有显式读设置的地方认它 | 行为开关写选项；只有 `database_list`/`redis_list`/`duckphp_*` 这类才写设置 |
| 子应用读不到设置 | 设置只由**根应用**加载，且读的是根的那份 | 用 `App::_Setting()`；要给子应用不同配置就用子应用的选项（`app` 里注入） |
| `.env` 没生效 | 忘了 `'use_env_file' => true`，或格式不是 `parse_ini_file` 的 `key=value` | 打开选项；检查文件在项目根 |
| 设置文件缺失导致报错 | 关掉了 `setting_file_ignore_exists` | 保持默认 `true`，或用空数组占位 |

## 下一步

- [第 6 章 调试、日志与 CLI 初体验](debugging.md)：`is_debug` 打开后能看见什么。
- [第 7 章 上线最小清单](deployment.md)：生产环境该怎么配。
- 参考手册：[DuckPhp\Core\App](../reference/Core-App.md)（全部核心选项）、[options 速查](../reference/options.md)
