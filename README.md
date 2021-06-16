# DuckPhp 介绍
[toc]
***v1.2.10版***

作者QQ: 85811616

官方QQ群: 714610448

##  一、教程
[**快速入门**](docs/tutorial-quickstart.md) ,快速入门页面。

[**文档索引页**](docs/index.md) ,所有文档索引页面，所有文档的集合入口

### 直接运行演示
```
cd template
php ./duckphp-project run
```
### Composer 安装

```
composer require dvaknheo/duckphp # 用 require 
./vendor/bin/duckphp new --help   # 查看有什么指令
./vendor/bin/duckphp new    # 创建工程
./vendor/bin/duckphp run    # --host=127.0.0.1 --port=9527 # 开始 web 服务器
```
不建议使用命令行的 web 服务器， 你把 nginx 或 apache 的 document_root 设置为  public 目录按常规框架调整即可。

DuckPhp 也支持在子目录里使用，同时也支持无 path_info 配置的 web 服务器。

## 二、DuckPhp 是什么

DuckPhp 的名字来源：

`Duck Typing` If it walks like a duck, swims like a duck, and quacks like a duck, then it probably is a duck. 

`鸭子类型`，这东西看起来像鸭子，叫起来像鸭子，所以就是鸭子。

起初，这是是想搞个简单的 PHP Web 简单框架 。现在是使用方式简单，实际方式不简单。

DuckPhp 的版本历程

+ 1.0.\* 系列版本是前身 DNMVCS 单文件模式的版本
+ 1.1.\* 系列版本是前身 DNMVCS 拆分成多文件的版本
+ 1.2.\* 系列版本是改名 DuckPhp 后的版本，随着思想的变化，或许会有大的变更
+ 1.3.\* 系列版本将是计划开始有人大规模使用后的稳定版本，将会对历史负责了。

## 三、DuckPhp 的理念
**业务层**。通常的 Model，Controller，View 少了一层。而因为这种缺层，导致了很多很糟糕的场景。你会发现很多人在 Contorller 里写一堆代码，或者在 Model 里写一堆代码。

这个层。有人称呼 Service ,有人称呼 Logic 。我最初的时候称呼为 App ，很长时间内我都称为 Service 。现在，我称呼为 Business 业务层。之所以改过来， Business 就是业务的意思啊。不用多想。 而且现在 Service 服务 这个层被用滥了。现在第三方的平台过来的东西才叫 Service ，业务范围之内的，就叫 Business 吧。Service 给人的感觉是业务需要的服务，不能完成一个功能。

所以，Business 按业务走，Model 层按数据库走，Controller 层按 URL 地址走，View 按页面走，这就是 DuckPhp 的理念。

DuckPhp 的最大意义是思想，只要思想在，什么框架你都可以用。
你可以不用 DuckPhp 实现 Controller-Business-Model 架构。
只要有这个思想就是理念成功了。

组合而非继承。这是 DuckPhp 提倡的另一个观点。

## 四、DuckPhp 的优点

#### 1.  全组件可替换

作为一个现代的 PHP 库， 全组件可替换是必须的。

DuckPhp 用可变单例方式，解决了**系统的调用形式不变，实现形式可变**，不需要魔改来修复系统漏洞。而其他框架用的 IoC,DI 技术则复杂且不方便调试。

#### 2.  高可靠性，无依赖

DuckPhp 无第三方依赖，你不必担心第三方依赖改动而大费周折。**不需要引入101 个第三方包，就能工作**，稳定性完全可控。

如果对默认实现不满，你也可以很容易改用需要第三方依赖的实现。

比如 DuckPhp 的数据库类很简洁，而且，你可以轻易方便的替换。如教程就有使用 thinkphp-db 的例子。[链接](docs/tutorial-db.db)

#### 3.  超低耦合

DuckPhp 耦合松散，扩展灵活方便，魔改容易。

DuckPhp 可以做到你的应用和 DuckPhp 的系统代码只有一行关联。 这个是其他 PHP 框架目前都做不到的。你的业务代码，基本和 DuckPhp 的系统代码无关。你只要研究业务代码，不要研究框架代码。

