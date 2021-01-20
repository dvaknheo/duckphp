# DuckPhp\Core\AppPluginTrait
[toc]

## 简介

这个Trait 把App类变成插件

## 选项
*需要注意的是：AppPluginTrait 的选项是在 plugin_options 设置.*

'plugin_path_namespace' => null,

    插件的命名空间路径
'plugin_namespace' => null,

    插件的命名空间
'plugin_routehook_position' => 'append-outter',

    插件路由的插入方法
'plugin_path_conifg' => 'config',

    插件的配置文件
'plugin_path_view' => 'view',

    插件的视图文件
'plugin_search_config' => false,

    插件搜索方法
'plugin_files_config' => [],

    插件的配置文件列表
'plugin_url_prefix' => '',

    URL 前缀，限定插件的目录。
## 公开方法
    public function pluginModeInit(array $options, object $context = null)
    public static function PluginModeRouteHook($path_info)
    public function _PluginModeRouteHook($path_info)

    
    public function pluginModeGetOldRoute()
    
## 用于重载的事件方法

    protected function onPluginModePrepare()
    protected function onPluginModeInit()
    protected function onPluginModeBeforeRun()
    protected function onPluginModeRun()
onPluginModeBeforeRun 运行阶段就执行 onPluginModeRun 得到回调之后才执行。

## 内部方法
    protected function pluginModeInitOptions($options)
    protected function pluginModeDefaultInit(array $options, object $context = null)
    protected function pluginModeIncludeConfigFile($file)
    protected function pluginModeSearchAllPluginFile($path, $setting_file = '')
    protected function pluginModeDefaultRouteHook($path_info)
    protected function pluginModeCloneHelpers()

## 主流程

## 详解
例子见于 template/public/full/

## 如何使用 一个应用级插件？
App 的 ext 选项里加个 插件名称 => 插件配置的类

## 如何把现有应用变成插件
App 类 use AppPluginTrait

## 初始化阶段

插件的初始化， 插件的初始化和 App 的初始化不同。
你要重写 pluginModeInit() 这个函数。
因为是 trait 不是父类，所以要在 再调用父类 的 同名方法的地方使用 pluginModeDefaultInit 。

插件的选项是通过  plugin_options 变量而不是 options 变量修改，目的就是子类化的时候可使用父类的选项。
'plugin_path_namespace' => null, 是指定插件类的基准文件目录，以配合插件的其他类使用。 默认为空的时候，会去搜索插件类的上一级类， 如 UserSystem\\Base\\App => UserSystem 。 这里注意到是，是 UserSystem 而不是 UserSystem\\Base 

'plugin_path_conifg' => 'config', 配置文件的目录，  'plugin_path_view' => 'view',  视图文件的目录。
这两在默认模式， 会提供默认的视图和配置，如果在你的应用里有同名文件，则会被覆盖。

'plugin_routehook_position' => 'append-outter',

特殊配置文件会从这里加载 pluginModeIncludeConfigFile
    protected function pluginModeSearchAllPluginFile($path, $setting_file = '')
搜索所有配置文件

AppPluginTrait 会在现有 View  / [plugin_path_namespace] 下搜索 view ，如果没有则退化 到 plugin_path_view

##  运行阶段

默认情况下 AppPluginTrait 会有个 RouteHook 附加在最后面，执行运行部分的代码。

PluginModeRouteHook -> _PluginModeRouteHook -> pluginModeDefaultRouteHook 。

为什么 PluginModeRouteHook 是静态的，是为了Swoole 模式不用修改。

_PluginModeRouteHook 就是你可以继承修改的方法

pluginModeDefaultRouteHook 默认的路由钩子

pluginModeDefaultRouteHook 通过 pluginModeCloneHelpers 把自己的 Helper  克隆过去调整 View 目录。

然后切入自己的 namespace 执行控制器。


    public function pluginModeInit(array $options, object $context = null)
    public static function PluginModeRouteHook($path_info)
    public function _PluginModeRouteHook($path_info)
    protected function pluginModeInitOptions($options)
    protected function pluginModeDefaultInit(array $options, object $context = null)
    protected function pluginModeIncludeConfigFile($file)
    protected function pluginModeSearchAllPluginFile($path, $setting_file = '')
    protected function pluginModeDefaultRouteHook($path_info)
    protected function pluginModeCloneHelpers()
    
    
    protected function onPluginModePrepare()
    protected function onPluginModeInit()
    protected function onPluginModeBeforeRun()
    protected function onPluginModeRun()
    protected function pluginModeBeforeRun($callback)
 