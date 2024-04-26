# DuckPhp 教程
[toc]
## 第一章 快速入门
### 安装
假定不管什么原因，选用了 DuckPhp 这个框架，需要快速入门.

最快的方式是从 github 下载 DuckPhp。

到所在目录之下运行

```bash
cd template
php ./duckphp-project run
```
浏览器中打开 http://127.0.0.1:8080/ 得到下面欢迎页就表明 OK 了
```text
Don't run the template file directly
Hello DuckPhp

Now is [<2020-06-14T11:45:46+08:00>]
--
(省略后面内容)
```
发布的时候，把网站目录指向 public/index.php 就行。
### 另一种安装模式： Composer 安装
在工程目录下执行：

```
composer require dvaknheo/duckphp # 用 require 
./vendor/bin/duckphp new --help   # 查看有什么指令
./vendor/bin/duckphp new    # 创建工程
./vendor/bin/duckphp run    # --host=127.0.0.1 --port=9527 # 开始 web 服务器
```
浏览器中打开 http://127.0.0.1:8080/ 得到下面欢迎页就表明 OK 了

```text
Hello DuckPhp

Now is [<2020-06-14T11:45:46+08:00>]
--
(省略后面内容)
```

当然你也可以用 nginx 或apache 安装。
nginx 把 document_root 配置成 `public` 目录。

nginx 的配置：

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

@script File: `template/view/test/done.php`

```php
<?php declare(strict_types=1);
// view/test/done.php?>
<!doctype html><html><body>
<h1>test</h1>
<div><?=$var ?></div>
</body></html>
```
### Controller控制器
写 /test/done 控制器对应的内容。

@script File: `template/src/Controller/testController.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Controller;

use ProjectNameTemplate\Business\DemoBusiness;

class testController
{
    public function action_done()
    {
        $var = DemoBusiness::_()->foo();
        Helper::Show(get_defined_vars());
    }
}

```
控制器里，我们处理外部数据，不做业务逻辑，业务逻辑在 Business 层做。

BaseController  这个基类，如果不强制要求也可以不用。

LazyToChange 这个命名空间前缀是工程命名前缀，怎么修改先略过。

use LazyToChange\Helper\ControllerHelper as C; C 是助手类

C::Show($data); 是 C::Show($data,'test/done'); 的缩写， 调用 test/done 这个视图。

### Business 业务层

业务逻辑层。根据业务逻辑来命名。

@script File: `template/src/Business/DemoBusiness.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Business;

use ProjectNameTemplate\Business\Base;
use ProjectNameTemplate\Business\Helper;
use ProjectNameTemplate\Model\DemoModel;

class DemoBusiness extends Base
{
    public function foo()
    {
        return "<" . DemoModel::_()->foo().">";
    }
    public function getDocData($f)
    {
        $ref = new \ReflectionClass(\DuckPhp\DuckPhp::class);
        $path = realpath(dirname($ref->getFileName()) . '/../docs').'/';
        $file = realpath($path.$f);
        if (substr($file, 0, strlen($path)) != $path) {
            return '';
        }
        $str = file_get_contents($file);
        if (substr($file, -3) === '.md') {
            $str = preg_replace('/([a-z_]+\.gv\.svg)/', "?f=$1", $str); // gv file to md file
        }
        return $str;
    }
    public function testdb()
    {
        return DemoModel::_()->testdb();
    }
}

```
BaseBusiness 也是不强求的，我们 extends BaseBusiness 是为了能用 DemoBusiness::G() 可变单例。

这里调用了 MiscModel 。

### Model 模型

完成 MiscModel 。

Model 类是实现基本功能的。一般 Model 类的命名是和数据库表一致的。

@script File: `template/src/Model/DemoModel.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Model;

use ProjectNameTemplate\Model\Base;
use ProjectNameTemplate\Model\Helper;

class DemoModel extends Base
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
    public function testdb()
    {
        $sql = "select 1+? as t";
        $ret = Helper::Db()->fetch($sql, 2);
        return $ret;
    }
}

```
同样 BaseModel 也是不强求的，我们 extends BaseModel 是为了能用 DemoModel::G() 可变单例。

