# DuckPhp\Ext\StaticReplacer
[toc]

## 简介

`组件类` 替换全局静态变量组件。 

## 公开方法

    public function &_GLOBALS($k, $v = null)
global 语句的替代方式

    public function &_STATICS($name, $value = null, $parent = 0)
static 语句的替代方式

    public function &_CLASS_STATICS($class_name, $var_name)
static 类内语句的替代方式

## 详解

常规的操作



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