DuckPhp 的 Controller 切换容易，独立，和其他类无关，简单明了。

DuckPhp 的路由也可以单独抽出使用。

#### 4. 简洁

DuckPhp 以库方式引入，所以 DuckPhp 工程骨架不像其他框架那样一大堆不可删除的文件

DuckPhp 框架的设计原则：这东西非得框架自带么，不自带行么。

DuckPhp 的配置基本都是使用默认方式。 不需要一大堆的配置文件。

DuckPhp 代码简洁，不做多余事情。最新版本默认 demo 运行根据 CodeCoverage 覆盖统计， 只需要行数 358 / 3811 (v1.2.11-dev)  执行行数/总可执行行数  。

DuckPhp 的应用调试非常方便，堆栈清晰，调用 debug_print_backtrace(2) 很容易发现。那些用了中间件的框架的堆栈很不清晰。

DuckPhp 各组件是无直接引用的，所以 var_dump() 能看出来。

DuckPhp/Core/App 是 DuckPhp 的子框架。有时候你用 DuckPhp/Core/App 也行。类似 lumen 之于 Laravel 。

#### 5. 可扩展

DuckPhp 支持扩展。这些扩展可独立，不一定非要仅仅用于 DuckPHP。

#### 6. 灵活自由

DuckPhp 不限制你的工程的命名空间固定为 app 。你甚至可以把你的工程作为其他项目的插件使用。

DuckPhp 支持全站路由，还支持局部路径路由和无 PATH_INFO 路由，不需要配服务器也能用。 可以在不修改 Web 服务器设置（如设置 PATH_INFO）的情况下使用，也可以在子目录里使用。

DuckPhp 很容易嵌入其他 PHP 框架。根据 DuckPhp 的返回值判断是否继续后面其他框架。

DuckPhp 支持 composer。无 composer 环境也可运。DuckPhp 是 Composer 库，不需要单独的脚手架工程。

#### 7. 最小惊讶原则(Principle of least astonishment)

DuckPhp 遵守最小惊讶原则，尽量避免一下常见问题：

“这东西从哪里来的，怎么就出现。这东西能干什么，我删除不行么。”

避免了注解之类不知道从哪里冒出来的东西。

#### 8. 全覆盖单元测试

DuckPhp 因为作者强迫症，每次发布都是通过全代码覆盖的测试，因此有很大健壮性。


#### 9. 区分使用角色

DuckPhp 的使用者角色分为 `业务工程师`和`核心工程师`。

`业务工程师` 只需要要研究业务代码。

`核心工程师` 才需要研究做系统核心代码。

#### 10. 其他优点

DuckPhp 无侵入，杜绝全局函数冲突引发的问题

DuckPhp 工程层级分明，不交叉引用。

DuckPhp 的类尽量无状态。

其他还有更多说到的优点，用到的时候会觉得精妙。


DuckPhp 有扩展能做到禁止你在 Controller 里直接写 sql 。有时候，框架必须为了防止人犯蠢，而牺牲了性能。但 DuckPhp 这么做几乎不影响性能。

DuckPhp 一度通过SwooleHttpd 不需要改代码就支持 swoole 。将来版本将支持 swoole 和 workerman ，和更多其他平台。

#### 11.和其他框架简单对比

|功能                 | CodeIgniter 4 | ThinkPHP 6 | Laravel 6 | DuckPhp |
|---------------------|--------------|------------|-----------|---------|
|仅一行关联           |              |            |           | V       |
|堆栈清晰             | V            | V          |           | V       |
|可热修复，不改源码解决所有问题 |              |            |           | V       |
|可把工程转成插件给第三方用 |              |            |           | V       |
|全覆盖测试           |              |            |           | V       |
|以库引用             |              |            |           | V       |
|单一 composer 框架   |              |            |           | V       |
|无第三方依赖         |              |            |           | V       |
|高性能               | V            | V          |           | V       |
|代码简洁             | V            | V          |           | V       |
|非固定全站框架                   |            |           |         | V |

