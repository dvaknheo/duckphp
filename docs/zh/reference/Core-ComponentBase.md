# DuckPhp\Core\ComponentBase

## 简介

`ComponentBase` 是 DuckPHP 框架中绝大多数组件的基类。框架的核心类（`Route`、`View`、`Console`、`Logger`、`SuperGlobal`、`SystemWrapper` 等）以及各 `Component\*`、`Ext\*` 组件均直接或间接继承它。

它把「组件」的通用行为收敛到一处：通过 `use SingletonExTrait` 获得 `类名::_()` 单例式访问入口；通过 `init()` 的模板流程统一完成选项合并、上下文注入与初始化；通过 `isInited()` 暴露初始化状态。子类通常只覆盖 `initOptions()` / `initContext()` 两个空钩子即可完成自定义初始化，无需关心基类流程。

> 源码中 `class ComponentBase // implements ComponentInterface` 一句以注释形式说明它实现了 `ComponentInterface` 的契约（并未用关键字 `implements` 声明）。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class ComponentBase`
- 使用的 Trait：`DuckPhp\Core\SingletonExTrait`

## 选项机制

`ComponentBase` 自身只声明一个空的 `public $options = []`，**不定义任何具体选项键**。具体选项由子类在自己的 `public $options` 中声明。基类提供的选项机制如下：

- `init()` 合并选项时使用 `array_intersect_key(array_replace_recursive($this->options, $options), $this->options)`：只保留**子类已声明过**的键，传入的未知选项会被白名单过滤掉（这也是框架“选项名可控、不随意扩展”的根基）。
- `init_once`（受保护属性，默认 `false`）：为 `true` 时，`init()` 在已初始化过的情况下直接返回当前实例，除非传入的选项中带 `__force__ => true`。
- `reInit()` 内部只是把 `__force__` 置真后重走 `init()`，用于强制重新初始化。

## 使用方式

### 自定义一个组件

```php
namespace My\Component;

use DuckPhp\Core\ComponentBase;

class MyComponent extends ComponentBase
{
    public $options = [
        'my_option' => 'default',
    ];

    protected function initOptions(array $options): void
    {
        // 这里可读取合并后的 $this->options 做加工
    }

    protected function initContext(object $context): void
    {
        // $context 通常是所属 App 实例；需要时可保存
    }
}

// 取实例并初始化（$app 一般传 App::_()）
$component = MyComponent::_()->init(['my_option' => 'value'], $app);
if ($component->isInited()) {
    // 已初始化完成
}
```

### 强制重新初始化

```php
$component->reInit(['my_option' => 'other']);
```

### 访问所属 App

```php
$app = $component->context(); // 内部等价 App::_()
```

## 配置示例

`ComponentBase` 没有具体选项；需要配置时由子类声明 `public $options` 并传入对应键，例如：

```php
$component = MyComponent::_()->init([
    'my_option' => 'value',
    // 未在 $options 中声明的键会被过滤掉
], App::_());
```

## 注意事项

- `init()` 的返回值为当前实例，可链式调用；接口/调用方常以 `ClassName::_()->init($options, $context)` 形式使用。
- `isInited()` 在 `init_once` 场景外并不阻止重复 `init()`；需要幂等初始化请自行把子类 `init_once` 属性设为 `true`。
- 传入的 `$context` 为 `null` 时跳过 `initContext()`（`App` 等自身作为上下文时会传入实例）。
- `IsAbsPath()` / `SlashDir()` 为受保护静态工具，供子类在拼接/判断路径时使用；

## 方法列表

### 公共方法

    public function __construct()
空构造器，便于子类不写构造函数也能被 `_()` 直接 `new`。

    public function context()
返回当前所属 App 实例，内部等价 `App::_()`。

    public function init(array $options, ?object $context = null)
组件初始化模板：选项白名单合并 → `initOptions()` → 传入上下文时 `initContext()` → 标记 `is_inited`；`init_once` 且已初始化且未带 `__force__` 时直接返回自身。

    public function reInit(array $options, ?object $context = null)
把 `__force__` 置为 `true` 后重走 `init()`，用于强制重新初始化。

    public function isInited(): bool
返回是否已完成初始化。

### 受保护方法

    protected function initOptions(array $options): void
子类覆盖点：处理并消费合并后的选项。基类为空实现。

    protected function initContext(object $context): void
子类覆盖点：接收并处理上下文（通常是所属 App）。基类为空实现。

    protected static function IsAbsPath($path)
判断路径是否为绝对路径（`/` 开头、盘符如 `C:\`、或 `\\` 开头）。

    protected static function SlashDir($path)
把路径尾部统一为目录分隔符结尾（非空时 `rtrim` 后补 `DIRECTORY_SEPARATOR`）。

## 相关链接

- [DuckPhp\Core\ComponentInterface](Core-ComponentInterface.md) — 本类实现的组件接口
- [DuckPhp\Core\App](Core-App.md) — `context()` 返回的所属应用实例
- [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) — `_()` 静态入口的来源 Trait
