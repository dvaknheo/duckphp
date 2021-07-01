# 应用插件
[toc]


## 简介

DuckPhp 的一大特色是你可以把你的工程提供给别人作为应用插件使用。
一般而已，后台系统都是 给 桩代码，然后在这上面改， DuckPhp 并不这样
本文教你使用应用插件，和如何写应用插件

应用插件是一套完整业务组件，用于不影响整个工程底下处理一套完整的功能。比如例子里的用户系统。
你直接用
http://127.0.0.1:8080/full/blog.php/login 来访问。 这就是通过在博客 Demo 这个项目里，使用 应用插件的形势访问
`SimpleAuth` 这个应用插件。

http://127.0.0.1:8080/full/auth.php 来访问。这就是 `SimpleAuth`这个项目。


相关类参考见  [AppPluginTrait ](ref/Component-AppPluginTrait.md)

## 使用方法


使用应用插件，在主类里加选项里添加应用插件的入口就可以。

正如所示 auth 所示范的那样。`核心工程师`在项目选项里添加相关代码
```php
$this->options['ext']['SimpleAuth\System\App'] = [
    // 更多选项
];
```

然后打开地址。

然后你发现  `SimpleAuth` 也是个 `DuckPhp` 独立工程。是的，开发 `DuckPhp` 应用插件 很容易
只要入口  `App` 类使用  `AppPluginTrait` 就行。

## 使用第三方包

第三方包提供的不一定符合你的要求，所以
覆盖视图 View, 你要在  `PROJECT_PATH/view/{plugin_namespace}/` 下的 view 文件将会覆盖 view 文件

覆盖业务 business 和  模型 model 在 `onPrepare 简单的用 可变单例 G 函数替换

controller 则需要 `DuckPhp::replaceControllerSingleton($new_class, $old_class)` （为什么 contoller 不能用 G 汗）

以下是常见的调整。
            'plugin_url_prefix' => '',
URL 前缀，限定插件的目录， 这是比较常见的调整，把应用插件放到特定目录。

            'plugin_routehook_position' => 'append-outter',
插件路由的插入方法，默认用最后的钩子

            'plugin_path_document' => 'public',
用于读取资源的目录

            'plugin_enable_readfile' => false,
启用用于读取资源的目录

            'plugin_readfile_prefix' => '',
启用用于读取资源的目录的前缀，如 /res

一般而言，提供 Api\MyProjectController 用于各种控制器助手，和 Web动作相关的
提供 Api\MyProjectService  用于各种业务代码。


页眉页脚 注意： View 的时候是切入 

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

## 事件
## 创建应用插件

创建一个最简单的应用插件只需要使用 AppPluginTrait  就可以了。
但是创建一个成熟的给别人用的应用插件不仅如此
提供 Api\MyProjectController
提供 Api\MyProjectService


一般而言， 应用插件 还会判断是否已经安装，是否在插件模式下运行等
还会触发一定事件用于更多定制化。

还注意到你的 数据库，session 和 cache 的键要有不同前缀以区分不同的工程。

你还需要安装程序。

