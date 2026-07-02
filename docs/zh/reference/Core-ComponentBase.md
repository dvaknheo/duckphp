# DuckPhp\Core\ComponentBase

核心组件基类。

## 简介

`ComponentBase` 是 DuckPHP 中绝大多数核心组件的基类。它通过 `SingletonTrait` 提供可变单例访问模式，并实现了 `ComponentInterface` 所约定的基本契约：通过 `init()` 合并选项、传入上下文、完成初始化，以及通过 `isInited()` 检查初始化状态。

虽然源码中未显式声明 `implements ComponentInterface`，但 `ComponentBase` 已经实现了该接口的全部方法约定。子类通常只需覆盖 `initOptions()` 和 `initContext()` 即可完成自定义初始化。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `init_once` | `false` | 是否只允许初始化一次。为 `true` 时，重复调用 `init()` 会直接返回当前实例，除非传入 `force_new_init` 强制重新初始化。 |

## 使用方式

### 作为组件基类

```php
namespace My\Component;

use DuckPhp\Core\ComponentBase;

class MyComponent extends ComponentBase
{
    public $options = [
        'my_option' => 'default',
    ];

    protected function initOptions(array $options)
    {
        // 处理自定义选项
    }

    protected function initContext(object $context)
    {
        // 处理上下文，例如保存当前 App 实例
    }
}

$component = MyComponent::_()->init(['my_option' => 'value'], $app);
```

### 获取组件实例

```php
use DuckPhp\Core\ComponentBase;

$component = ComponentBase::_();
$component->init($options);

if ($component->isInited()) {
    // 组件已初始化
}
```

### 重新初始化

```php
// 强制重新初始化，忽略 init_once 限制
$component->reInit($options);
```

## 配置示例

### 基础组件配置

```php
class MyComponent extends ComponentBase
{
    public $options = [
        'init_once' => true,
        'my_option' => 'default',
    ];
}
```

### 强制重新初始化

```php
MyComponent::_()->reInit([
    'my_option' => 'new_value',
]);
```

## 注意事项

1. `init()` 会自动用传入的选项合并并过滤当前 `$options` 中已存在的键。
2. `initOptions()` 和 `initContext()` 是可重写方法，用于子类扩展。
3. `context()` 默认返回 `App::Current()`，即当前 Phase 下的应用实例。
4. `extendFullFile()` 辅助方法用于在组件中解析可覆盖文件路径。

## 全部选项

```php
public $options = [
    'init_once' => false,
];
```

## 方法列表

### 公共方法

    public function __construct()
构造函数，子类可以覆盖以执行额外的构造逻辑

    public function context()
返回当前应用上下文，默认等价于 `App::Current()`

    public function init(array $options, ?object $context = null)
初始化组件：合并选项、调用 `initOptions()` 和 `initContext()`，并标记初始化完成

    public function reInit(array $options, ?object $context = null)
强制重新初始化，通过设置 `force_new_init` 选项绕过 `init_once` 限制

    public function isInited(): bool
返回组件是否已完成初始化

    public function extendFullFile($path_main, $path_sub, $file, $use_override = true)
解析文件完整路径，支持上下文覆盖和绝对路径判断

### 受保护方法

    protected function initOptions(array $options)
【可重写】处理组件自定义选项，默认空实现

    protected function initContext(object $context)
【可重写】处理上下文对象，默认空实现

    protected static function IsAbsPath($path)
判断路径是否为绝对路径

    protected static function SlashDir($path)
将路径统一以目录分隔符结尾

## 相关链接

- [DuckPhp\Core\ComponentInterface](Core-ComponentInterface.md)
- [DuckPhp\Core\SingletonTrait](Core-SingletonTrait.md)
- [DuckPhp\Core\App](Core-App.md)
