# DuckPhp\Helper\AppHelperTrait

## 简介

`AppHelperTrait` 是**应用级（App 上下文）**的静态助手集合，汇集了“框架级但不属于某一层专属”的常用转发：异常处理、全局事件、运行状态、路由钩子与映射、会话/请求状态（`SuperGlobal`）、可替换系统函数（`SystemWrapper`：`header`/`setcookie`/`exit`/`session_*` 等）、DB/Redis、CLI 参数、扩展选项保存等。

业务代码通常不直接使用本 Trait；它是 `DuckPhpAllInOne` 组合的四个 Helper 之一，工程里也可由自己的 `System` 层/应用基类组合。

## 类信息

- 命名空间：`DuckPhp\Helper`
- 声明：`trait AppHelperTrait`
- 使用的 Trait：`DuckPhp\Core\SingletonExTrait`

## 使用方式

```php
class MyApp extends DuckPhp\DuckPhp
{
    use DuckPhp\Helper\AppHelperTrait; // 或直接继承 DuckPhpAllInOne

    // 控制器动作内即可使用这些应用级能力：
    // self::header('Content-Type: application/json');
    // self::addRouteHook(..., 'prepend-outter');
    // $map = self::getRouteMaps();
    // self::FireGlobalEvent('my_event', $data);
}
```

## 注意事项

- 大多方法为**纯转发**：同一能力的“下层组件”在对应组件文档（`ExceptionManager`/`Runtime`/`Route`/`RouteHookRouteMap`/`RouteHookRewrite`/`SuperGlobal`/`SystemWrapper`/`DbManager`/`RedisManager`/`Console`/`GlobalEvent`/`ExtOptionsLoader`/`View`）中描述。
- `header`/`setcookie`/`exit`/`session_*`/`set_exception_handler`/`register_shutdown_function`/`mime_content_type` 走 `SystemWrapper`，即**可被替换的系统函数**（测试/常驻场景可注入实现）。
- 本 Trait 未包含 `Controller` 层的 `GET/POST/Show/Url` 等方法；那些属于 `ControllerHelperTrait`。

## 方法列表

### 公共方法

    public static function CallException(\Throwable $ex)
把异常交给异常管理器处理（`ExceptionManager::CallException`）。

    public static function RemoveEvent($event, $callback = null)
移除全局事件监听（转发 `GlobalEvent::remove`）。

    public static function isRunning(): bool
框架是否处于运行中（`Runtime` 状态）。

    public static function isInException(): bool
当前是否处于异常处理流程中（`Runtime` 状态）。

    public static function addRouteHook($callback, $position = 'append-outter', $once = true)
注册路由钩子（`Route::addRouteHook`，位置见 Core-Route）。

    public static function replaceController(string $old_class, string $new_class)
替换路由中某控制器类（`Route::replaceController`）。

    public static function getViewData(): array
取视图数据（`View::getViewData`）。

    public static function DbCloseAll()
关闭全部数据库连接（`DbManager`）。

    public static function SESSION($key = null, $default = null)
读会话变量（转发 `SuperGlobal::_SESSION`）。

    public static function FILES($key = null, $default = null)
读上传文件变量（转发 `SuperGlobal::_FILES`）。

    public static function SessionSet($key, $value)
写会话变量。

    public static function SessionUnset($key)
删除会话变量。

    public static function SessionGet($key, $default = null)
读单个会话变量。

    public static function CookieSet($key, $value, $expire = 0)
写 Cookie（转发 `SuperGlobal::_CookieSet`）。

    public static function CookieGet($key, $default = null)
读 Cookie（转发 `SuperGlobal::_CookieGet`）。

    public static function system_wrapper_replace(array $funcs)
注入可替换系统函数实现（转发 `SystemWrapper::_system_wrapper_replace`）。

    public static function system_wrapper_get_providers(): array
取当前系统函数提供者表。

    public static function header($output, bool $replace = true, int $http_response_code = 0)
发送 HTTP 头（经 SystemWrapper，可替换）。

    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
写 Cookie（经 SystemWrapper，可替换）。

    public static function exit($code = 0)
退出（经 SystemWrapper；框架可配成抛 `ExitException` 以便捕获）。

    public static function set_exception_handler(callable $exception_handler)
注册异常处理（经 SystemWrapper）。

    public static function register_shutdown_function(callable $callback, ...$args)
注册关闭回调（经 SystemWrapper）。

    public static function session_start(array $options = [])
启动会话（经 SystemWrapper；`$options` 传给原生 `session_start`）。

    public static function session_id($session_id = null)
取/设会话 ID（经 SystemWrapper）。

    public static function session_destroy()
销毁会话（经 SystemWrapper）。

    public static function session_set_save_handler(\SessionHandlerInterface $handler)
设置会话保存句柄（经 SystemWrapper）。

    public static function mime_content_type($file)
取文件 MIME 类型（经 SystemWrapper）。

    public static function setBeforeGetDbHandler($db_before_get_object_handler)
设置“取库前”回调（转发 `DbManager::setBeforeGetDbHandler`）。

    public static function Redis($tag = 0)
取 Redis 客户端（`RedisManager::Redis($tag)`）。

    public static function getRouteMaps()
取路由映射表（转发 `RouteHookRouteMap::getRouteMaps`）。

    public static function assignRoute($key, $value = null)
登记路由映射（转发 `RouteHookRouteMap::assignRoute`）。

    public static function assignImportantRoute($key, $value = null)
登记重要路由映射（不会被覆盖）。

    public static function assignRewrite($key, $value = null)
登记 URL 重写规则（转发 `RouteHookRewrite::assignRewrite`）。

    public static function getRewrites()
取当前重写规则表。

    public static function getCliParameters()
取 CLI 解析出的参数（转发 `Console::getCliParameters`）。

    public static function FireGlobalEvent($event, ...$args)
触发全局事件（转发 `GlobalEvent::fire`）。

    public static function OnGlobalEvent($event, $callback)
注册全局事件监听（转发 `GlobalEvent::on`）。

    public static function saveExtOptions(array $options): void
把扩展选项写入（转发 `ExtOptionsLoader::saveExtOptions`）。

    public static function ProjectThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
抛“项目异常”（转发 `CoreHelper::_ProjectThrowOn`，异常类取 `exception_for_project` / `exception_map`）。

    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
`ProjectThrowOn` 的简写别名（同走项目异常）。

## 相关链接

- [DuckPhp\Helper\ControllerHelperTrait](Helper-ControllerHelperTrait.md) — 控制器层助手
- [DuckPhp\Helper\BusinessHelperTrait](Helper-BusinessHelperTrait.md) — 业务层助手
- [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — 组合四个 Helper Trait 的入口类
