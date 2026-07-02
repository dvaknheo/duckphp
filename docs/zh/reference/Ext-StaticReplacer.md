# DuckPhp\Ext\StaticReplacer

> ⚠️ 警告：该扩展是实验性的或已废弃，不建议在新项目中使用。

## 简介

`StaticReplacer` 是一个用于替换全局变量、静态变量和类静态变量的实验性组件。它允许在测试或特定场景下替换这些变量，但实现依赖反射和 `debug_backtrace`，不够稳定，不建议在新项目中使用。

## 选项

无。

## 使用方式

### 替换全局变量

```php
use DuckPhp\Ext\StaticReplacer;

$GLOBALS = &StaticReplacer::_()->_GLOBALS('test_key', 'default_value');
$GLOBALS = 'new_value';
```

### 替换函数/方法内的静态变量

```php
function myFunction()
{
    $static_var = &DuckPhp\Ext\StaticReplacer::_()->_STATICS('my_var', 'default_value', 0);
}
```

### 替换类静态变量

```php
use DuckPhp\Ext\StaticReplacer;

$staticProp = &StaticReplacer::_()->_CLASS_STATICS(SomeClass::class, 'staticProperty');
$staticProp = 'new_value';
```

## 配置示例

无。

## 注意事项

1. 该组件主要用于测试场景下的变量替换，不建议用于生产代码。
2. `_STATICS()` 依赖 `debug_backtrace()` 分析调用栈，可能受 PHP 优化设置影响。
3. `_CLASS_STATICS()` 使用反射读取类静态属性，如果属性不存在会抛出异常。
4. 变量引用返回需要注意引用生命周期，避免意外修改。
5. 源码中 TODO 标记了未完成的 `Replace` 功能。

## 方法列表

### 公共方法

    public function &_GLOBALS($k, $v = null)
获取或初始化一个全局变量替换值。返回引用，允许外部修改。

    public function &_STATICS($name, $value = null, $parent = 0)
根据调用栈信息获取或初始化一个静态变量替换值。返回引用。

    public function &_CLASS_STATICS($class_name, $var_name)
通过反射获取或初始化一个类静态属性替换值。返回引用。

## 相关链接

无。
