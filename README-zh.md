# DuckPhp
***v1.3.1 版本改进
作者QQ: 85811616
官方QQ群: 714610448

Gitee 仓库地址：https://gitee.com/dvaknheo/duckphp
Github 仓库地址：https://github.com/dvaknheo/duckphp

## 一、DuckPhp 是什么

DuckPhp 是一个零依赖、全组件可替换、部署与协作都极其灵活的库模式的 PHP 框架。

DuckPhp is a library-style PHP framework that offers zero dependencies, fully replaceable components, and exceptional flexibility in deployment and teamwork.

DuckPhp 的名字来源：

`Duck Typing` If it walks like a duck, swims like a duck, and quacks like a duck, then it probably is a duck. 

`鸭子类型`，这东西看起来像鸭子，叫起来像鸭子，所以就是鸭子。


##  二、优点详细说明

DuckPhp 是一个零依赖、全组件可替换、部署与协作都极其灵活的库模式的 PHP 框架。
DuckPhp is a library-style PHP framework that offers zero dependencies, fully replaceable components, and exceptional flexibility in deployment and teamwork.


### Composer 安装

```
composer require dvaknheo/duckphp # 用 require 

```
由此可以看出 duckphp 是库模式的框架，而不是一堆库创建起来的

DuckPhp 以库方式引入，所以 DuckPhp 工程骨架不像其他框架那样一大堆不可删除的文件

DuckPhp 零依赖，你不必担心第三方依赖改动而大费周折。**不需要引入101 个第三方包，就能工作**，稳定性完全可控。


### 样例一

最简单的例子。你只是想做个不要验证的 api。那你就写个api.php 文件

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne as DuckPhpAllInOne;
use DuckPhp\DuckPhpAllInOne as Helper;
class MyHello extends DuckPhpAllInOne
{
	public function action_index()
	{
		$words = __h("<b>Hello, this is all in one</b>");
        Helper::Show(['words'=>$words],'main');
	}
    public function view_main($data)
    {
        echo $data['words'];
    }
}
$options = [
    // 'is_debug'=>true,
];
MyHello::RunQuickly($options);

```

说明：

这里入口用的是 DuckPhp\DuckPhpAllInOne 类，把东西封装成一个类的模式 DuckPhp\DuckPhp 。
你可以看到 `use DuckPhp\DuckPhpAllInOne as Helper;`  这里 Helper 类把后面所有类封装一起了
还演示的了__h()函数

流程是 action_index => sho =>view_main


由例子，我们引申出DuckPhp特点：

DuckPhp 不限制你的工程的命名空间。

> 示例代码就是用  MyHello 开始的命名空间

DuckPhp 的配置基本都是使用默认方式。 不需要一大堆的配置文件。DuckPhp 工程有很多个选项调整得到不同的结果。

> 这里的 $options 就是应用选项，你可以打开调试模式。 具体你可以查看文档

DuckPhp 无侵入，杜绝全局函数冲突引发的问题
> 只有少数几个 __ 开始的全局函数，你也可以覆盖他们


### 样例二 插入其他工程

DuckPhp 可以把你的工程直接插入其他工程，不用修改。 你不需要在 DuckPhp 工程上做二次开发。

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne;

class MyApi2 extends DuckPhpAllInOne
{
	public function action_index()
	{
		echo "I'm child.";
	}
}
class MyApi extends DuckPhpAllInOne
{
	public $options = [
		'app'=>[
			MyApi2::class=>[
                'controller_url_prefix' => 'child/',
			],
		]
	];
	public function action_index()
	{
		$url_child = __url('child/index');
		echo "I'm Parent. Goto <a href='{$url_child}'>child</a>";
	}
}

MyApi::RunQuickly();
```


在这里  MyApi2 MyApi 都是独立的 DuckPhp 应用。 MyApi 把 MyApi2 作为子应用

当你懒得为你的api 写用户系统，你可以把 DuckAdmin 工程的用户系统就这么插入，然后用 Helper::UserId() 获得用户ID。

> 子应用其实是个很复杂的活，涉及到静态资源，应用通讯，应用的组件共享等


这里还体现 DuckPhp 另一个特点

DuckPhp 支持全站路由，还支持局部路径路由和无 PATH_INFO 路由，不需要配服务器也能用。 可以在不修改 Web 服务器设置（如设置 PATH_INFO）的情况下使用，也可以在子目录里使用。
> DuckPhpAllInOne 相比 DuckPhp类，默认启用无 PATH_INFO 路由
> DuckPhp 通过 dvaknheo/workermanhttpd 扩展，支持 workerman 。不需要改工程代码，将来也支持 更多其他平台。
> 不建议使用命令行的 web 服务器， 你把 nginx 或 apache 的 document_root 设置为  public 目录按常规框架调整即可。



