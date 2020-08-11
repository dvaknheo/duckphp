# DuckPhp 介绍
作者QQ: 85811616

官方QQ群: 714610448

##  教程
[**快速入门**](doc/tutorial-quickstart.md) ,快速入门页面。

[**文档索引页**](doc/index.md) ,所有文档索引页面，所有文档的集合入口

### 直接运行演示。
进入 template 目录
```
php start_server.php
```
即可
### Composer 安装

```
composer require dvaknheo/duckphp # 用 require 
./vendor/bin/duckphp --help     # 查看有什么指令
./vendor/bin/duckphp --create   # --full # 创建工程，把 template 目录内容复制到当前目录
./vendor/bin/duckphp --start    # --host=127.0.0.1 --port=8080 # 开始 web 服务器
```
不建议使用命令行的 web 服务器， 你把 nginx 或 apache 的 document_root 设置为  public 目录按常规框架调整即可。

DuckPhp 也支持在子目录里使用，同时也支持无 path_info 配置的 web 服务器。
## DuckPhp 是什么

DuckPhp 的名字来源：

`Duck Typing` If it walks like a duck, swims like a duck, and quacks like a duck, then it probably is a duck. 

鸭子类型，这东西看起来像鸭子，叫起来像鸭子，所以就是鸭子。

起初，这是是想搞个简单的 PHP Web 简单框架 。现在是使用方式简单，实际方式不简单。

DuckPhp 的版本历程

+ 1.0.\* 系列版本是前身 DNMVCS 单文件模式的版本
+ 1.1.\* 系列版本是前身 DNMVCS 拆分成多文件的版本
+ 1.2.\* 系列版本是改名 DuckPhp 后的版本，随着思想的变化，或许会有大的变更
+ 1.3.\* 系列版本将是计划开始有人大规模使用后的稳定版本，将会对历史负责了。

## DuckPhp 的使用原则
业务层。通常的 Model，Controller，View 少了一层。而因为这种缺层，导致了很多很糟糕的场景。你会发现很多人在 Contorller 里写一堆代码，或者在 Model 里写一堆代码。

这个层。有人称呼 Service ,有人称呼 Logic 。我最初的时候称呼为 App ，很长时间内我都称为 Service 。现在，我称呼为 Business 业务层。之所以改过来， Business 就是业务的意思啊。不用多想。 而且现在 Service 服务 这个层被用滥了。现在第三方的平台过来的东西才叫 Service ，业务范围之内的，就叫 Business 吧。Service 给人的感觉是业务需要的服务，不能完成一个功能。

所以，Business 按业务走，Model 层按数据库走，Controller 层按 URL 地址走，View 按页面走，这就是 DuckPhp 的理念。

DuckPhp 的最大意义是思想，只要思想在，什么框架你都可以用。
你可以不用 DuckPhp 实现 Controller-Business-Model 架构。
只要有这个思想就是理念成功了。

组合而非继承。这是 DuckPhp 提倡的另一个观点。

## DuckPhp 的优点
### 主要优点
1. DuckPhp 可以做到你的应用和 DuckPhp 的系统代码只有一行关联。 这个是其他 PHP 框架目前都做不到的。你的代码，基本和 DuckPhp 的系统代码无关。
2. DuckPhp 用可变单例方式，解决了【系统的调用形式不变，实现形式可变】，比如不用 hack 来改系统漏洞。而其他框架用的 IoC,DI 技术则复杂且不方便调试。
3. DuckPhp 的应用调试非常方便，堆栈清晰，调用 debug_print_backtrace(2) 很容易发现。那些用了中间件的框架的堆栈很不清晰。
4. DuckPhp 无第三方依赖，你不必担心第三方依赖改动而大费周折。
5. DuckPhp 耦合松散，扩展灵活方便，魔改容易。
6. DuckPhp 是库，可以按 composer 库的方式引入
7. DuckPhp 很容易嵌入其他 PHP 框架。根据 DuckPhp 的返回值判断是否继续后面其他框架。
8. DuckPhp 支持 composer。无 composer 环境也可运。

