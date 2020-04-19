# DuckPHP 教程
## 第一章 快速入门
### 安装
假定不管什么原因，选用了 DuckPHP 这个框架，需要快速入门.

最快的方式是从 github 下载 DuckPHP。

到所在目录之下运行

```bash
php template/bin/start_server.php
```
浏览器中打开 http://127.0.0.1:8080/ 得到下面欢迎页就表明 OK 了
```text
Don't run the template file directly
Hello DuckPHP

Time Now is [2019-04-19T21:36:06+08:00]
For More Take the DuckPHP-FullTest (TODO)
```
发布的时候，把网站目录指向 public/index.php 就行。
### 另一种安装模式： Composer 安装
在工程目录下执行：
```
composer require dvaknheo/duckphp # 用 require 
./vendor/bin/duckphp --help     # 查看有什么指令
./vendor/bin/duckphp --create   # --full # --force # 创建工程
./start_server.php    # --host=127.0.0.1 --port=9527 # 开始 web 服务器

```
将会直接把 template 的东西复制到工程并做调整，同样执行
```bash
php bin/start_server.php
```
浏览器中打开 http://127.0.0.1:9527/ 得到下面欢迎页就表明 OK 了
```text
Don't run the template file directly
Hello DuckPHP

Time Now is [2019-04-19T21:36:06+08:00]
For More Take the DuckPHP-FullTest (TODO)
```
细则可以看 --help 参数

当然你也可以用 nginx 或apache 安装。
nginx 把 document_root 配置成 `public` 目录。

```
try_files $uri $uri/ /index.php$request_uri;
```
### 第一个任务
路径： http://127.0.0.1:8080/test/done 
作用是显示当前时间的任务。

对照目录结构我们要加个 test/done 显示当前时间

都在各代码段里注释了文件所在相对工程目录的位置

### View 视图
先做出要显示的样子。
```php
<?php // view/test/done.php ?>
<!doctype html><html><body>
<h1>test</h1>
<div><?=$var ?></div>
</body></html>
```
### Controller控制器
写 /test/done 控制器对应的内容。
```php
<?php
// app/Controller/test.php
namespace MY\Controller;

// use MY\Base\BaseController;
use MY\Base\Helper\ControllerHelper as C;
use MY\Service\MiscService;

class test // extends BaseController
{
    public function done()
    {
        $data=[];
        $data['var']=C::H(MiscService::G()->foo());
        C::Show($data); // C::Show($data,'test/done');
    }
}
```
控制器里，我们处理外部数据，不做业务逻辑，业务逻辑在 Service 层做。

BaseController  这个基类，如果不强制要求也可以不用。

MY 这个命名空间前缀可在选项 ['namespace'] 中变更。

C::H 用来做 html编码。

C::Show($data); 是 C::Show($data,'test/done'); 的缩写， 调用 test/done 这个视图。

### Service 服务
业务逻辑层。根据业务逻辑来命名。
```php
<?php
// app/Service/MiscService.php
namespace MY\Service;

use MY\Base\Helper\ServiceHelper as S;
use MY\Base\BaseService;
use MY\Model\MiscModel;

class MiscService  extends BaseService
{
    public function foo()
    {
        $time=MiscModel::G()->getTime();
        $ret="<".$time.">";
        return $ret;
    }
}
```
BaseService 也是不强求的，我们 extends BaseService 是为了能用 G 函数这个单例方法。

这里调用了 MiscModel 。

### Model 模型

完成 MiscModel 。

Model 类是实现基本功能的。一般 Model 类的命名是和数据库表一致的。

```php
<?php
// app/Model/MiscModel.php
namespace MY\Model;

use MY\Base\BaseModel;
use MY\Base\Helper\ModelHelper as M;

class MiscModel extends BaseModel
{
    public function getTime()
    {
        return DATE(DATE_ATOM);
    }
}
```
同样 BaseModel 也是不强求的，我们 extends BaseModel 是为了能用 G 函数这个单例方法。

### 最后显示结果
```text
test

<2019-04-19T22:21:49+08:00>
```
### 如果没有配置 PATH_INFO
如果你懒得配置 PATH_INFO，把 `public/index.php` 文件这项打开
```php
$options['ext']['DuckPhp\\Ext\\RouteHookeOneFile']=true;
```
同样访问  http://127.0.0.1:8080/index.php?_r=test/done  也是得到想同测试页面的结果

### 数据库操作
前提工作，我们注释掉 `public/index.php` 中跳过设置文件的选项
```php
//$options['skip_setting_file']=true;
```
`./vendor/bin/duckphp --create` 脚本会删去这一行。

数据库演示需要数据库配置。

我们复制 `config/setting.sample.php` 为 `config/setting.php`
```php
return [
    'duckphp_is_debug' => false,
    'duckphp_platform' => 'default',
    //*
    'database_list' => [
        [
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8mb4;',
        'username' => 'admin',
        'password' => '123456',
        'driver_options' => [],
        ],
    ],
    //*/
];
```
然后，我们写 `app/Controller/dbtest.php` 如下
```php
namespace MY\Controller;
use MY\Base\App as M;

class dbtest
{
    public function main()
    {
        $ret = $this->foo();
        var_dump($ret);
    }
    public function foo()
    {
        if (M::DB()===null) {
            var_dump("No database setting!");
            return;
        }
        $sql = "select 1+? as t";
        $ret = M::DB()->fetch($sql,2);
        return $ret;
    }
}
```
访问   http://127.0.0.1:8080/dbtest/main 
会得到

