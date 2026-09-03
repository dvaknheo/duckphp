# DuckPhp\Ext\JsonRpcClientBase

## 简介

`JsonRpcClientBase` 是 JSON-RPC **客户端**基类：把对本对象的任意方法调用转成一次 RPC 请求发给服务端（经 `JsonRpcExt::callRpc`）。配合 `JsonRpcExt` 的自动加载（`JsonRpc\` 前缀下的类自动继承本类），可在代码里“像调本地对象一样”调用远程服务。

用法：通过 `JsonRpcExt::_Wrap($class)` 生成/包装一个客户端实例（`JsonRpcClientBase`），或继承本类并 `setJsonRpcClientBase($realClass)` 指定对应的服务类名。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class JsonRpcClientBase extends DuckPhp\Core\ComponentBase`

## 使用方式

```php
use DuckPhp\Ext\JsonRpcExt;

$client = JsonRpcExt::_Wrap(CalculatorService::class);
// $client 是 JsonRpcClientBase：以下调用变成远程 RPC
$sum = $client->add(1, 2);
```

## 注意事项

- `__call`：`$method`/`$arguments` 交给 `JsonRpcExt::callRpc($base_class, $method, $arguments)`；`$base_class` 未显式设置时由 `JsonRpcExt::getRealClass($this)` 推断（去掉 `JsonRpc\` 前缀）。
- `init()/isInited()` 被覆盖：设置了 `_base_class` 时，这两个调用也会先经 RPC 通知服务端（`callRPC`）再走父类逻辑——即客户端对象本身的生命周期也会镜像到远端。

## 方法列表

### 公共方法

    public function __construct()
空构造器。

    public function setJsonRpcClientBase(string $class): self
设置对应的“真实服务类名”，返回自身。

    public function __call(string $method, array $arguments)
把未定义的方法调用转成 RPC 请求并返回结果。

    public function init(array $options, ?object $context = null)
初始化：先 RPC 通知远端，再走父类流程。

    public function isInited(): bool
先 RPC 查询远端，再返回父类初始化状态。

## 相关链接

- [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) — RPC 服务端/传输实现
