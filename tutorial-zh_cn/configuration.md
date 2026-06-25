# 应用配置

## 配置传递方式

DuckPHP 的配置以 PHP 数组形式传递，通过 `App::RunQuickly($options)` 或 `$app->init($options)` 传入。

```php
\MyProject\System\App::RunQuickly([
    'is_debug' => true,
    'path' => __DIR__ . '/../',
    // 更多选项...
]);
```

## 配置文件（推荐方式）

### config/DuckPhpSettings.config.php

将运行时配置（数据库、Redis 等）放在此文件中，框架启动时自动加载：

```php
<?php
return [
    'duckphp_is_debug' => true,
    'database_list' => [
        [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4;',
            'username' => 'root',
            'password' => 'password',
        ],
    ],
];
```

在 `App` 类中启用配置加载：

```php
class App extends DuckPhp
{
    public $options = [
        'setting_file' => 'config/DuckPhpSettings.config.php',
        'setting_file_enable' => true,
    ];
}
```

### .env 文件支持

设置 `'use_env_file' => true` 后，框架会自动加载项目根目录的 `.env` 文件。

## 配置层级（由低到高覆盖）

1. **代码默认值** — 每个类中 `$options` 属性定义的默认值
2. **继承合并** — `Core\App` → `DuckPhp` → 你的 `App` 类，逐层合并
3. **配置文件** — `DuckPhpSettings.config.php` 返回的数组
4. **`.env` 文件** — 若启用
5. **运行时传入** — `RunQuickly($options)` 或 `init($options)` 的参数

## 核心选项速查

### 路径相关

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | 自动检测 | 项目根目录绝对路径 |
| `namespace` | 自动检测 | 项目命名空间 |
| `path_view` | `'view'` | 视图模板目录（相对项目根） |
| `path_config` | `'config'` | 配置目录 |
| `path_runtime` | `'runtime'` | 运行时目录（日志等） |
| `path_resource` | `'res'` | 静态资源目录 |
| `path_document` | `'public'` | Web 文档根目录 |

### 调试与错误

| 选项 | 默认值 | 说明 |
|---|---|---|
| `is_debug` | `false` | 是否开启调试模式 |
| `error_404` | `null` | 404 错误视图，`'_sys/error_404'` 等 |
| `error_500` | `null` | 500 错误视图 |
| `exception_for_project` | `\Exception::class` | 项目异常基类 |
| `exception_for_business` | 继承自 `exception_for_project` | Business 层异常类 |
| `exception_for_controller` | 继承自 `exception_for_project` | Controller 层异常类 |
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

### 数据库

| 选项 | 默认值 | 说明 |
|---|---|---|
| `database_list` | `null` | 数据库配置列表 `[{dsn,username,password}]` |
| `database_driver` | `''` | 数据库驱动 |
| `database_list_reload_by_setting` | `true` | 是否从配置文件重载数据库配置 |
| `database_log_sql_query` | `false` | 是否记录 SQL 日志 |

### 扩展

| 选项 | 默认值 | 说明 |
|---|---|---|
| `ext` | `[]` | 启用的扩展列表 `[类名 => true]` |
| `app` | `[]` | 子应用列表 `[类名 => 选项数组]` |

## 全局设置项

在配置文件中定义的设置项，通过 `App::Setting($key)` 读取。框架使用的设置键：

| 键 | 说明 |
|---|---|
| `duckphp_is_debug` | 调试模式 |
| `duckphp_is_maintain` | 维护模式 |
| `duckphp_platform` | 平台标识 |
| `database_list` | 数据库配置 |
| `redis_list` | Redis 配置 |
