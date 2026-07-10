# DuckPhp\Ext\JsonRpcExt

JSON-RPC 扩展组件。

## 简介

`JsonRpcExt` 为框架提供 JSON-RPC 2.0 的客户端与服务端能力。它可以自动为服务端接口生成代理类，也可以接收 JSON-RPC 请求并调用本地服务类。

该组件通常由 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `jsonrpc_namespace` | `'JsonRpc'` | 自动加载的代理类命名空间前缀。 |
| `jsonrpc_backend` | `'https://127.0.0.1'` | 后端 JSON-RPC 服务器地址。 |
| `jsonrpc_is_debug` | `false` | 是否为调试模式。为 `true` 时错误信息会包含原始响应。 |
| `jsonrpc_enable_autoload` | `true` | 是否注册自动加载器，以懒加载代理类。 |
| `jsonrpc_check_token_handler` | `null` | 请求 Token 处理回调，签名 `function($ch)`。 |
| `jsonrpc_wrap_auto_adjust` | `true` | 是否自动调整包装行为。 |
| `jsonrpc_service_interface` | `''` | 本地服务接口约束。如果非空，仅允许该接口的实现类被调用。 |
| `jsonrpc_service_namespace` | `''` | 本地服务类命名空间前缀。 |
| `jsonrpc_timeout` | `5` | cURL 请求超时时间（秒）。 |

## 使用方式

### 作为客户端

```php
use DuckPhp\Ext\JsonRpcExt;

$client = new \JsonRpc\Service\MyService(); // 通过自动加载的代理类
$result = $client->foo(['bar' => 1]);
```

代理类不需要真实存在，`JsonRpcExt` 会按需通过 `eval` 创建继承 `JsonRpcClientBase` 的类。

### 使用 `Wrap` 包装现有类

```php
$proxy = JsonRpcExt::Wrap(\MyService::class);
$result = $proxy->method($arg);
```

### 作为服务端

在路由钩子中处理请求：

```php
$input = json_decode(file_get_contents('php://input'), true);
$ret = JsonRpcExt::_()->onRpcCall($input);
echo json_encode($ret);
```

`onRpcCall` 会解析 `method` 为 `ClassName.method`，调用对应服务类方法。

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\JsonRpcExt::class => true,
        ],
        'jsonrpc_namespace' => 'JsonRpc',
        'jsonrpc_backend' => 'https://api.example.com/rpc',
        'jsonrpc_service_namespace' => 'MyApp\\Service',
        'jsonrpc_service_interface' => 'MyApp\\Service\\RpcServiceInterface',
        'jsonrpc_timeout' => 10,
    ];
}
```

## 注意事项

1. 代理类通过 `spl_autoload_register` 注册，若关闭 `jsonrpc_enable_autoload`，需要手动创建代理类。
2. 客户端通过 `JsonRpcExt::callRpc()` 发起 cURL 请求，并返回服务端 `result` 字段。
3. 服务端会调用 `adjustService()` 校验类是否实现 `jsonrpc_service_interface`。
4. 调用失败时抛出 `ErrorException` 或 `Exception`。
5. 支持 `CURLOPT_CONNECT_TO` 进行主机重定向，传数组形式的后端地址即可。

## 全部选项

```php
    public $options = [
        'jsonrpc_namespace' => 'JsonRpc',
        'jsonrpc_backend' => 'https://127.0.0.1',
        'jsonrpc_is_debug' => false,
        'jsonrpc_enable_autoload' => true,
        'jsonrpc_check_token_handler' => null,
        'jsonrpc_wrap_auto_adjust' => true,
        'jsonrpc_service_interface' => '',
        'jsonrpc_service_namespace' => '',
        'jsonrpc_timeout' => 5,
    ];
```

## 方法列表

### 公共方法

    public function clear(): void
注销自动加载器。

    public function getRealClass(object $object): string
获取对象的真实类名，去除 `jsonrpc_namespace` 前缀。

    public static function Wrap($class)
包装类为代理对象（静态代理到 `JsonRpcClientBase`）。

    public static function _Wrap($class)
创建 `JsonRpcClientBase` 实例并注入原始类。

    public function _autoload($class): void
自动加载 `jsonrpc_namespace` 下的代理类。

    public function callRpc(string $classname, string $method, array $arguments)
向后端发送 JSON-RPC 请求，并返回 `result` 字段。

    public function onRpcCall(array $input)
处理 JSON-RPC 请求输入，调用本地服务类并返回响应数组。

### 受保护方法

    protected function initOptions(array $options): void
初始化选项，注册自动加载器。

    protected function adjustService(string $service): ?string
校验服务类并返回完整类名。如果配置了接口约束但未实现，则返回 `null`。

    protected function curl_file_get_contents($url, $post): string
使用 cURL 发送 JSON-RPC 请求。

    protected function prepare_token($ch)
如果配置了 `jsonrpc_check_token_handler`，则调用该回调处理请求句柄。

## 相关链接

- [DuckPhp\Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md)
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