### DuckPhp 还有以下优点：

* DuckPhp 代码简洁，不做多余事情。最新版本默认demo运行只需要 406/5252 行。
* DuckPhp 的 Controller 切换容易，独立，和其他类无关，简单明了。
* DuckPhp 支持全站路由，还支持局部路径路由和非 PATH_INFO 路由，不需要配服务器也能用。 可以在不修改 Web 服务器设置（如设置 PATH_INFO）的情况下使用，也可以在子目录里使用。
* DuckPhp 的路由也可以单独抽出使用。
* DuckPhp 支持扩展。这些扩展可独立，不一定非要仅仅用于 DuckPHP。
* DuckPhp 的数据库类很简洁，而且，你可以轻易方便的替换。如教程就有使用 thinkphp-db 的例子。
* DuckPhp 有扩展能做到禁止你在 Controller 里直接写 sql 。有时候，框架必须为了防止人犯蠢，而牺牲了性能。但 DuckPhp 这么做几乎不影响性能。
* DuckPhp/Core 是 DuckPhp 的子框架。有时候你用 DuckPhp/Core 也行。类似 lumen 之于 Laravel
* DuckPhp/Core 没有数据类，因为数据库类不是 Web 框架的必备。Laravel 的 ORM 确实很强大。但是意味着和 jquery 那样不可调试。
* DuckPhp 不限制你的工程的命名空间固定为 app.
* DuckPhp 可以规范为，Business 类只能用 MY\Base\BusinessHelper . Controller 类 只能用 MY\Base\ControllerHelper .Model 类只能引用 MY\Base\ModelHepler。 View 类只能用 ViewHelper ，其他类不允许用。也可以规范成 只用 MY\Base\App 类这个系统类。其中 MY 这个命名空间你可以自定义。

### 和其他框架简单对比

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

## 理解 DuckPhp 的原则

DuckPhp 层级关系图

```text
           /-> View-->ViewHelper
Controller --> Business ------------------------------ ---> Model
         \         \   \               \  /                  \
          \         \   \-> (Business)Lib ----> ExModel----------->ModelHelper
           \         \             \                
            \         ---------------->BusinessHelper
             \-->ControllerHelper
```
![arch_full](doc/arch_full.gv.svg)

* Controller 按 URL 入口走 调用 view 和service
* Service 按业务走 ,调用 model 和其他第三方代码。
* Model 按数据库表走，基本上只实现和当前表相关的操作。
* View 按页面走
* 不建议 Model 抛异常
* ControllerHelper,ServiceHelper,ModelHelper,ViewHelper 都为助手类，通常缩写为 C, S, M, V

1. 如果 Service 相互调用怎么办?
添加后缀为 LibService 用于 Service 共享调用，不对外，如MyLibService
2. 如果跨表怎么办?，三种解决方案
    1. 在主表里附加，其他表估计用不到的情况。
    2. 添加后缀为 ExModel 用于表示这个 Model 是多个表的，如 UserExModel。
    3. 或者单独和数据库不一致如取名 UserAndPlayerRelationModel

## DuckPhp 不做什么

* ORM ，和各种屏蔽 sql 的行为，根据日志查 sql 方便多了。 自己简单封装了 pdo 。你也可以使用自己的DB类。 你也可以用第三方ORM（教程最末有替换成 think-orm 的方法）
* 模板引擎，PHP本身就是模板引擎。
* Widget ， 和 MVC 分离违背。
* 接管替代默认的POST，GET，SESSION 。系统提供给你就用，不要折腾这些。 *除非为了支持 swoole*

## DuckPhp 还要做什么

**我真的很需要反馈啊，给我个反馈吧**

* 文档，教程是有了，但还是不太够。
* 范例，例子还太少太简单了。
* 更多的杀手级应用。
## 样例
### 1. hello world
```php
<?php
require_once __DIR__.'/../vendor/autoload.php';

class Main
{
    public function index()
    {
        echo "hello world";
    }
}
$options=[
    'namespace_controller'=>"\\",   // 本例特殊，设置控制器的命名空间为根
    'skip_setting_file'=>true,      // 本例特殊，跳过配置文件
];
DuckPhp\DuckPhp::RunQuickly($options);

```
### 2. 复杂样例

