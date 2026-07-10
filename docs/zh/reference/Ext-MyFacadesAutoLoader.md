# DuckPhp\Ext\MyFacadesAutoLoader

> ⚠️ 警告：该扩展是实验性的或已废弃，不建议在新项目中使用。

## 简介

`MyFacadesAutoLoader` 是一个用于自动加载 facade 类的扩展。它注册一个自动加载器，当请求特定命名空间下的类时，动态生成一个继承自 `MyFacadesBase` 的空类，并将静态调用转发到映射的真实类。

该扩展使用 `eval()` 动态生成类，且依赖 `MyFacadesBase` 实现静态调用转发，属于实验性实现，不建议在新项目中使用。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `facades_namespace` | `'MyFacades'` | Facade 类所在的命名空间前缀。 |
| `facades_map` | `[]` | Facade 类到真实类的映射表。 |
| `facades_enable_autoload` | `true` | 是否注册自动加载器。 |

## 使用方式

### 基础配置

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            DuckPhp\Ext\MyFacadesAutoLoader::class => true,
        ],
        'facades_namespace' => 'MyFacades',
        'facades_map' => [
            'MyFacades\UserService' => App\Service\UserService::class,
        ],
    ];
}
```

### 使用自动加载的 Facade

```php
use MyFacades\UserService;

$result = UserService::getUserById(123);
// 等价于 App\Service\UserService::_()->getUserById(123)
```

### 获取映射回调

```php
$callback = MyFacadesAutoLoader::_()->getFacadesCallback('MyFacades\UserService', 'getUserById');
// 返回 [$object, 'getUserById']
```

### 清除自动加载器

```php
MyFacadesAutoLoader::_()->clear();
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            DuckPhp\Ext\MyFacadesAutoLoader::class => true,
            DuckPhp\Ext\MyFacadesBase::class => true,
        ],
        'facades_namespace' => 'Facades',
        'facades_map' => [
            'Facades\User' => App\Service\UserService::class,
            'Facades\Order' => App\Service\OrderService::class,
        ],
    ];
}
```

## 注意事项

1. 自动加载器通过 `eval()` 动态生成 facade 类，可能影响性能与 IDE 提示。
2. 真实目标类需要实现 `_()` 单例方法，否则静态调用会失败。
3. 如果类名不在 `facades_map` 中，但命名空间匹配 `facades_namespace`，则去掉前缀后的类名作为真实类名。
4. 调用 `clear()` 会清空映射并注销自动加载器。

## 全部选项

        'facades_namespace' => 'MyFacades',
        'facades_map' => [],
        'facades_enable_autoload' => true,

## 方法列表

### 公共方法

    public function _autoload($class): void
注册的自动加载回调。如果类匹配 facade 命名空间或映射表，则动态生成对应的类定义。

    public function getFacadesCallback(string $input_class, string $name): ?array
根据 facade 类名获取真实对象及其方法回调。

    public function clear(): void
清空 facade 映射并注销自动加载器。

### 受保护方法

    protected function initOptions(array $options): void
初始化选项，解析命名空间前缀，并在启用自动加载时注册自动加载器。

## 相关链接

- [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md)
