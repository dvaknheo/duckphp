# DuckPhp\Ext\JsonRpcClientBase

## Introduction

`JsonRpcClientBase` is the JSON-RPC **client** base class: it turns any method call made on the object into one RPC request sent to the server (via `JsonRpcExt::callRpc`). Together with `JsonRpcExt`'s autoloading (classes under the `JsonRpc\` prefix automatically inherit from this class), you can call remote services in code "as if calling a local object".

Usage: create/wrap a client instance (`JsonRpcClientBase`) through `JsonRpcExt::_Wrap($class)`, or inherit this class and specify the corresponding service class name with `setJsonRpcClientBase($realClass)`.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class JsonRpcClientBase extends DuckPhp\Core\ComponentBase`

## Usage

```php
use DuckPhp\Ext\JsonRpcExt;

$client = JsonRpcExt::_Wrap(CalculatorService::class);
// $client is a JsonRpcClientBase: the following calls become remote RPC
$sum = $client->add(1, 2);
```

## Caveats

- `__call`: `$method`/`$arguments` are handed to `JsonRpcExt::callRpc($base_class, $method, $arguments)`; when `$base_class` is not set explicitly it is inferred by `JsonRpcExt::getRealClass($this)` (stripping the `JsonRpc\` prefix).
- `init()/isInited()` are overridden: when `_base_class` is set, these two calls also notify the server via RPC first (`callRpc`) before running the parent logic — i.e. the client object's own lifecycle is mirrored to the remote side.

## Methods

### Public methods

    public function __construct()
Empty constructor.

    public function setJsonRpcClientBase(string $class): self
Sets the corresponding "real service class name" and returns itself.

    public function __call(string $method, array $arguments)
Turns an undefined method call into an RPC request and returns the result.

    public function init(array $options, ?object $context = null)
Initializes: notifies the remote side via RPC first, then runs the parent flow.

    public function isInited(): bool
Queries the remote side via RPC first, then returns the parent initialization state.

## Related links

- [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) — the RPC server/transport implementation