## 五、DuckPhp 不做什么

* ORM ，和各种屏蔽 sql 的行为，根据日志查 sql 方便多了。 自己简单封装了 pdo 。你也可以使用自己的DB类。 你也可以用第三方ORM（教程就有使用 thinkphp-db 的例子。[链接](docs/tutorial-db.db)）
* 模板引擎，PHP本身就是模板引擎。
* Widget ， 和 MVC 分离违背。

## 六、理解 DuckPhp 的原则

DuckPhp 工程层级关系图

```text
           /-> View-->ViewHelper
Controller --> Business ------------------------------ ---> Model
         \         \   \               \  /                  \
          \         \   \-> (Business)Lib ----> ExModel----------->ModelHelper
           \         \             \                
            \         ---------------->BusinessHelper
             \-->ControllerHelper
```
![arch_full](docs/arch_full.gv.svg)

* Controller 按 URL 入口走 调用 View 和 Business
* Business 按业务走 ,调用 model 和其他第三方代码。
* Model 按数据库表走，基本上只实现和当前表相关的操作。
* View 按页面走
* 不建议 Model 抛异常
* ControllerHelper, BusinessHelper, ModelHelper, ViewHelper 都为助手类，通常缩写为 C, B, M, V

1. 如果  Business 业务之间 相互调用怎么办?
添加后缀为 Lib 用于 Business 共享调用，不对外，如 CacheLib.

2. 如果跨表怎么办?，三种解决方案
    1. 在主表里附加，其他表估计用不到的情况。
    2. 添加后缀为 ExModel 用于表示这个 Model 是多个表的，如 UserExModel。
    3. 或者单独和数据库不一致如取名 UserAndPlayerRelationModel
## 七、常用工程目录结构

DuckPhp 代码里的 template 目录就是我们的工程目录示例。也是工程桩代码。

在执行 `./vendor/bin/duckphp new` 的时候，会把代码复制到工程目录。 并做一些改动。

@script 目录结构

```text
+---app                         // psr-4 标准的自动加载目录。
|   +---Business                // 业务目录
|   |       BaseBusiness.php    //   服务基类
|   |       DemoBusiness.php    //   测试的业务
|   +---Controller              // 控制器目录
|   |       Main.php            //   默认控制器
|   +---Model                   // 模型目录
|   |       BaseModel.php       //   模型基类
|   |       DemoModel.php       //   测试模型
|   +---Helper                  // 助手类目录
|   |       BusinessHelper.php  //   服务助手类
|   |       ControllerHelper.php//   控制器助手类
|   |       ModelHelper.php     //   模型助手类
|   \---System                  // 系统基类放在这里
|           App.php             //   默认框架入口文件
|           BaseController.php  //   控制器基类
|           BaseException.php   //   系统错误基类
+---config                      // 配置文件放这里
|       config.php              //   配置，目前是空数组
|       setting.sample.php      //   设置，去除敏感信息的模板
+---view                        // 视图目录
|   |   main.php                //   视图文件
|   \---_sys                    //   系统错误视图文件
|           error-404.php       //   404 页面
|           error-500.php       //   500 页面
+---public                      // 网站目录
|       index.php               //   主页，入口页
\---duckphp-project             // 命令行入口
```
这个目录结构里，`业务工程师`只能写 `app/Controller`,`app/Model`,`app/Business`,`view` 这四个目录。
有时候需要去读 `app/Helper` 目录下的的类。其他则是 `核心工程师` 的活。

app 目录，就是放 LazyToChange 命名空间的东西了。 app 目录可以在选项里设置成其他名字
命名空间 LazyToChange 是 可调的。比如调整成 MyProject ,TheBigOneProject  等。
可以用 `./vendor/bin/duckphp new --namespace TheBigOneProject` 调整。

文件都不复杂。基本都是空类或空继承类，便于不同处理。
这些结构能精简么？
可以，你可以一个目录都不要。

System/App.php 这个文件的入口类继承 DuckPhp\DuckPhp 类，工程的入口流程会在这里进行，这里是`核心工程师`重点了解的类。

