# DuckPhp\Component\AppPluginTrait
[toc]

## 简介

这个Trait 把 App 类变成插件

## 选项
*需要注意的是：AppPluginTrait 的插件选项是在 `plugin_options` 属性里设置* 目的就是子类化的时候可使用父类的选项。
所有选项

            'plugin_path' => null,
插件的路径,使用默认将会调整成普通App路径~ 会替换成插件类的命名空间

            'plugin_namespace' => null,
插件的命名空间，使用默认配置将会调整成所在类的命名空间的父命名空间

            'plugin_routehook_position' => 'append-outter',
插件路由的插入方法，默认用最后的钩子

            'plugin_path_conifg' => 'config',

插件的配置文件，

            'plugin_path_view' => 'view',
插件的视图文件，

            'plugin_files_config' => [],
插件的配置文件列表

            'plugin_search_config' => true,
插件搜索配置模式

            'plugin_url_prefix' => '',
URL 前缀，限定插件的目录

            'plugin_view_options' => [],
传递给 View 的选项

            'plugin_route_options' => [],
传递给 Route 的选项

            'plugin_path_document' => 'public',
用于读取资源的目录

            'plugin_enable_readfile' => false,
启用用于读取资源的目录

            'plugin_readfile_prefix' => '',
启用用于读取资源的目录的前缀，如 /res

            'plugin_use_singletonex_route' => true,
启用 SingletonEx, 让客户可以修改Controller

            'plugin_component_class_view' => '',
替换默认的View

            'plugin_component_class_route' => '',
替换默认的Route

            'plugin_injected_helper_map' => '',
高级功能

## 公开方法
全部方法：

    public function pluginModeInit(array $plugin_options, object $context = null)
初始化入口，插件的初始化从这里开始

    public static function PluginModeRouteHook($path_info)
    protected function _PluginModeRouteHook($path_info)
路由勾子

    public function pluginModeGetOldComponent($class)
获得旧的 $class::G() 实例

    public function pluginModeClear()
插件清理，备不时之需

## 用于重载的事件方法

    protected function onPluginModePrepare()
    protected function onPluginModeInit()
    protected function onPluginModeBeforeRun()
    public function onPluginModeAfterRun()
    public function onPluginModeException()

onPluginModeBeforeRun 运行阶段就执行 onPluginModeRun 得到回调之后才执行。 
onPluginModeAfterRun 是 public 的？
onPluginModeException 出异常后调用

## 内部方法
全部内部方法

    protected function pluginModeInitBasePath()
    protected function pluginModeInitConfigFiles($setting_file)
    private function pluginModeCheckPathInfo($path_info)
    protected function pluginModeReplaceDynamicComponent()
    protected function pluginModeInitDynamicComponent()
    protected function pluginModeReadFile($path_info)
    private function pluginModeGetPath($path_key, $path_key_parent = 'plugin_path'): string
    protected function pluginModeSearchAllPluginFile($path, $setting_file = '')
内部的方法

    protected function mime_content_type($file)
    protected function getMimeData()
修复 mime_content_type() 的替代方法。

## 应用
例子见于 template/public/full/

### 如何使用 一个应用级插件？
App 的 ext 选项里加个 插件名称 => 插件配置的类，比如

```php
$options['ext'][MyPluginApp::class] = true;
```
当然，你也可以调整选项
```php
$options['ext'][MyPluginApp::class] = [
    '`plugin_url_prefix`'=> '/admin',
    // 更多插件选项
];
```

### 如何把现有应用变成插件
```php
class MyPluginApp extends DuckPhp
{
    use AppPluginTrait;
    public $plugin_options = [
        //覆盖的插件选项
    ];
}
```
### 如何调整使用的插件

#### 前提
默认情况下， `plugin_namespace` 会调整为 插件类的命名空间的父层命名空间。
默认情况下， `plugin_path` 会调整为插件类的父层的父层的父层目录，
使得插件和普通 App 的模式共享同样的目录配置
`plugin_path` 配合 `plugin_path_*` 使用。如果 `plugin_path_*` 为绝对路径，则忽略 `plugin_path` 。

#### 限定于子目录。

调整插件选项 `plugin_url_prefix` 比如 MyPluginApp 仅仅在 `/admin` 下生效。

#### 覆盖视图和配置文件

视图文件 view/`{plugin_namespace}`/view.php 将会覆盖`{plugin_path}`/`{plugin_path_view}`/view.php 。

如果 plugin_path_view 是绝对路径，则是 `{plugin_path_view}`/view.php
Config 配置文件,类似 View。 config/{`plugin_namespace`}/config.php 会覆盖 {`plugin_path`}/{`plugin_path_config`}/config.php。

如果 plugin_path_view 是绝对路径，则是 `{plugin_path_view}`/view.php

资源文件，`{plugin_path}`/`{plugin_path_document}`/X.css 。对应的是 public/`{plugin_url_prefix}`/X.css 。



需要手动设置插件选项 `plugin_enable_readfile` 为 `true`,

#### 调整 View,Route

给 View,Route 加选项请使用插件选项 `plugin_view_options` `plugi_route_options`

替换 View 类，Route 类 , `plugin_component_class_view` `plugin_component_class_route`

Model, Controller， 用 MyMode::G(Model::G()),MyController::G(Controller::G()) 重写。 

如果插件编写者设置了插件选项 `plugin_use_singletonex_route` 为 false. 则无法修改 Controller

Route 将暂时替换成新的无钩子的 Route 类

高级使用：助手函数注入， `plugin_injected_helper_map`

### 插件的事件
这些都有对应的同名公开属性，默认如果有值得，则执行
onPluginModePrepare()

    初始化前
onPluginModeInit()

    初始化后
onPluginModeBeforeRun()

     运行前
onPluginModeAfterRun()

    成功运行后
## 主流程

### 初始化阶段

插件的初始化， 插件的初始化和 App 的初始化不同，并没有通过 init() 方法初始化。而是 pluginModeInit() 

初始化默认变量

执行 `onPluginMOdePrepare`事件处理函数

调整 view 路径，调整 configer

调整 helper

AppPluginTrait 会在现有 View  / [plugin_path_namespace] 下搜索 view ，如果没有则退化 到 plugin_path_view

最后，勾挂路由钩子，PluginModeRouteHook  -> \_PluginModeRouteHook。 执行 `onPluginMOdeInit` 事件处理函数

###  运行阶段

`pluginModeCheckPathInfo` 看是否路径符合

替换默认的动态组件（View 和 Route)并初始化

onBeforeRun

运行 Route

如果 Route::G()->run 失败，处理分支， 清理返回

onAfterRun

清理




