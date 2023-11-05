# DuckPhp\DuckPhp
[toc]

## 简介
总入口类。
## 依赖关系
`DuckPhp\DuckPhp` 继承 微框架入口类 [DuckPhp\Core\App](Core-App.md)，并且引用这些组件类用于增强功能
- 使用 [DuckPhp\Component\Cache](Component-Cache.md)
- 使用 [DuckPhp\Component\Console](Component-Console.md)
- 使用 [DuckPhp\Component\DbManager](Component-DbManager.md)
- 使用 [DuckPhp\Component\DuckPhpCommand](Component-DuckPhpCommand.md)
- 使用 [DuckPhp\Component\EventManager](Component-EventManager.md)
- 使用 [DuckPhp\Component\Pager](Component-Pager.md)
- 使用 [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
- 使用 [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)

## 选项

继承 [DuckPhp\Core\KernelTrait](Core-Trait.md) 的默认选项。详细查看 [DuckPhp\Core\KernelTrait](Core-Trait.md)的文档。
        'setting_file' => 'setting',
设置文件名。

        'setting_file_enable' => true,
使用设置文件: $path/$path_config/$setting_file.php

        'use_env_file' => false,
使用 .env 文件
打开这项，可以读取 path 选项下的 env 文件

        'config_ext_file_map' => [],
额外的配置文件数组，用于 AppPluginTrait

        'setting_file_ignore_exists' => true,
如果设置文件不存在也不报错

并且做了以下更改

```php
[
    'ext' => [
        DbManager::class => true,
        RouteHookRouteMap::class => true,
    ],
    'route_map_auto_extend_method' => false,
    'database_auto_extend_method' => false,
];
```
Console

Console::G()->options['cli_default_command_class'] = DuckPhpCommand::class;



DuckPhpCommand

RouteHookPathInfoCompat


## 说明

也许你想从这个入口类了解 DuckPhp 的所有配置，但这个类只是扩展自 DuckPhp 类只是弥补了 [DuckPhp\Core\App](Core-App.md) 缺失的方法。
具体的方法在 DuckPhp\Core\App 里。主要流程在 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)  里

## 公开方法
    public function __construct()
    
    protected function initAfterOverride(array $options, object $context = null)



## 详解

App 类，继承了 DuckPhp\Core\App 的功能，在默认配置里，还加载了其他 Ext 扩展的内容。


+ 如果你要看有什么选项，查看  Kernel 和 App  文档
+ 如果你要看核心流程，查看  Kernel  文档
+ 如果你要看有什么方法，查看 App 文档。

## 备忘


    public static function setBeforeGetDbHandler($db_before_get_object_handler)

    public static function getRoutes()

    public static function assignRoute($key, $value = null)

    public static function assignImportantRoute($key, $value = null)
//


    protected function initComponents(array $options, object $context = null)


    public static function InitAsContainer($options)

    public function thenRunAsContainer($skip_404 = false, $welcome_handle = null)

    public function isInstalled()

    public function install($options)

    protected function bumpSingletonToRoot($oldClass, $newClass)

    public function _Admin($admin = null)

    public function _User($user = null)

    public function _AdminId()

    public function _UserId()




    public static function InitAsContainer($options)

    public function thenRunAsContainer($skip_404 = false, $welcome_handle = null)

    public function isInstalled()

    public function install($options)

    protected function bumpSingletonToRoot($oldClass, $newClass)

    public function _Admin($admin = null)

    public function _User($user = null)

    public function _AdminId()

    public function _UserId()

