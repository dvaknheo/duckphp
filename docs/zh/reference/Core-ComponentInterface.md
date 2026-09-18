# DuckPhp\Core\ComponentInterface

## 简介

`ComponentInterface` 是 DuckPHP 组件的契约接口，规定了“框架内一个组件必须具备哪些公开入口”：

- 一个单例式静态入口 `_()`；
- 一个统一的初始化方法 `init()`；
- 一个初始化状态查询 `isInited()`。

框架中的 `ComponentBase` 在源码中以注释形式（`class ComponentBase // implements ComponentInterface`）声明实现了本接口的全部方法，因此绝大多数实际组件并不直接 `implements` 本接口，而是继承 `ComponentBase`。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`interface ComponentInterface`

## 使用方式

一般业务代码不需要直接面向本接口编程。需要让某个类具备“组件形态”时，继承 `ComponentBase` 即可；若你想编写一个完全不依赖 `ComponentBase` 的组件，则实现本接口的三个方法：

```php
namespace My\Component;

use DuckPhp\Core\ComponentInterface;

class MyComponent implements ComponentInterface
{
    public static function _($new_object = null)
    {
        // 自行实现单例或按需创建
    }
    public function init(array $options, ?object $context = null)
    {
        return $this; // 初始化并返回自身
    }
    public function isInited(): bool
    {
        return true;
    }
}
```

## 注意事项

- `init()` 的第二个参数在各处统一写作 `$context`（实现类如 `ComponentBase` 同名；早期版本接口里曾拼作 `$contetxt`，已修正）。
- 本接口不含 `reInit()`；`reInit()` 是 `ComponentBase` 在接口之上额外提供的便利方法。

## 方法列表

### 公共方法

    public static function _($new_object = null)
单例式静态入口：返回（或创建）当前组件实例；传入对象时一般用于替换/登记实例。

    public function init(array $options, ?object $context = null)
组件初始化入口，按约定返回 `$this` 以便链式调用；`$context` 为上下文（通常是所属 App）。

    public function isInited(): bool
返回该组件是否已完成初始化。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 本接口的主要实现基类