### 最后显示结果
```text
test

<2019-04-19T22:21:49+08:00>
```
### 如果没有配置 PATH_INFO
如果你懒得配置 PATH_INFO，把 `app/System/App.php` 文件选项打开
```php
'path_info_compact_enable' => false, 
```
同样访问  http://127.0.0.1:8080/index.php?_r=test/done  也是得到想同测试页面的结果

### 数据库操作
前提工作，我们加上 `app/System/App.php` 中跳过设置文件的选项打开
```php
'setting_file_enable' => true,
```


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
namespace LazyToChange\Controller;
use LazyToChange\System\App as M;

class dbtest
{
    public function main()
    {
        $ret = $this->foo();
        var_dump($ret);
    }
    public function foo()
    {
        if (M::Db()===null) {
            var_dump("No database setting!");
            return;
        }
        $sql = "select 1+? as t";
        $ret = M::Db()->fetch($sql,2);
        return $ret;
    }
}
```
访问   http://127.0.0.1:8080/dbtest/main 
会得到

```
array('t'=>3);
```
`M::DB()`  的几个方法  `fetch` `fetchAll`,`execute` 和 pdo 类似


### 快速入门演示了什么

文件型路由，分层思维

### 快速入门没演示什么

异常处理，扩展 等高级内容

## 了解更多

查看 [**文档索引页**](index.md) ,所有文档索引页面，所有文档的集合入口

--
array (
  0 => 'autoload.php',                          // 无composer时的加载文件
  1 => 'src/Component/DbManager.php',           // Db管理器
  2 => 'src/Component/ExtOptionsLoader.php',    // 加载额外选项（来自 DuckPhpApps.config.php
  3 => 'src/Component/GlobalAdmin.php',         // 全局管理员
  4 => 'src/Component/GlobalUser.php',          // 全局数据库
  5 => 'src/Component/RedisManager.php',        // Redis管理器
  6 => 'src/Component/RouteHookResource.php',   // 资源文件钩子
  7 => 'src/Component/RouteHookRewrite.php',    // URL重写钩子
  8 => 'src/Component/RouteHookRouteMap.php',   // 路由映射钩子
  9 => 'src/Component/ZCallTrait.php',          // 跨相位调用
  10 => 'src/Core/App.php',                     // 核心基类
  11 => 'src/Core/AutoLoader.php',              // 加载器
  12 => 'src/Core/ComponentBase.php',           // 组件基类
  13 => 'src/Core/Console.php',                 // 命令行处理
  14 => 'src/Core/EventManager.php',            // 事件管理
  15 => 'src/Core/ExceptionManager.php',        // 异常管理
  16 => 'src/Core/Functions.php',               // 全局函数
  17 => 'src/Core/KernelTrait.php',             // 核心 Trait
  18 => 'src/Core/Logger.php',                  // 日志
  19 => 'src/Core/PhaseContainer.php',          // 相位管理器
  20 => 'src/Core/Route.php',                   // 路由
  21 => 'src/Core/Runtime.php',                 // 运行时
  22 => 'src/Core/SingletonTrait.php',          // 单例模式
  23 => 'src/Core/SuperGlobal.php',             // 全局变量
  24 => 'src/Core/SystemWrapper.php',           // 接管系统函数
  25 => 'src/Core/View.php',                    // 视图
  26 => 'src/DuckPhp.php',                      // 入口类
  27 => 'src/Ext/CallableView.php',             // 扩展，调用模式的视图
  28 => 'src/Foundation/SimpleBusinessTrait.php',   // 业务类Trait
  29 => 'src/Foundation/SimpleControllerTrait.php', // 控制器类Trait
  30 => 'src/Foundation/SimpleModelTrait.php',      // 模型类Trait
  31 => 'src/Foundation/SimpleSingletonTrait.php',  // 简单的单例 Trait
  32 => 'src/Helper/ControllerHelperTrait.php',     // 控制器 Trait
  33 => 'template/config/DuckPhpApps.config.php',   // 业务类Trait
  34 => 'template/config/DuckPhpSettings.config.php',   // 业务类Trait
  
  35 => 'template/public/dbtest.php',               // 我们调用了额外应用的 dbtest 类
  36 => 'template/public/index.php',                 // 入口类
  37 => 'template/src/Controller/Base.php',         // 控制器基类
  38 => 'template/src/Controller/Helper.php',       //
  39 => 'template/src/Controller/MainController.php',
  40 => 'template/src/System/App.php',
  41 => 'template/view/files.php',
)

--

  0 => 'autoload.php',                          // 无composer时的加载文件
  11 => 'src/Core/AutoLoader.php',              // 加载器

  17 => 'src/Core/KernelTrait.php',             // 核心 Trait
  10 => 'src/Core/App.php',                     // 核心基类
  12 => 'src/Core/ComponentBase.php',           // 组件基类
  22 => 'src/Core/SingletonTrait.php',          // 单例模式
  16 => 'src/Core/Functions.php',               // 全局函数
  19 => 'src/Core/PhaseContainer.php',          // 相位管理器
  13 => 'src/Core/Console.php',                 // 命令行处理

  14 => 'src/Core/EventManager.php',            // 事件管理
  15 => 'src/Core/ExceptionManager.php',        // 异常管理
  20 => 'src/Core/Route.php',                   // 路由
  21 => 'src/Core/Runtime.php',                 // 运行时
  25 => 'src/Core/View.php',                    // 视图
  
  18 => 'src/Core/Logger.php',                  // 日志
  23 => 'src/Core/SuperGlobal.php',             // 全局变量
  24 => 'src/Core/SystemWrapper.php',           // 接管系统函数
  
  2 => 'src/Component/ExtOptionsLoader.php',    // 加载额外选项（来自 DuckPhpApps.config.php


  26 => 'src/DuckPhp.php',                      // 入口类
  6 => 'src/Component/RouteHookResource.php',   // 资源文件钩子
  7 => 'src/Component/RouteHookRewrite.php',    // URL重写钩子
  8 => 'src/Component/RouteHookRouteMap.php',   // 路由映射钩子
  9 => 'src/Component/ZCallTrait.php',          // 跨相位调用

  1 => 'src/Component/DbManager.php',           // Db管理器
  5 => 'src/Component/RedisManager.php',        // Redis管理器
  
  3 => 'src/Component/GlobalAdmin.php',         // 全局管理员
  4 => 'src/Component/GlobalUser.php',          // 全局数据库
  
  28 => 'src/Foundation/SimpleBusinessTrait.php',   // 业务类Trait
  29 => 'src/Foundation/SimpleControllerTrait.php', // 控制器类Trait
  30 => 'src/Foundation/SimpleModelTrait.php',      // 模型类Trait
  31 => 'src/Foundation/SimpleSingletonTrait.php',  // 简单的单例 Trait
  
  32 => 'src/Helper/ControllerHelperTrait.php',     // 控制器 Trait

  
  33 => 'template/config/DuckPhpApps.config.php',   // 业务类Trait
  34 => 'template/config/DuckPhpSettings.config.php',   // 业务类Trait
  
  
  36 => 'template/public/index.php',                 // 入口类
  
  37 => 'template/src/Controller/Base.php',         // 控制器基类
  38 => 'template/src/Controller/Helper.php',       //
  39 => 'template/src/Controller/MainController.php',
  
  40 => 'template/src/System/App.php',
  41 => 'template/view/files.php',

  27 => 'src/Ext/CallableView.php',             // 扩展，调用模式的视图
  35 => 'template/public/dbtest.php',               // 我们调用了额外应用的 dbtest 类

为什么安装要用 命令行模式 ，因为命令行模式，可以超越 web 用户的安全权限。 web 用户忽略安全权限可是不好