```
array('t'=>3);
```

### 快速入门演示了什么
文件型路由，分层思维
### 快速入门没演示什么

异常处理，扩展 等高级内容



##### "ext" 选项和扩展

ext 是一个选项，这里单独成一节是因为这个选项很重要。涉及到 DuckPHP 的扩展系统

在 DuckPHP/Core/App 里， ext 是个空数组。
**重要选项**

    扩展映射 ,$ext_class => $options。
    
    $ext_class 为扩展的类名，如果找不到扩展类则不启用。
    
    $ext_class 满足接口。
    $ext_class->init(array $options,$context=null);
    
    如果 $options 为  false 则不启用，
    如果 $options 为 true ，则会把当前 $options 传递进去。
DuckPHP/Core 的 Configer, Route, View, AutoLoader 默认都在这调用


### 入口类 App 类的方法 以及高级程序员。

MY\Base\App 是 继承扩展 DuckPHP\App 类的方法。

DuckPHP\App 类或其子类在初始化之后，会切换入这个子类走后面的流程。

DuckPHP\App 包含所有助手类的方法。所有助手方法都在 DuckPHP\App 里实现

#### 用于 override 的两个重要方法

onInit()

    用于初始化，你可能会在这里再次调整  $this->options。
    你可以在调用父类的初始化前后做一些操作
onRun(): void

    用于运行前，做一些你想做的事 ，和 onInit 不同，你不用调用父类
RunQuickly(): bool

    前面已经介绍
#### 聚合方法

    ModelHelper,SerivceHelper,ControllerHelper,ViewHelper 都在 App 类里实现。
    这用于你想偷懒，直接 App::foo(); 的情况。
#### 接管的静态方法
    App::On404();
    App::OnException(): void
    App::OnDevErrorHandler():void 
    App::IsRunning();
    App::InException();

#### 常用方法
这些方法不能归入 助手类里，只能在 App 类单独给出的。

addRouteHook($hook,$position,$once=true)

    添加路由钩子 
    $hook 返回空用默认路由处理，否则调用返回的回调。
    $position 包括位置['prepend-outter','prepend-inner',]
addBeforeShowHandler($callback)

    添加显示前处理
extendComponents($class,$methods,$components);

    扩展组件的静态方法。
    其中： $components 为 ['M','V','C','S','A'] 组合可选。
#### 公开动态方法

    App->init();
    App->run();
    
    App->extendComponents();
    App->assignPathNamespace();
    App->addRouteHook();
#### 内部可扩展方法
    App->initOptions();
    App->checkOverride();
    App->initExtentions();
    App->reloadFlags();
    App::system_wrapper_get_providers();
    App::session_set_save_handler();
#### 下划线开始的动态方法
    下划线开始的动态方法，表示的是内部，但特殊情况下会被外部调用的方法。
    有些会对应无下划线的静态方法，用于实现。 用于接管的状态。


#### 其他方法

    其他方法有待你的发掘。如果你要用于特殊用处的话。
    目前一共有 32 个 public function ,36 public static function ,10 protected function 
    还有 +3 来自 ExtendableStaticCallTrait 方法。
    79 个方法。

### 请求流程和生命周期
DuckPHP\App::RunQuickly($options) 发生了什么

DuckPHP\App::G()->init($options,$callback)->run();

init 为初始化阶段 ，run 为运行阶段。$callback 在init() 之后执行（也是为了偷懒

init 出事化阶段
    处理是否是插件模式
    处理自动加载  AutoLoader::G()->init($options, $this)->run();
    处理异常管理 ExceptionManager::G()->init($exception_options, $this)->run();
    如果有子类，切入子类继续 checkOverride() 
    调整补齐选项 initOptions()
    

    * onInit()，可 override 处理这里了。
    默认的 onInit
        初始化 Configer
        从 Configer 再设置 是否调试状态和平台 reloadFlags();
        初始化 View
        设置为已载入 View ，用于发生异常时候的显示。
        初始化 Route
        初始化扩展 initExtentions()
    初始化阶段就结束了。

run() 运行阶段

    处理 addBeforeRunHandler() 引入的 beforeRunHandlers
    * onRun ，可 override 处理这里了。
    重制 RuntimeState 并设置为开始
    绑定路由
    ** 开始路由处理 Route::G()->run();
    如果返回 404 则 On404() 处理 404
    clear 清理
        如果没显示，而且还有 beforeShowHandlers() 处理（用于处理 DB 关闭等
        设置 RuntimeState 为结束

## 第三章 DuckPHP 核心开发和扩展参考

#### 可变单例 G 方法
这里，对之前的 G 方法统一说明
G 方法表面上是个单例函数，实际上的可替换的。
DuckPHP 系统组件的连接，多是以调用类的可变单例来实现的。

### 结构图
![core](DuckPHP.gv.svg)

##### 架构分析

Core 目录 核心框架

