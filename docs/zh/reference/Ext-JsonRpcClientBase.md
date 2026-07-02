# DuckPhp\Ext\JsonRpcClientBase

JSON-RPC 客户端基类。

## 简介

`JsonRpcClientBase` 是所有 JSON-RPC 代理类的基类。当代理类调用不存在的方法时，方法名和参数会被转发到 `JsonRpcExt::callRpc()`，从而向远程服务器发起 JSON-RPC 请求。

它通常不需要手动实例化，而是由 `JsonRpcExt` 自动加载或 `Wrap` 方法创建。

## 选项

该类本身没有独立选项，全部行为由 `DuckPhp\Ext\JsonRpcExt` 的选项控制。

## 使用方式

### 通过自动加载代理类

```php
use JsonRpc\MyApp\Service\UserService;

$client = UserService::_();
$result = $client->getUser(1);
```

### 通过 `JsonRpcExt::Wrap()` 创建

```php
$proxy = \DuckPhp\Ext\JsonRpcExt::Wrap(\MyApp\Service\UserService::class);
$result = $proxy->getUser(1);
```

### 手动设置基类

```php
$base = new \DuckPhp\Ext\JsonRpcClientBase();
$base->setJsonRpcClientBase(\MyApp\Service\UserService::class);
$base->getUser(1);
```

## 配置示例

无需单独配置 `JsonRpcClientBase`，只需配置 `JsonRpcExt`：

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\JsonRpcExt::class => true,
        ],
        'jsonrpc_backend' => 'https://api.example.com/rpc',
        'jsonrpc_namespace' => 'JsonRpc',
    ];
}
```

## 注意事项

1. 所有方法调用都被 `__call()` 捕获并转发到 `JsonRpcExt`。
2. `init()` 和 `isInited()` 方法也会被转发，避免破坏单例生命周期。
3. 如果未设置 `_base_class`，则通过 `JsonRpcExt::getRealClass()` 自动推导当前代理类名。

## 全部选项

无

## 方法列表

### 公共方法

    public function setJsonRpcClientBase($class)
设置代理所代表的真实类名，并返回当前实例。

    public function __call($method, $arguments)
将方法调用转发到 `JsonRpcExt::callRpc()`。

    public function init(array $options, ?object $context = null)
如果设置了基类，则将该方法调用通过 RPC 转发；否则调用父类初始化。

    public function isInited(): bool
如果设置了基类，则将该方法调用通过 RPC 转发；否则返回父类状态。

## 相关链接

- [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
