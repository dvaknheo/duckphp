# DuckPhp\Foundation\Business\BusinessHelper

## 简介

`BusinessHelper` 是面向 **Business（业务层）** 的静态助手集合（方法就在本类里，不再有 trait）。业务层通过它访问：应用设置与路径（`Setting`/`AppOptions`/`PathOfProject`/`PathOfRuntime`）、配置（`Config`）、缓存（`Cache`）、校验（`Validator*`）、事件（`FireGlobalEvent`/`OnGlobalEvent`）、用户/管理员服务（`AdminService`/`UserService`）以及业务异常快速抛出（`BusinessThrowOn`）。

本类自带 4 个事件名常量（`$EVENT_REGISTERING/$EVENT_REGISTERED/$EVENT_LOGINING/$EVENT_LOGINED`），供注册/登录等业务生命周期事件使用。

## 类信息

- 命名空间：`DuckPhp\Foundation\Business`
- 声明：`class BusinessHelper`
- 使用的 Trait：`DuckPhp\Core\SingletonExTrait`
- 事件名常量：`$EVENT_REGISTERING = 'registering'`、`$EVENT_REGISTERED = 'registered'`、`$EVENT_LOGINING = 'logining'`、`$EVENT_LOGINED = 'logined'`

## 使用方式

```php
namespace MyProject\Business;

use DuckPhp\Foundation\Business\BusinessHelper;

class Helper extends BusinessHelper
{
}

// 在 Business 内：
$conf = Helper::Config('database', 'host');
$rows = Helper::Cache()->get('k');          // 取缓存
$ok   = Helper::XpCall([$obj, 'method']);   // 异常封装调用
$errs = Helper::ValidatorValid($_POST, ['age' => 'int|min:1']);
Helper::BusinessThrowOn(!$flag, '业务不允许', 10001);
```
## 注意事项

- `Setting`/`Options` 读取的是 App 的设置与选项；`PathOfProject`/`PathOfRuntime` 来自 App 的项目/运行时路径。
- `Validator*` 三个方法分别是 `filter`/`check`/`valid` 的口径：`ValidatorFilter` 返回过滤后数据、`ValidatorCheck` 失败抛异常、`ValidatorValid` 返回错误数组。
- `AdminService`/`UserService` 分别取 `GlobalAdmin`/`GlobalUser` 的 service，供业务层做认证服务调用。
- 事件常量拼写已修正：`$EVENT_REGISTERING = 'registering'`、`$EVENT_REGISTERED = 'registered'`（旧名为 `REGISTING`/`registed`，工程侧若有监听旧名的代码需一并改）。登录侧沿用框架既有写法 `$EVENT_LOGINING/$EVENT_LOGINED`（与 `GlobalUser` 的 `EVENT_*_LOGINING/LOGINED` 一致），未改。

## 方法列表

### 公共方法

    public static function Setting($key = null, $default = null)
读取应用设置（等价 `App::_()->_Setting()`）。

    public static function AppOptions(string $key, $default = null)
读取应用 options 中某键（未设置返回 `$default`）。

    public static function Config($file_basename, $key = null, $default = null)
读取 `config/` 下某配置文件的内容（经由 `Configer`）。

    public static function XpCall($callback, ...$args)
以“异常封装”方式调用回调并透传结果（经由 `CoreHelper`，业务异常可在上层被统一捕获）。

    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
`$flag` 为真时抛业务异常（默认业务异常类，可指定 `$exception_class`）。

    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
`BusinessThrowOn` 的简写别名（同走业务异常）。

    public static function Cache($object = null)
取（或替换）缓存组件实例。

    public static function PathOfProject(): string
项目根路径（`App::getProjectPath()`）。

    public static function PathOfRuntime(): string
运行时目录路径（`App::getRuntimePath()`）。

    public static function FireGlobalEvent($event, ...$args)
触发全局事件（转发 `GlobalEvent::fire`）。

    public static function OnGlobalEvent($event, $callback)
注册全局事件监听（转发 `GlobalEvent::on`）。

    public static function AdminService()
返回管理员 service（`GlobalAdmin::_()->service()`）。

    public static function UserService()
返回用户 service（`GlobalUser::_()->service()`）。

    public static function Validator($new = null)
取（或替换）`Validator` 组件实例。

    public static function ValidatorFilter($data, $rules, $messages = [])
按规则校验并返回过滤后的数据（失败抛异常，等价 filter 口径）。

    public static function ValidatorCheck($data, $rules, $messages = [])
按规则校验，失败抛异常（check 口径）。

    public static function ValidatorValid($data, $rules, $messages = [])
按规则校验并返回错误数组（valid 口径，无错为空数组）。

## 相关链接

- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — 控制器层助手（含 `AdminService`/`UserService` 的控制器版）
- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — 数据层助手
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — 四层并集门面（`__callStatic`，本层是它的第三查找目标）
