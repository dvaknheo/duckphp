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
- 调试模式，本地开发的时候开启调试模式。作用很大
- 平台模式，用于多机部署的时候看是在哪台机器上。
- 维护模式，开启的时候，会进入 `error_maintain` 应用选项配置的页面。

## 应用选项

什么是`应用选项`？ DuckPhp应用的入口类一般如下：
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
这些应用选项都有一堆默认值，你把可以把他们 dump 出来。
通过修改这些应用选项 ，你可以得到不一样的应用效果

应用设置的默认位置 `config/DuckPhpSettings.config.php` 这个文件是否能移动位置呢？可以。 他们在应用选项的默认值是这样：
```
    'setting_file' => 'config/DuckPhpSettings.config.php',
    'setting_file_enable' => true,
```
比如应用选项  `'use_env_file' => true` 后，框架会自动加载项目根目录的 `.env` 作为设置 (settings)。

但这不是应用选项的全部。 你可以在这里添加修改组件的选项
`DuckPhp\Core` `DuckPhp\Component`



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
