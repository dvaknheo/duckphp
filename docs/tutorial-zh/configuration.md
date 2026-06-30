# 应用选项和应用设置

## 应用设置
当你用骨架建立工程的时候，你可以看到 `config/DuckPhpSettings.config.php`
这文件的配置用于保存敏感信息，将运行时配置（数据库、Redis 等）放在此文件中

术语 `应用设置`(`app settings`) 指的就是这些选项。这些选项是全局的。
一个典型的设置如下：
```php
<?php
return [
    'duckphp_is_debug' => true,         // 调试模式
    //'duckphp_platform' => 'default',  // 平台标识
    //'duckphp_is_maintain' => false,   // 维护模式
    'database_list' => [
        [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4;',
            'username' => 'root',
            'password' => 'password',
        ],
    ],
];
```
> - 调试模式，本地开发的时候开启调试模式。作用很大
> - 平台模式，用于多机部署的时候看是在哪台机器上。
> - 维护模式，开启的时候，会进入 `error_maintain` 应用选项配置的页面。

## 应用选项

什么是 `应用选项`？ DuckPhp应用的入口类一般如下：

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        //'path_info_compact_enable' => false,
        
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
		
        'exception_for_project'  => ProjectException::class,
        'exception_for_business'  => BusinessException::class,
        'exception_for_controller'  => ControllerException::class,
        'exception_reporter' =>  ExceptionReporter::class,
        //...
    ];
}
```
这个 `options` 属性就是`应用选项`(`app options`)
这些应用选项都有一堆默认值，你可以把他们 dump 出来
通过修改这些应用选项 ，你可以得到不一样的应用效果

前一节说的 应用设置 的默认位置 `config/DuckPhpSettings.config.php` 这个文件是否能移动位置呢？可以。 他们在应用选项的默认值是这样：
```
    'setting_file' => 'config/DuckPhpSettings.config.php',
    'setting_file_enable' => true,
```
比如应用选项  `'use_env_file' => true` 后，框架会自动加载项目根目录的 `.env` 作为设置 (settings)。
但这不是应用选项的全部。框架默认加载的组件也带有自己的选项，你可以在 `App::$options` 中覆盖它们。

### 默认加载的组件

框架启动时会自动初始化以下组件，每个组件都有自己的默认选项：

**核心组件**
- `Logger` — 日志记录
- `SuperGlobal` — 超全局变量管理
- `View` — 视图渲染
- `Route` — 路由系统
- `ExceptionManager` — 异常处理

**数据组件**
- `DbManager` — 数据库管理器（根应用自动加载）
- `RedisManager` — Redis 管理器（根应用自动加载）

**扩展组件**（通过 `ext` 选项启用）
- `Lang` — 国际化
- `RouteHookCheckStatus` — 维护模式检查
- `RouteHookRewrite` — URL 重写
- `RouteHookRouteMap` — 路由映射
- `RouteHookResource` — 静态资源处理

### 覆盖组件默认选项

当你在 `App::$options` 中设置与组件同名的选项时，就会覆盖该组件的默认值。例如：

```php
class App extends DuckPhp
{
    public $options = [
        // 修改路由方法前缀，默认是 'action_'
        'controller_method_prefix' => 'call_',
    ];
}
```

修改后，主页的入口方法就从 `MainController::action_index()` 变成了 `MainController::call_index()`。

再比如修改日志配置：

```php
class App extends DuckPhp
{
    public $options = [
        'log_prefix' => 'MyApp',              // 日志前缀
        'log_file_template' => 'app_%Y%m%d.log', // 日志文件名格式
    ];
}
```

各组件的完整选项列表参见 [附录：应用选项参考](appendix-options.md)。

## 部分应用选项速查

### 路径相关

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | 自动检测 | 项目根目录绝对路径 |
| `namespace` | 自动检测 | 项目命名空间 |
| `path_view` | `'view'` | 视图模板目录（相对项目根） |
| `path_config` | `'config'` | 配置目录 |
| `path_runtime` | `'runtime'` | 运行时目录（日志等） |

### 调试与错误

| 选项 | 默认值 | 说明 |
|---|---|---|
| `is_debug` | `false` | 是否开启调试模式 |
| `error_404` | `null` | 404 错误视图，`'_sys/error_404'` 等 |
| `error_500` | `null` | 500 错误视图 |
| `exception_for_project` | `\Exception::class` | 项目异常基类 |
| `exception_for_business` | `null`（继承自 `exception_for_project`） | Business 层异常类 |
| `exception_for_controller` | `null`（继承自 `exception_for_project`） | Controller 层异常类 |
| `exception_reporter` | `null` | 异常报告器类名 |

### 路由相关

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace_controller` | `'Controller'` | 控制器命名空间段 |
| `controller_class_postfix` | `'Controller'` | 控制器类后缀 |
| `controller_method_prefix` | `'action_'` | 控制器方法前缀 |
| `controller_welcome_class` | `'Main'` | 欢迎页控制器类名 |
| `controller_welcome_method` | `'index'` | 默认 action 方法 |
| `controller_url_prefix` | `''` | URL 前缀 |
| `controller_resource_prefix` | `''` | 静态资源 URL 前缀 |
| `controller_class_map` | `[]` | 控制器类替换映射 |
| `rewrite_map` | `[]` | URL 重写映射 |
| `route_map` | `[]` | 路由映射 |
| `route_map_important` | `[]` | 优先路由映射 |
| `skip_404` | `false` | 跳过 404 处理 |

