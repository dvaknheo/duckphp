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

@script File: `template/app/Controller/test.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

use LazyToChange\Helper\ControllerHelper as C;
use LazyToChange\Business\DemoBusiness;

class test
{
    public function done()
    {
        $var = DemoBusiness::G()->foo();
        C::Show(get_defined_vars());
    }
}

```
控制器里，我们处理外部数据，不做业务逻辑，业务逻辑在 Business 层做。

BaseController  这个基类，如果不强制要求也可以不用。

LazyToChange 这个命名空间前缀是工程命名前缀，怎么修改先略过。

C::H 用来做 html编码。

C::Show($data); 是 C::Show($data,'test/done'); 的缩写， 调用 test/done 这个视图。

### Business 业务层

业务逻辑层。根据业务逻辑来命名。

@script File: `template/app/Business/DemoBusiness.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Business;
use LazyToChange\Helper\BusinessHelper as B;

use LazyToChange\Model\DemoModel;

class DemoBusiness extends BaseBusiness
{
    public function foo()
    {
        return "<" . DemoModel::G()->foo().">";
    }
}

```
BaseBusiness也是不强求的，我们 extends BaseBusiness 是为了能用 DemoBusiness::G() 可变单例。

这里调用了 MiscModel 。

### Model 模型

完成 MiscModel 。

Model 类是实现基本功能的。一般 Model 类的命名是和数据库表一致的。

@script File: `template/app/Model/DemoModel.php`

```php
<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Model;

use LazyToChange\Model\BaseModel;
// use LazyToChange\Helper\ModelHelper as M;

class DemoModel extends BaseModel
{
    public function foo()
    {
        return DATE(DATE_ATOM);
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
'use_setting_file' => true,
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


--
