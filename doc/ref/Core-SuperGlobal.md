# DuckPhp\Core\SuperGlobal
[toc]

## 简介

`组件类` 超全局变量组件。 为了支持不仅仅 WEB 环境下的 超全局变量。

## 公开方法
public function __construct()

    构造函数和组件类不同， SuperGlobal 在初始化的时候就已经自己 init 。你可以用 reset 清除 init 状态。
public function init(array $options, object $context = null)

    SuperGlobal 没用到任何选项。
public function reset()

    重置初始化状态
public function session_start(array $options=[])

    接管系统相应函数
public function session_id($session_id)

    接管系统相应函数
public function session_destroy()

    接管系统相应函数
public function session_set_save_handler($handler)

    接管系统相应函数
public function &_GLOBALS($k, $v=null)

    global 语句的替代方式
public function &_STATICS($name, $value=null, $parent=0)

    static 语句的替代方式
public function &_CLASS_STATICS($class_name, $var_name)

    static 类内语句的替代方式
## 详解
常规的操作
```
$_GET['x'] => SuperGlobal::G()->_GET['x'];
```


函数 _GLOBALS _STATICS _CLASS_STATICS 用于兼容协程环境。

```php
global $a='val'; =>  $a=App::GLOBALS('a','val');
```
静态变量 
```php
static $a='val'; =>  $a=App::STATICS('a','val');
```
类内静态变量
```php
$x=static::$abc; => $x=App::CLASS_STATICS(static::class,'abc');

```
注意到 _STATICS 最后一个参数，每加一级调用，这个数字要加一。