工程附带的模板文件 `template/public/demo.php` 在单一的文件里演示如何使用 `DuckPhp`。

需要注意的是，这个样例是为了演示特性把所有东西集中到一个文件，实际编码不会把所有东西全放在同一个文件里。


```php
<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace
{
    require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
}
// 以下部分是核心工程师写。
namespace MySpace\System
{
    use DuckPhp\Ext\CallableView;
    use DuckPhp\Ext\RouteHookOneFileMode; // 我们要支持无路由的配置模式
    use MySpace\View\Views;

    class App extends \DuckPhp\DuckPhp
    {
        // @override
        public $options = [
            'is_debug' => true,
                // 开启调试模式
            'skip_setting_file' => true,
                // 本例特殊，跳过设置文件 这个选项防止没有上传设置文件到服务器
             
            'ext' => [
                RouteHookOneFileMode::class => true,
                    // 开启单一文件模式，服务器不配置也能运行
                CallableView::class => true,
                    // 默认的 View 不支持函数调用，我们用扩展 CallableView 代替系统的 View
            ],
            'callable_view_class' => Views::class, 
                    // 替换的 View 类。
        ];
        protected function onInit()
        {
            //初始化之后在这里运行。
            //var_dump($this->options);//查看总共多少选项
        }
        protected function onRun()
        {
            //运行期在这里
        }
    }
    //服务基类, 为了 XXService::G() 可变单例。
    class BaseService
    {
        use \DuckPhp\Core\SingletonEx;
    }
} // end namespace
// 助手类
namespace MySpace\Base\Helper
{
    class ControllerHelper extends \DuckPhp\Helper\ControllerHelper
    {
        // 添加你想要的助手函数
    }
    class ServiceHelper extends \DuckPhp\Helper\ServiceHelper
    {
        // 添加你想要的助手函数
    }
    class ModelHelper extends \DuckPhp\Helper\ModelHelper
    {
        // 添加你想要的助手函数
    }
    class ViewHelper extends \DuckPhp\Helper\ViewHelper
    {
        // 添加你想要的助手函数
    }
} // end namespace
//------------------------------
// 以下部分由应用工程师编写，不再和 DuckPhp 的类有任何关系。
namespace MySpace\Controller
{
    use MySpace\Base\Helper\ControllerHelper as C;  // 引用助手类
    use MySpace\Service\MyService;                  // 引用相关服务类

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
            $output = "Hello, now time is " . C::H(MyService::G()->getTimeDesc());
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
namespace MySpace\Service
{
    use MySpace\Base\Helper\ServiceHelper as S;
    use MySpace\Base\BaseService;
    use MySpace\Model\MyModel;

    class MyService extends BaseService
    {
        public function getTimeDesc()
        {
            return "<" . MyModel::getTimeDesc() . ">";
        }
    }

} // end namespace
namespace MySpace\Model
{
    use MySpace\Base\Helper\ModelHelper as M;

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
        public function header($data)
        {
            extract($data); ?>
            <html>
                <head>
                </head>
                <body>
                <header style="border:1px gray solid;">I am Header</header>
    <?php
        }

        public function main_view($data)
        {
            extract($data); ?>
            <h1><?=$output?></h1>
            <a href="<?=$url_about?>">go to "about/me"</a>
    <?php
        }
        public function about_me($data)
        {
            extract($data); ?>
            <h1> OK, go back.</h1>
            <a href="<?=$url_main?>">back</a>
    <?php
        }
        public function footer($data)
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
namespace {
    $options = [
        'namespace' => 'MySpace', //项目命名空间为 MySpace，  你可以随意命名
    ];
    \DuckPhp\DuckPhp::RunQuickly($options);
}

```
## 架构图
![DuckPhp](doc/duckphp.gv.svg)
说明一下

灰色的是公开给外部的类可单独使用的类。



## 还有什么要说的

使用它，鼓励我，让我有写下去的动力


