# 组件与扩展

DuckPHP 采用组件化架构，所有功能模块都是可选的、可替换的。

## 内置组件（Component）

组件在 `DuckPhp\Component\` 命名空间下，框架启动时自动初始化。

### DbManager — 数据库管理器

管理数据库连接池，支持读写分离。

```php
DbManager::_()->_DbForRead()->fetchAll("SELECT * FROM users");
DbManager::_()->_DbForWrite()->execute("UPDATE users SET name=?", 'new');
```

### RedisManager — Redis 管理器

```php
RedisManager::Redis(0)->set('key', 'value');
RedisManager::Redis(0)->get('key');
```

### Configer — 配置读取

从 `config/` 目录加载 PHP 配置文件。

```php
// 加载 config/app.php
Configer::_()->_Config('app', 'key', 'default');
// 或
Helper::Config('app', 'key');
```

### Cache — 缓存

默认实现为空缓存（不存储），可自行扩展：

```php
Cache::_()->get('key', 'default');
Cache::_()->set('key', 'value', 3600);
```

### Pager — 分页器

生成分页 HTML：

```php
Pager::_()->PageHtml($total, [
    'page_size' => 10,
    'page_key' => 'page',
]);
// 或
Helper::PageHtml($total);
```

### Lang — 国际化

多语言支持：

```php
// 配置语言文件 config/lang/zh_CN.php
// 内容：return ['hello' => '你好'];

__l('hello');            // 输出 '你好'
__l('hello {name}', ['name' => '世界']); // '你好 世界'
__h($str);               // HTML 转义
__hl('hello');           // 国际化 + HTML 转义
```

语言自动检测（按优先级）：

1. URL 参数（`?lang=zh_CN`）
2. Cookie
3. HTTP `Accept-Language` 头
4. 命令行环境变量
5. 默认语言配置

### RouteHook* — 路由钩子

| 组件 | 功能 |
|---|---|
| `RouteHookCheckStatus` | 检查维护模式/安装状态 |
| `RouteHookRewrite` | URL 重写 |
| `RouteHookRouteMap` | 路由映射匹配 |
| `RouteHookResource` | 静态资源处理 |
| `RouteHookPathInfoCompat` | PATH_INFO 兼容模式 |

## 可选扩展（Ext）

扩展在 `DuckPhp\Ext\` 命名空间下，通过 `options['ext']` 按需启用。

### CallableView — 函数式视图

用类方法代替视图文件，适合 API 或简单项目：

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\CallableView::class => true,
    ],
    'callable_view_class' => \MyApp\View\Views::class,
];
```

视图类：

```php
namespace MyApp\View;

class Views
{
    public static function main($data)
    {
        extract($data);
        ?>
        <h1><?= $title ?></h1>
        <?php
    }
}
```

### JsonView — JSON 视图

将所有视图输出转为 JSON，适合纯 API 项目：

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\JsonView::class => true,
    ],
    'json_view_skip_vars' => ['debug_info'],  // 排除某些变量
];
```

### MiniRoute — 迷你路由

简化的路由模式，适合小项目。与默认路由相比，去掉了控制器后缀和方法前缀等约定：

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\MiniRoute::class => true,
    ],
];
```

MiniRoute 的默认选项：
- `controller_class_postfix` = `''`（无后缀）
- `controller_method_prefix` = `''`（无前缀）
- 其他选项与主路由一致

启用后，URL `/user/list` 将直接映射到 `User::list()`，而非 `UserController::action_list()`。

### JsonRpcExt — JSON-RPC 支持

提供 JSON-RPC 服务端和客户端支持：

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\JsonRpcExt::class => true,
    ],
    'jsonrpc_namespace' => 'JsonRpc',           // 服务命名空间
    'jsonrpc_backend' => 'https://127.0.0.1',     // 后端地址
    'jsonrpc_is_debug' => false,                // 调试模式
    'jsonrpc_enable_autoload' => true,            // 自动加载服务
    'jsonrpc_timeout' => 5,                       // 超时时间
];
```

启用后，框架会自动处理 JSON-RPC 请求，将指定命名空间下的类方法暴露为 RPC 服务。

### StrictCheck — 严格检查

开发阶段严格模式检查。

## 子应用系统（App 嵌套）

DuckPHP 支持在同一个进程中运行多个相互隔离的应用——子应用。

### 配置子应用

```php
class App extends DuckPhp
{
    public $options = [
        'app' => [
            'BlogApp' => [
                'controller_url_prefix' => 'blog/',  // URL 前缀
                'namespace' => 'BlogApp\\',
                // 其他选项...
            ],
            'ApiApp' => [
                'controller_url_prefix' => 'api/',
                'namespace' => 'ApiApp\\',
                'ext' => [
                    \DuckPhp\Ext\JsonView::class => true,
                ],
            ],
        ],
    ];
}
```

### 子应用生命周期

子应用拥有与主应用相同的生命周期，主应用的 `onBeforeChildrenInit()` 回调在子应用初始化前触发。

### 相位（Phase）隔离

每个应用在相位容器中拥有独立空间，通过 `PhaseContainer` 管理：

```php
// 切换当前相位
App::Phase('BlogApp');
// 此时 BlogApp 下的所有单例可见
$data = BlogService::_()->getData();

// 切回主应用
App::Phase(App::Root()->getOverridingClass());
```

## 全局函数

框架定义了以下全局辅助函数（在 `src/Core/Functions.php` 中）：

| 函数 | 说明 |
|---|---|
| `__h($str)` | HTML 转义 |
| `__l($str, $args)` | 国际化 |
| `__hl($str, $args)` | 国际化 + HTML 转义 |
| `__url($url)` | 生成 URL |
| `__res($url)` | 生成资源 URL |
| `__domain($use_scheme)` | 获取当前域名 |
| `__json($data)` | JSON 编码 |
| `__display($view, $data)` | 渲染视图片段 |
| `__var_dump(...)` | 调试输出（仅调试模式） |
| `__var_log($var)` | 调试日志 |
| `__trace_dump()` | 打印调用栈 |
| `__debug_log($msg)` | 调试日志 |
| `__logger()` | 获取日志实例 |
| `__is_debug()` | 是否调试模式 |
| `__is_real_debug()` | 是否真实调试模式 |
| `__platform()` | 获取平台标识 |
