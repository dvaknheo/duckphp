# DuckPhp\Ext\RouteHookApiServer

## 简介

`RouteHookApiServer` 是“API 服务器”路由扩展：把形如 `Namespace/Service.method`（`/` 路径 + `.` 动作）的请求映射到 `apiserver_namespace` 下的服务类并调用，结果以 JSON 输出；并预设跨域头与 JSON 错误响应。挂在 `prepend-inner`（默认路由前接管 API 类请求）。

请求参数按目标方法的反射参数名从输入中取（有类型时按 bool/int/float/string 过滤），缺必需参数抛 404/类型不匹配等异常（`ReflectionException`）。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class RouteHookApiServer extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 应用命名空间（拼相对子命名空间用）。 |
| `apiserver_base_class` | `''` | 服务类基类约束（支持 `~` 占位前缀；服务类须为其子类）。 |
| `apiserver_namespace` | `'Api'` | 服务类所在命名空间。 |
| `apiserver_class_postfix` | `''` | 服务类名后缀。 |
| `apiserver_use_singletonex` | `false` | 为 `true` 时经 `_()` 取单例（且动作名为 `G` 时拒绝）。 |
| `apiserver_404_as_exception` | `false` | 未命中时是否抛 `ReflectionException("404")`。 |

> ⚠️ **选项名统一为 `apiserver_*`**：旧拼法 `api_server_base_class` / `api_server_namespace` / `api_server_class_postfix` / `api_server_use_singletonex` / `api_server_404_as_exception` **在源码里已不存在**（见 `src/Ext/RouteHookApiServer.php` 的 `$options`）；传旧名不会报错，但**不生效**（组件退回默认值），表现为「明明配了命名空间却全 404」。更早还有一个 `api_server_interface`，它被 `apiserver_base_class` 取代（`~` 前缀表示当前命名空间）。

## 使用方式

```php
\DuckPhp\Ext\RouteHookApiServer::_()->init([
    'namespace' => 'MyProject',
    'apiserver_namespace' => 'Api',
    'apiserver_base_class' => '\\MyProject\\Api\\Base',
], $app);

// 请求 POST /Api/User.login，body: name=xx
// → 调用 MyProject\Api\User::login($name)，返回 JSON。
```

```php
// 服务类示例（命名空间 MyProject\Api）：
class User extends Base
{
    public function login(string $name): array
    {
        return ['ok' => true, 'name' => $name];
    }
}
```

## 注意事项

- 方法映射：路径末段用 `.` 切出动作（`User.login` → 类 `User`、方法 `login`）；类名支持 `/` 命名空间写法（`Admin/User.login`）。
- `callAPI()` 反射取参数：按参数名从输入取；无默认值的缺参抛 `ReflectionException("Need Parameter",-2)`；类型过滤失败抛 `("Type Unmatch",-3)`；动作不存在/不满足基类约束走 `onMissing`（404 或抛异常）。
- 响应：`exitJson()` 输出预设 CORS 头 + `Content-Type: text/plain; charset=utf-8`，调试模式加 `JSON_PRETTY_PRINT`。
- 异常被默认异常处理器接管为 JSON：`OnJsonError` 输出 `{error_code, error_message}`。
- 输入：调试模式读 `$_REQUEST`，否则读 `$_POST`。

## 方法列表

### 公共方法

    public static function Hook($path_info)
静态钩子入口，转发 `_Hook`。

    public function _Hook(string $path_info): bool
解析目标服务/动作、调用并以 JSON 退出；未命中按选项处理。

    public static function OnJsonError($e)
静态错误入口，转发 `_OnJsonError`。

    public function _OnJsonError($e): void
把异常输出为 `{error_code, error_message}` JSON。

### 受保护方法

    protected function initContext(object $context): void
把 `Hook` 挂到 Route 的 `prepend-inner`。

    protected function onMissing(): bool
未命中处理：开启 `apiserver_404_as_exception` 时抛 `ReflectionException("404")`，否则返回 `false`。

    protected function getComponenetNamespace(string $namespace_key): string
按选项拼出完整命名空间（相对名会拼上 `namespace`）。

    protected function getObjectAndMethod(string $path_info): array
解析出 `[服务对象, 动作名]`；空/不满足基类/禁用动作返回 `[null, null]`。

    protected function getInputs(string $path_info): array
取输入：调试模式 `$_REQUEST`，否则 `$_POST`。

    protected function exitJson($ret, bool $exit = true): void
输出 CORS 头并以 JSON 输出结果。

    protected function callAPI(object $object, string $method, array $input)
反射调用服务方法：按参数名取输入并按类型过滤/校验。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md) — 钩子宿主
- [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) — 默认异常处理（JSON 化）