BaseController, BaseModel, BaseBusiness 是你自己要改的基类，基本只实现了单例模式。

Helper 目录，助手类，如果你一个人偷懒，直接用 APP 类也行  


### 如何精简目录
* 移除 app/Helper/ 目录,如果你直接用 App::* 替代助手类。
* 移除 app/System/BaseController.php 如果你的 Controller 和默认的一样不需要基本类。
* 移除 app/Model/BaseModel.php 如果你的 Model 用的全静态方法。
* 移除 app/System/BaseBusiness.php 如果你的 Business 不需要 G() 可变单例方法。
* 移除 config/ 目录,在启动选项里删除 'use_setting_file'=>true
* 移除 view/\_sys/ 目录 你需要设置启动选项里404和500错误 'error\_404','error\_500 。
* 移除 view 目录如果你不需要 view ，如 API 项目。
* 移除 duckphp-project 如果你不需要额外的命令行。
* 移除 测试和示例文件

@script 目录结构

##  八、教程索引

助手类教程在这里 [助手类教程](docs/tutorial-helper.md)，基本上，看完助手类教程，`业务工程师`就可以开干了。

此外有什么不了解的，问`核心工程师`吧。
比如路由方面，常见是文件路由。 [路由教程](docs/tutorial-route.md)

如果你的项目使用内置数据库，或许你还要看  [数据库教程](docs/tutorial-helper.md)

还有 [异常处理](docs/tutorial-exception.md) 异常处理，和 [事件处理](docs/tutorial-event.md)

命令行怎么处理，需要看  [命令行教程](docs/tutorial-console.md)

一些额外功能，你要看   [内置扩展介绍](docs/tutorial-extension.md)

使用第三方插件或把项目变成插件 需要看 [插件模式](docs/tutorial-plugin.md)

最后，查看 [开发相关](docs/tutorial-support.md) 加入开发



## 九、样例
### 1. hello world

@script File: `template/public/helloworld.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE

class Main
{
    public function index()
    {
        echo "hello world";
    }
}
$options = [
    'namespace_controller' => "\\",   // 本例特殊，设置控制器的命名空间为根，而不是默认的 Controller
    // 还有百来个选项以上可用，详细请查看参考文档
];
\DuckPhp\DuckPhp::RunQuickly($options);

```
从这个样例，我们可以简单的知道调整 `$options` 选项可以得到不同的结果。

DuckPhp 工程有上百个选项调整得到不同的结果。具体参考 [选项参考](docs/ref/options.md)

### 2. 复杂样例

工程附带的模板文件 `template/public/demo.php` 在单一的文件里演示如何使用 `DuckPhp`。

需要注意的是，这个样例是为了演示特性把所有东西集中到一个文件，实际编码不会把所有东西全放在同一个文件里。


