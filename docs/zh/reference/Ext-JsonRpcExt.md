# DuckPhp\Ext\JsonRpcExt

## 简介

`JsonRpcExt` 是 JSON-RPC 扩展的总控：既是**客户端传输层**（`callRpc()` 经 HTTP/curl 按 JSON-RPC 2.0 调用远端），也是**服务端分发**（`onRpcCall()` 把 `Namespace.Service.method` 形式的方法名解析到本地服务类并调用），还带一个 `JsonRpc\` 命名空间的自动加载（客户端类生成）。

典型链路：客户端 `JsonRpcClientBase::__call` → `JsonRpcExt::callRpc`（POST 到 `jsonrpc_backend`）→ 远端 `onRpcCall` 分发到 `Service::_()->method(...)`。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class JsonRpcExt extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `jsonrpc_namespace` | `'JsonRpc'` | 客户端自动加载的命名空间前缀。 |
| `jsonrpc_backend` | `'https://127.0.0.1'` | 服务端地址（也可传 `[base, real_host]` 以使用 `CURLOPT_CONNECT_TO`）。 |
| `jsonrpc_is_debug` | `false` | 失败时是否在异常消息中带原始返回。 |
| `jsonrpc_enable_autoload` | `true` | 是否注册客户端类自动加载。 |
| `jsonrpc_check_token_handler` | `null` | 可选：给 curl 会话加 token 的回调。 |
| `jsonrpc_wrap_auto_adjust` | `true` | （保留配置）自动调整包装行为。 |
| `jsonrpc_service_interface` | `''` | 服务端校验：服务类需是该接口的子类才接受。 |
| `jsonrpc_service_namespace` | `''` | 服务类命名空间（客户端/服务端两侧补全用）。 |
| `jsonrpc_timeout` | `5` | curl 超时秒数。 |

## 使用方式

```php
\DuckPhp\Ext\JsonRpcExt::_()->init([
    'jsonrpc_backend' => 'https://rpc.example.com/rpc.php',
    'jsonrpc_service_namespace' => 'MyProject\\RpcService',
], $app);

// 客户端（假设计算器服务）：
$c = JsonRpcExt::_Wrap(Calculator::class);
echo $c->add(3, 4);

// 服务端（rpc.php 里）：
$result = JsonRpcExt::_()->onRpcCall(json_decode(file_get_contents('php://input'), true));
echo json_encode($result);
```

## 注意事项

- 客户端 `_Wrap($class)` 返回一个以 `JsonRpcClientBase` 为实例、可 `_()` 替换的“伪类”实例；`_autoload` 对 `JsonRpc\` 前缀动态生成客户端类。
- 请求方法名为 `服务类全名(\.分隔).方法`（如 `MyProject.RpcService.Calculator.add`）；服务端按末段取方法、前缀拼 `jsonrpc_service_namespace` 定位服务类，并校验其实现 `jsonrpc_service_interface`。
- `callRpc()`：响应为空或含 `error` 时抛异常；否则返回 `result`。
- `prepare_token()`：配置了 `jsonrpc_check_token_handler` 时用于给请求附加令牌。

## 方法列表

### 公共方法

    public function clear(): void
注销自动加载。

    public function getRealClass(object $object): string
去掉客户端对象类名中的 `JsonRpc\` 前缀，得到真实服务类名。

    public static function Wrap($class)
静态便捷：等价 `_Wrap`。

    public static function _Wrap($class)
把服务类包装成 RPC 客户端（返回可 `_()` 替换的 `JsonRpcClientBase` 实例）。

    public function _autoload($class): void
为 `JsonRpc\` 前缀动态生成客户端类。

    public function callRpc(string $classname, string $method, array $arguments)
发送 JSON-RPC 2.0 请求并返回 `result`；失败抛异常。

    public function onRpcCall(array $input)
服务端入口：解析方法名、分发到服务类并返回 JSON-RPC 响应（异常转入 `error`）。

### 受保护方法

    protected function initOptions(array $options): void
初始化调试/前缀/自动加载。

    protected function adjustService(string $service): ?string
补全服务类命名空间并校验 `jsonrpc_service_interface`。

    protected function curl_file_get_contents($url, $post): string
用 curl 发起 POST（支持 `CURLOPT_CONNECT_TO`、超时与 token）。

    protected function prepare_token($ch)
按 `jsonrpc_check_token_handler` 为 curl 会话附加令牌。

## 相关链接

- [DuckPhp\Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md) — 客户端基类
