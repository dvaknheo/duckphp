# 插件模式
[toc]


## 简介

扩展包是一套完整业务组件，用于不影响整个工程底下处理一套完整的功能。比如例子里的用户系统。
你直接用
http://127.0.0.1:8080/full/blog.php/login 来访问。 这就是通过在博客 Demo 这个项目里，使用 扩展包的形势访问
SimpleAuth这个扩展包。

http://127.0.0.1:8080/full/auth.php 来访问。这就是 SimpleAuth这个项目。

## 使用方法


使用扩展包，在主类里加选项里添加扩展包的入口就可以。

正如所示 auth 所示范的那样。在普通 
```php
$this->options['ext']['SimpleAuth\Base\App'] = true;
```
然后打开地址。

然后你发先 auth 也是个 DuckPhp 独立工程。是的，开发 DuckPhp 扩展包 很容易
只要入口  App 类使用 AppPluginTrait 就行。

相关类参考见 [AppPluginTrait ](ref/Component-AppPluginTrait.md)

AppPluginTrait 帮你做了什么，如果不满要求应该怎么办

AppPluginTrait 的默认选项

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
'plugin_view_options' => [],

    传递给 View 的选项
'plugin_route_options' => [],

    传递给 Route 的选项

'plugin_path_document' => '../public',

    用于读取资源的目录
'plugin_enable_readfile' => false,

    启用用于读取资源的目录
'plugin_use_singletonex_route' => true,

    启用 SingletonEx, 让客户可以修改Controller

```
[
        'plugin_path_namespace' => null,
        'plugin_namespace' => null,
        
        'plugin_routehook_position' => 'append-outter', // 路由钩子位置
        
        'plugin_path_conifg' => 'config',            //路径
        'plugin_path_view' => 'view',         // view 
        
        'plugin_search_config' => false,   // 搜索配置
        'plugin_files_config' => [],
    ];
```

对默认选项不满意的修改 $plugin_options 选项。

## 调整第三方包
第三方包提供的不一定符合你的要求，所以
覆盖 view , 你要在  view/{plugin_namespace}/ 下的 view 文件将会覆盖 view 文件
覆盖 model 在 onPrepare 用 G 函数替换
controller 同样可以使用 G 函数 如果选项 plugin_use_singletonex_route 启用
使用自己的 view

## 调整自己的实现
```php
    //
```

## 原理


初始化。
 AppPluginTrait 兼容 App ，初始化不从 init() 开始，而是从 pluginModeInit() 开始
    public function puglinModeInit(array $options, object $context = null)

ThroOnTrait ::ThrowTo 就在这里用的

运行阶段
钩子名字，
克隆助手类的方法到当前助手类，
设置 View 可覆盖

创建新路由，绑定  $_SERVER, path_info;
新路由的默认地址

依赖关系
AppPluginTrait  依赖 Route , View , Configer
///////////////////////// 
把你的工程变成扩展包

如题