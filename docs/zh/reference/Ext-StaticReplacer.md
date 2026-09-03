# DuckPhp\Ext\StaticReplacer

## 简介

`StaticReplacer` 提供“把全局变量 / 函数内静态变量 / 类静态属性”挪到实例存储里仿真的能力，用于测试等需要隔离或可控替换全局状态的场景。三个方法都**按引用返回**，读写方式接近原生结构：

- `_GLOBALS($k, $v)`：仿真 `$GLOBALS[$k]`；
- `_STATICS($name, $value)`：按调用位置（`debug_backtrace`：对象/类/方法）为键仿真的“函数静态变量”；
- `_CLASS_STATICS($class_name, $var_name)`：读取某类静态属性到本地缓存（首读经反射取真值，之后用缓存值）。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class StaticReplacer extends DuckPhp\Core\ComponentBase`

## 使用方式

```php
use DuckPhp\Ext\StaticReplacer;

$sr = StaticReplacer::_();

// 仿真全局变量
$v = &$sr->_GLOBALS('counter');
$v++;

// 仿真函数内静态（键含调用位置）
$x = &$sr->_STATICS('cache', null);

// 读/替换某类静态属性
$old = &$sr->_CLASS_STATICS(MyClass::class, 'property');
```

## 注意事项

- `_STATICS` 的键由 `debug_backtrace` 推导（对象 hash + 类 + 类型 + 函数名），因此**不同调用位置**即使同名也是不同槽位；`$parent` 参数可指定回溯层。
- `_CLASS_STATICS` 只在首次通过反射读取真实值，此后返回本地副本——**对副本的修改不会写回真实类静态属性**（适合读隔离）。
- 类为普通组件实例，状态跨请求持久与否取决于组件容器生命周期。

## 方法列表

### 公共方法

    public function &_GLOBALS(string $k, $v = null)
按引用取/建一个“全局变量”槽位。

    public function &_STATICS(string $name, $value = null, int $parent = 0)
按引用取/建一个“函数静态变量”槽位（键含调用上下文）。

    public function &_CLASS_STATICS(string $class_name, string $var_name)
按引用取某类静态属性的本地缓存（首次经反射读取）。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 组件基类
