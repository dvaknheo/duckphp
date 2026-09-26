# DuckPhp\Ext\JsonRpcExt

## Introduction

`JsonRpcExt` is the master control of the JSON-RPC extension: it is both the **client transport layer** (`callRpc()` calls the remote side over HTTP/curl following JSON-RPC 2.0) and the **server-side dispatcher** (`onRpcCall()` resolves a method name in `Namespace.Service.method` form to a local service class and calls it), plus an autoloader for the `JsonRpc\` namespace (client class generation).

Typical chain: client `JsonRpcClientBase::__call` → `JsonRpcExt::callRpc` (POST to `jsonrpc_backend`) → the remote `onRpcCall` dispatches to `Service::_()->method(...)`.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class JsonRpcExt extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Description |
|---|---|---|
| `jsonrpc_namespace` | `'JsonRpc'` | Namespace prefix for the client autoloader. |
| `jsonrpc_backend` | `'https://127.0.0.1'` | Server address (also accepts `[base, real_host]` to use `CURLOPT_CONNECT_TO`). |
| `jsonrpc_is_debug` | `false` | Whether to include the raw response in the exception message on failure. |
| `jsonrpc_enable_autoload` | `true` | Whether to register the client class autoloader. |
| `jsonrpc_check_token_handler` | `null` | Optional: callback that adds a token to the curl session. |
| `jsonrpc_wrap_auto_adjust` | `true` | (reserved config) auto-adjust the wrapping behavior. |
| `jsonrpc_service_interface` | `''` | Server-side check: a service class is accepted only if it is a subclass of this interface. |
| `jsonrpc_service_namespace` | `''` | Service class namespace (used for completion on both client and server sides). |
| `jsonrpc_timeout` | `5` | curl timeout in seconds. |

## Usage

```php
\DuckPhp\Ext\JsonRpcExt::_()->init([
    'jsonrpc_backend' => 'https://rpc.example.com/rpc.php',
    'jsonrpc_service_namespace' => 'MyProject\\RpcService',
], $app);

// Client side (suppose a calculator service):
$c = JsonRpcExt::_Wrap(Calculator::class);
echo $c->add(3, 4);

// Server side (inside rpc.php):
$result = JsonRpcExt::_()->onRpcCall(json_decode(file_get_contents('php://input'), true));
echo json_encode($result);
```

## Caveats

- The client `_Wrap($class)` returns a "pseudo-class" instance based on `JsonRpcClientBase`, replaceable via `_()`; `_autoload` dynamically generates client classes for the `JsonRpc\` prefix.
- The request method name is `full service class name (\. separated).method` (e.g. `MyProject.RpcService.Calculator.add`); the server takes the method from the last segment, prefixes the rest with `jsonrpc_service_namespace` to locate the service class, and checks it implements `jsonrpc_service_interface`.
- `callRpc()`: throws an exception when the response is empty or contains `error`; otherwise returns `result`.
- `prepare_token()`: used to attach a token to the request when `jsonrpc_check_token_handler` is configured.

## Methods

### Public methods

    public function clear(): void
Unregisters the autoloader.

    public function getRealClass(object $object): string
Strips the `JsonRpc\` prefix from a client object's class name to get the real service class name.

    public static function Wrap($class)
Static convenience: equivalent to `_Wrap`.

    public static function _Wrap($class)
Wraps a service class into an RPC client (returns a `JsonRpcClientBase` instance replaceable via `_()`).

    public function _autoload($class): void
Dynamically generates client classes for the `JsonRpc\` prefix.

    public function callRpc(string $classname, string $method, array $arguments)
Sends a JSON-RPC 2.0 request and returns `result`; throws on failure.

    public function onRpcCall(array $input)
Server-side entry: resolves the method name, dispatches to the service class, and returns a JSON-RPC response (exceptions go into `error`).

### Protected methods

    protected function initOptions(array $options): void
Initializes debug/prefix/autoload.

    protected function adjustService(string $service): ?string
Completes the service class namespace and checks `jsonrpc_service_interface`.

    protected function curl_file_get_contents($url, $post): string
Makes a POST with curl (supports `CURLOPT_CONNECT_TO`, timeout and token).

    protected function prepare_token($ch)
Attaches a token to the curl session per `jsonrpc_check_token_handler`.

## Related links

- [DuckPhp\Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md) — the client base class
