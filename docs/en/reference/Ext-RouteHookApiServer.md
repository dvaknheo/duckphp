# DuckPhp\Ext\RouteHookApiServer

## Introduction

`RouteHookApiServer` is the "API server" route extension: it maps requests shaped like `Namespace/Service.method` (a `/` path plus a `.` action) onto a service class under `apiserver_namespace`, calls it and outputs the result as JSON; it also presets the CORS headers and a JSON error response. It hangs off `prepend-inner` (taking over API-style requests before default routing).

Request parameters are taken from the input by the reflected parameter names of the target method (filtered as bool/int/float/string when a type is declared), and a missing required parameter throws a 404 / type-mismatch style exception (`ReflectionException`).

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class RouteHookApiServer extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Meaning |
|---|---|---|
| `namespace` | `''` | The application namespace (used to assemble a relative sub-namespace). |
| `apiserver_base_class` | `''` | The constraint on the service base class (supports the `~` placeholder prefix; the service class must be a subclass of it). |
| `apiserver_namespace` | `'Api'` | The namespace the service classes live in. |
| `apiserver_class_postfix` | `''` | The service class-name suffix. |
| `apiserver_use_singletonex` | `false` | When `true`, fetches the singleton through `_()` (and rejects the action when its name is `G`). |
| `apiserver_404_as_exception` | `false` | Whether to throw `ReflectionException("404")` on a miss. |

## Usage

```php
\DuckPhp\Ext\RouteHookApiServer::_()->init([
    'namespace' => 'MyProject',
    'apiserver_namespace' => 'Api',
    'apiserver_base_class' => '\\MyProject\\Api\\Base',
], $app);

// request POST /Api/User.login, body: name=xx
// → calls MyProject\Api\User::login($name) and returns JSON.
```

```php
// a service class example (namespace MyProject\Api):
class User extends Base
{
    public function login(string $name): array
    {
        return ['ok' => true, 'name' => $name];
    }
}
```

## Caveats

- Method mapping: the last path segment is split on `.` to get the action (`User.login` → class `User`, method `login`); a class name may use the `/` namespace form (`Admin/User.login`).
- `callAPI()` takes parameters by reflection: by parameter name from the input; a missing parameter without a default throws `ReflectionException("Need Parameter",-2)`; a failed type filter throws `("Type Unmatch",-3)`; a missing action or one not satisfying the base-class constraint goes through `onMissing` (404 or an exception).
- Response: `exitJson()` outputs the preset CORS headers plus `Content-Type: text/plain; charset=utf-8`, and adds `JSON_PRETTY_PRINT` in debug mode.
- Exceptions are taken over by the default exception handler as JSON: `OnJsonError` outputs `{error_code, error_message}`.
- Input: debug mode reads `$_REQUEST`, otherwise `$_POST`.

## Methods

### Public methods

    public static function Hook($path_info)
The static hook entry point, forwarding to `_Hook`.

    public function _Hook(string $path_info): bool
Resolves the target service/action, calls it and exits with JSON; a miss is handled according to the options.

    public static function OnJsonError($e)
The static error entry point, forwarding to `_OnJsonError`.

    public function _OnJsonError($e): void
Outputs the exception as `{error_code, error_message}` JSON.

### Protected methods

    protected function initContext(object $context): void
Hangs `Hook` on Route's `prepend-inner`.

    protected function onMissing(): bool
Miss handling: throws `ReflectionException("404")` when `apiserver_404_as_exception` is on, otherwise returns `false`.

    protected function getComponenetNamespace(string $namespace_key): string
Assembles the full namespace from the options (a relative name gets `namespace` prepended).

    protected function getObjectAndMethod(string $path_info): array
Resolves `[service object, action name]`; an empty path, a violated base-class constraint or a disabled action returns `[null, null]`.

    protected function getInputs(string $path_info): array
Takes the input: `$_REQUEST` in debug mode, otherwise `$_POST`.

    protected function exitJson($ret, bool $exit = true): void
Outputs the CORS headers and the result as JSON.

    protected function callAPI(object $object, string $method, array $input)
Calls the service method by reflection: takes the input by parameter name and filters/validates by type.

## Related links

- [DuckPhp\Core\Route](Core-Route.md) — the hook host
- [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) — the default exception handling (turning things into JSON)