### 样例三 组件替换

这个样例没前面那么大价值，但是体现了DukPhp 的灵活性 的例子


DuckPhp 作为一个现代的 PHP 库， 全组件可替换是必须的。

DuckPhp 用可变单例方式，解决了**系统的调用形式不变，实现形式可变**，不需要魔改来修复系统漏洞。而其他框架用的 IoC,DI 技术则复杂且不方便调试。
> 如果对默认实现不满，你也可以很容易改用需要第三方依赖的实现。



DuckPhp 的应用调试非常方便，堆栈清晰，调用 debug_print_backtrace(2) 很容易发现。那些用了中间件的框架的堆栈很不清晰。
> 调试用 `__trace_dump()`

## 正常的工程文件模式

//TODO，嵌入指南里 文件结构那一章节


DuckPhp 工程层级分明，不交叉引用。

DuckPhp 的使用者角色分为 `业务工程师`和`核心工程师`。`业务工程师` 只需要要研究业务代码。`核心工程师` 才需要研究做系统核心代码。

> 基本上，看完助手类教程，`业务工程师`就可以开干了。不懂的东西，问`核心工程师`吧。


## 工程上意义不大的优点





DuckPhp 支持 composer。无 composer 环境也可运行。DuckPhp 是 Composer 库，不需要单独的脚手架工程。
> 拥有自己 loader 但工程上意义不大。

DuckPhp/Core/App 是 DuckPhp 的子框架。有时候你用 DuckPhp/Core/App 也行。

DuckPhp 的 Controller 切换容易，独立，和其他类无关，简单明了。

DuckPhp 的路由也可以单独抽出使用。

> 实际工程上这三项没多大意义，现在基本没人会单独拆出来使用。

DuckPhp 支持扩展。这些扩展可独立，不一定非要仅仅用于 DuckPhp 。

> 工程上意义不大，只要支持 init([],$context) 都算

DuckPhp 可以做到你的应用和 DuckPhp 的系统代码只有一行关联。 这个是其他 PHP 框架目前都做不到的。你的业务代码，基本和 DuckPhp 的系统代码无关。你只要研究业务代码，不要研究框架代码。

> 通过修改选项实现


DuckPhp 有扩展能做到禁止你在 Controller 里直接写 sql 。有时候，框架必须为了防止人犯蠢，而牺牲了性能。但 DuckPhp 这么做几乎不影响性能。

> 只是现在没多大作用


DuckPhp  耦合松散，扩展灵活方便，魔改容易。

>DuckPhp 的数据库类很简洁，而且，你可以轻易方便的替换。

DuckPhp 的类尽量无状态。

DuckPhp 各组件是无直接引用的，所以 var_dump(AnyComponet::_()) 能看出来。


// 开发组理念 

DuckPhp 代码简洁，不做多余事情。最新版本默认 demo 运行根据 CodeCoverage 覆盖统计， 只需要行数 376 / 4381 (v1.2.13-dev)  执行行数/总可执行行数  。

DuckPhp 框架的设计原则：这东西非得框架自带么，不自带行么。

DuckPhp 因为作者强迫症，每次发布都是通过全代码覆盖的测试，因此有很大健壮性。



DuckPhp 没有 ORM ，和各种屏蔽 sql 的行为，根据日志查 sql 方便多了。 自己简单封装了 pdo 。你也可以使用自己的DB类。 你也可以用第三方ORM（教程就有使用 thinkphp-db 的例子。[链接](docs/tutorial-db.db)）

DuckPhp 不带 模板引擎，PHP本身就是模板引擎。

DuckPhp 不写 Widget ， 和 MVC 分离违背。

## 十二、DuckPhp 还要做什么

**我真的很需要反馈啊，给我个反馈吧**
* 更多的杀手级应用。



## DuckPhp 的版本历程
起初，这是是想搞个简单的 PHP Web 简单框架 。现在是使用方式简单，实际方式不简单。

+ 1.0.\* 系列版本是前身 DNMVCS 单文件模式的版本
+ 1.1.\* 系列版本是前身 DNMVCS 拆分成多文件的版本
+ 1.2.\* 系列版本是改名 DuckPhp 后的版本，随着思想的变化，或许会有大的变更
+ 1.3.\* 系列版本将是计划开始有人大规模使用后的稳定版本，将会对历史负责了。

1.3 版本，最大的变化是增加了相位概念，使得各应用之间相互插入也无影响
1.3.4 ，增加了docker 支持，修复了多语言支持, 为 1.3.5 准备

使用它，鼓励我，让我有写下去的动力

![logo](duckphp.jpg)