@script File: `template/public/demo.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */

// 以下部分是核心工程师写。
namespace MySpace\System
{
    //自动加载文件
    require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
    
    use DuckPhp\DuckPhp;
    use DuckPhp\Ext\CallableView;
    use DuckPhp\SingletonEx\SingletonExTrait;
    use MySpace\View\Views;

    class App extends DuckPhp
    {
        // @override 重写
        public $options = [
            'is_debug' => true,
                // 开启调试模式
            'path_info_compact_enable' => true,
                // 开启单一文件模式，服务器不配置也能运行
            'ext' => [
                CallableView::class => true,
                // 默认的 View 不支持函数调用，我们开启自带扩展 CallableView 代替系统的 View
            ],
            'callable_view_class' => Views::class,
                // 替换的 View 类。
        ];
        // @override 重写
        protected function onInit()
        {
            //初始化之后在这里运行。
            //var_dump($this->options);//查看总共多少选项
        }
        // @override 重写
        protected function onRun()
        {
            //运行期代码在这里，你可以在这里 static::session_start();
        }
    }
    //服务基类, 为了 Business::G() 可变单例。
    class BaseBusiness
    {
        use SingletonExTrait;
    }
} // end namespace
// 助手类

namespace MySpace\Helper
{
    class ControllerHelper extends \DuckPhp\Helper\ControllerHelper
    {
        // 添加你想要的助手函数
    }
    class BusinessHelper extends \DuckPhp\Helper\BusinessHelper
    {
        // 添加你想要的助手函数
    }
    class ModelHelper extends \DuckPhp\Helper\ModelHelper
    {
        // 添加你想要的助手函数
    }
    class ViewHelper extends \DuckPhp\Helper\ViewHelper
    {
        // 添加你想要的助手函数。 ViewHelper 一般来说是不使用的
    }
    class AppHelper extends \DuckPhp\Helper\AdvanceHelper
    {
        // 添加你想要的助手函数。 AppHelper 一般来说是不使用的
    }
} // end namespace

//------------------------------
// 以下部分由应用工程师编写，不再和 DuckPhp 的类有任何关系。

namespace MySpace\Controller
{
    use MySpace\Business\MyBusiness;  // 引用助手类
    use MySpace\Helper\ControllerHelper as C; // 引用相关服务类

    class Main
    {
        public function __construct()
        {
            // 在构造函数设置页眉页脚。
            C::setViewHeadFoot('header', 'footer');
        }
        public function index()
        {
            //获取数据
            $output = "Hello, now time is " . C::H(MyBusiness::G()->getTimeDesc());
            $url_about = C::URL('about/me');
            C::Show(get_defined_vars(), 'main_view'); //显示数据
        }
    }
    class about
    {
        public function me()
        {
            $url_main = C::URL(''); //默认URL
            C::setViewHeadFoot('header', 'footer');
            C::Show(get_defined_vars()); // 默认视图 about/me ，可省略
        }
    }
} // end namespace

namespace MySpace\Business
{
    use MySpace\Model\MyModel;
    use MySpace\System\BaseBusiness;
    use MySpace\Helper\BusinessHelper as B;

    class MyBusiness extends BaseBusiness
    {
        public function getTimeDesc()
        {
            return "<" . MyModel::getTimeDesc() . ">";
        }
    }

} // end namespace

namespace MySpace\Model
{
    use MySpace\Helper\ModelHelper as M;

    class MyModel
    {
        public static function getTimeDesc()
        {
            return date(DATE_ATOM);
        }
    }
}
// 把 PHP 代码去掉看，这是可预览的 HTML 结构

namespace MySpace\View {
    class Views
    {
        public static function header($data)
        {
            extract($data); ?>
            <html>
                <head>
                </head>
                <body>
                <header style="border:1px gray solid;">I am Header</header>
    <?php
        }

        public static function main_view($data)
        {
            extract($data); ?>
            <h1><?=$output?></h1>
            <a href="<?=$url_about?>">go to "about/me"</a>
    <?php
        }
        public static function about_me($data)
        {
            extract($data); ?>
            <h1> OK, go back.</h1>
            <a href="<?=$url_main?>">back</a>
    <?php
        }
        public static function footer($data)
        {
            ?>
            <footer style="border:1px gray solid;">I am footer</footer>
        </body>
    </html>
    <?php
        }
    }
} // end namespace

//------------------------------
// 入口，放最后面避免自动加载问题

namespace
{
    $options = [
        // 'override_class' => 'MySpace\System\App', 
        // 你也可以在这里调整选项。覆盖类内选项
    ];
    \MySpace\System\App::RunQuickly($options);
}
```
## 十、nginx 配置
这是我的 nginx 配置，如果在安装时候出现什么问题，欢迎反馈。
毕竟一般配置好后都不会去动。出现什么安装问题会没特别在意


