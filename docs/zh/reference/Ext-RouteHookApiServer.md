# DuckPhp\Ext\RouteHookApiServer

API 服务器路由钩子扩展。

## 简介

`RouteHookApiServer` 是一个路由钩子，用于将 HTTP 请求转换为 API 调用。它会根据 `PATH_INFO` 定位命名空间下的类和方法，自动注入请求参数，最后以 JSON 格式输出结果。

该组件适合构建简单的 RESTful 风格 API。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 应用命名空间前缀。 |
| `api_server_base_class` | `''` | 允许的 API 基类。API 类必须继承该基类。支持 `~` 作为命名空间前缀占位符。 |
| `api_server_namespace` | `'Api'` | API 类所在的子命名空间。 |
| `api_server_class_postfix` | `''` | API 类名后缀。 |
| `api_server_use_singletonex` | `false` | 为 `true` 时通过 `Class::_()` 获取单例；否则 `new` 实例化。 |
| `api_server_404_as_exception` | `false` | 为 `true` 时，未匹配到 API 抛出 `ReflectionException`；否则返回 `false` 让后续路由处理。 |

## 使用方式

### 作为 DuckPhp 扩展加载

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\RouteHookApiServer::class => true,
        ],
        'api_server_namespace' => 'Api',
        'api_server_base_class' => 'MyApp\\Api\\BaseApi',
    ];
}
```

### 请求示例

路径：`/api/User.getInfo` 对应 `MyApp\Api\User::getInfo()`。

请求参数会通过反射注入方法参数中，并按声明类型进行过滤校验。

### 输出响应

成功时返回方法返回值，失败时返回：

```json
{
    "error_code": -1,
    "error_message": "..."
}
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\RouteHookApiServer::class => true,
        ],
        'namespace' => 'MyApp',
        'api_server_namespace' => 'Api',
        'api_server_base_class' => 'MyApp\\Api\\Base',
        'api_server_use_singletonex' => false,
        'api_server_404_as_exception' => true,
    ];
}
```

## 注意事项

1. 初始化时会向 `Route` 注册一个 `prepend-inner` 钩子。
2. 默认会设置全局异常处理器为 `OnJsonError`，错误以 JSON 形式输出。
3. 方法参数类型会被自动校验：`bool`、`int`、`float`、`string`。
4. 输出 `Content-Type` 为 `text/plain; charset=utf-8`，并附加 CORS 响应头。
5. 调试模式下返回的 JSON 会启用 `JSON_PRETTY_PRINT`。

## 全部选项

```php
    public $options = [
        'namespace' => '',
        'api_server_base_class' => '',
        'api_server_namespace' => 'Api',
        'api_server_class_postfix' => '',
        'api_server_use_singletonex' => false,
        'api_server_404_as_exception' => false,
    ];
```

## 方法列表

### 公共方法

    public static function Hook($path_info)
路由钩子入口。静态调用会转发到实例方法 `_Hook()`。

    public function _Hook($path_info)
解析路径、注入参数、调用 API 并输出 JSON 响应。

    public static function OnJsonError($e)
全局异常处理器入口。

    public function _OnJsonError($e)
输出异常对应的 JSON 错误信息。

### 受保护方法

    protected function initContext(object $context)
向 `Route` 注册路由钩子。

    protected function onMissing()
未找到 API 时的处理逻辑。

    protected function getComponenetNamespace($namespace_key)
拼接完整命名空间。

    protected function getObjectAndMethod($path_info)
根据路径解析 API 类和方法。

    protected function getInputs($path_info)
获取请求参数。调试模式下读取 `$_REQUEST`，否则读取 `$_POST`。

    protected function exitJson($ret, $exit = true)
输出 JSON 并设置响应头。

    protected function callAPI($object, $method, $input)
通过反射调用方法，并自动注入和校验参数。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\Route](Core-Route.md)
- [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md)