```
server {
    root DUCKPHP_ROOT/template/public;
    index index.php index.html index.htm;
    
    try_files $uri $uri/ /index.php$request_uri;
    location ~ \.php {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_split_path_info ^(.*\.php)(/.*)?$;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
## 十一、架构
### 系统架构图

![DuckPhp](docs/duckphp.gv.svg)

### DuckPhp 类/文件结构参考

 (粗体部分是启动的时候引用的文件)

1. `Core` 目录是核心目录，核心框架。基本功能都在 Core 里实现
    1. **[ComponentBase](docs/ref/Core-ComponentBase.md)** 组件基类
         1. **[ComponentInterface](docs/ref/Core-ComponentInterface.md)** 组件接口
    2. **[App](docs/ref/Core-App.md)** 核心应用类。引用以下类
        1. **[KernelTrait](docs/ref/Core-KernelTrait.md)** 核心Trait 以下是 `核心必备组件`
            1. [AutoLoader](docs/ref/Core-AutoLoader.md) 自动加载类
            2. **[Configer](docs/ref/Core-Configer.md)** 配置组件
            3. **[View](docs/ref/Core-View.md)** 视图组件
            4. **[Route](docs/ref/Core-Route.md)** 路由组件
            5. **[ExceptionManager](docs/ref/Core-ExceptionManager.md)**   异常管理组件
            6. **[RuntimeState](docs/ref/Core-RuntimeState.md)** 运行期数据保存组件
            7. **[Functions](docs/ref/Core-Functions.md)** 全局函数列表
        2. [ExtendableStaticCallTrait](docs/ref/Core-ExtendableStaticCallTrait.md) 扩展静态调用的 trait
        3. [SystemWrapperTrait](docs/ref/Core-SystemWrapperTrait.md) 替换系统同名函数的 trait
        4. [Logger](docs/ref/Core-Logger.md) 日志组件
    3. [AppPluginTrait](docs/ref/Core-AppPluginTrait.md)   这个Trait用于把独立工程 App 转成插件 
2. `Db` 目录，数据库目录
   1. [DbAdvanceTrait](docs/ref/Db-DbAdvanceTrait.md)  这个 trait 增加了 Db类的高级功能
   2. [DbInterface](docs/ref/Db-DbInterface.md) Db 类满足 DbInterface 接口
   3. [Db](docs/ref/Db-Db.md) Db类
3. **[DuckPhp](docs/ref/DuckPhp.md)** 入口类，加载了默认扩展的 DuckPhp 入口 ，扩展自 [DuckPhp\\Core\\App](docs/ref/Core-App.md)
4. `Component` 目录，自带组件扩展。
    1. [AppPluginTrait](docs/ref/Component-AppPluginTrait.md)   这个Trait用于把独立工程 App 转成插件 
    2. [Cache](docs/ref/Component-Cache.md) 缓存组件
    3. **[Console](docs/ref/Component-Cache.md)** 命令行模式扩展组件
    4. [Installer](docs/ref/Component-Installer.md) 安装器
    5. [DuckPhpCommand](docs/ref/Component-DuckPhpCommand.md) DuckPhp 的默认指令组件
    6. [DbManager](docs/ref/Component-DbManager.md) 数据库管理组件
    7. [EventManager](docs/ref/Component-EventManager.md) 事件管理组件
    8. [Pager](docs/ref/Component-Pager.md) 分页类
        1. [PagerInteface](docs/ref/Component-PagerInteface.md) 分页接口
    9. **[RouteHookPathInfoCompat](docs/ref/Component-RouteHookPathInfoCompat.md)** 无程序路由设计模式组件
    10. **[RouteHookRouteMap](docs/ref/Component-RouteHookRouteMap.md)** 路由映射组件
5. `Ext` 扩展目录，非默认加载的扩展。按字母排序。
    1. [CallableView](docs/ref/Ext-CallableView.md) 可接受函数调用的视图组件
    2. [EmptyView](docs/ref/Ext-EmptyView.md) 空视图组件
    3. [HookChain](docs/ref/Ext-HookChain.md) 把回调扩展成链的类
    4. [HttpServerPlugin](docs/ref/Ext-HttpServerPlugin.md) TODO http 扩展插件
    5. [JsonRpcExt](docs/ref/Ext-JsonRpcExt.md) Json 远程调用组件，把本地调用改为远程调用
        1. [JsonRpcClientBase](docs/ref/Ext-JsonRpcClientBase.md)
    6. [JsonView](docs/ref/Ext-JsonView.md) Json 视图组件
    7. [Misc](docs/ref/Ext-Misc.md) 杂项功能组件
    8. [MyFacadesAutoLoader](docs/ref/Ext-MyFacadesAutoLoader.md) 门面组件，不推荐
        1. [MyFacadesBase](docs/ref/Ext-MyFacadesBase.md) 门面类的基类，不推荐
    9. [MyMiddleware](docs/ref/Ext-MyMiddleware.md) 中间件，不推荐
    10. [RedisCache](docs/ref/Ext-RedisSimpleCache.md) redis 缓存组件
    11. [RedisManager](docs/ref/Ext-RedisManager.md) Redis管理器组件
    12. [RouteHookDirectoryMode](docs/ref/Ext-RouteHookDirectoryMode.md) 多个目录基准的模式组件
    13. [RouteHookManager](docs/ref/Ext-RouteHookManager.md) 路由钩子管理器
    14. [RouteHookRewrite](docs/ref/Ext-RouteHookRewrite.md) 路由重写组件
    15. [SimpleModelTrait](docs/ref/Ext-SimpleModel.md) 简单的模型Trait
    16. [SqlDumper](docs/ref/Ext-SqlDumper.md) Sql 迁移类
    17. [StaticReplacer](docs/ref/Ext-StaticReplacer.md) 适配协程的语法替换写法类
    18. [StrictCheck](docs/ref/Ext-StrictCheck.md) 严格检查模式组件
    19. [SuperGlobalContext](docs/ref/Ext-SuperGlobalContext.md) 超全局上下文组件
6. `Helper` 目录，各种助手类。
    1. [AdvanceHelper](docs/ref/Helper-AdvanceHelper.md) 应用助手类
    2. [AdvanceHelperTrait](docs/ref/Helper-AdvanceHelperTrait.md) 应用助手Trait
    3. [BusinessHelper](docs/ref/Helper-BusinessHelper.md) 业务助手类
    4. [BusinessHelperTrait](docs/ref/Helper-BusinessHelperTrait.md) 业务助手Trait
    5. [ControllerHelper](docs/ref/Helper-ControllerHelper.md) 控制器助手类
    6. [ControllerHelperTrait](docs/ref/Helper-ControllerHelperTrait.md) 控制器助手Trait
    7. [ModelHelper](docs/ref/Helper-ModelHelper.md) 模型助手类
    8. [ModelHelperTrait](docs/ref/Helper-ModelHelperTrait.md) 模型助手Trait
    9. [ViewHelper](docs/ref/Helper-ViewHelper.md) 视图助手类
    10. [ViewHelperTrait](docs/ref/Helper-ViewHelperTrait.md) 视图助手Trait
7. `HttpServer` 目录
    1. [AppInterface](docs/ref/HttpServer-AppInterface.md)  Http 服务的应用接口
    2. [HttpServer](docs/ref/HttpServer-HttpServer.md)  Http 服务器
    3. [HttpServerInterface](docs/ref/HttpServer-HttpServerInterface.md)  Http 服务接口
8. `SingletonEx`目录
    1. **[SingletonExTrait](docs/ref/SingletonEx-SingletonExTrait.md)**  可变单例 trait
    1. [SimpleReplacer](docs/ref/SingletonEx-SimpleReplacer.md)  可选可变单例容器
9. `ThrowOn`目录
    1. [ThrowOnTrait](docs/ref/ThrowOn-ThrowOnTrait.md) 可抛 trait，应用工程引用它方便异常处理

### 应用架构图（缺事件和异常处理部分）

![DuckPhp](docs/arch_all.gv.svg)

## 十二、DuckPhp 还要做什么

**我真的很需要反馈啊，给我个反馈吧**

* 文档，文档目前已经有很多了。但是还存在缺失，需要人帮我过一下。
* 范例，例子还太少太简单了。
* 更多的杀手级应用。

## 十三、还有什么要说的

使用它，鼓励我，让我有写下去的动力
