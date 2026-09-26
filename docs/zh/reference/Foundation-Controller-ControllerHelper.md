# DuckPhp\Foundation\Controller\ControllerHelper

## 简介

`ControllerHelper` 是面向 **Controller（控制器层）** 的静态助手集合（方法就在本类里，不再有 trait），也是四层 Helper 中最丰富的一个。它提供控制器日常所需的全部便捷入口：

- 请求输入：`GET/POST/REQUEST/COOKIE/SERVER`；
- 请求类型判断：`IsPost`（当前是否 POST）、`IsAjax`（是否 Ajax）；
- 路由与 URL：`PathInfo/Url/Res/Domain/Parameter/getRouteCallingClass/getRouteCallingMethod`；
- 输出：`Show/Render/Show302/Show404/ShowJson`；
- 可替换系统函数：`header/setcookie/exit`；
- 异常处理注册：`assignExceptionHandler/setMultiExceptionHandler/setDefaultExceptionHandler/ControllerThrowOn`；
- 分页：`Pager/PageNo/PageWindow/PageHtml`；
- 配置/设置：`Setting/AppOptions/Config`；
- 事件：`FireGlobalEvent/OnGlobalEvent`；
- 用户/管理员：`Admin*`/`User*` 系列。

本类自带 10 个动作级事件名常量与 8 个异常码/异常消息常量，均为 `User`/`Admin` 对应常量的别名。

## 类信息

- 命名空间：`DuckPhp\Foundation\Controller`
- 声明：`class ControllerHelper`
- 使用的 Trait：`DuckPhp\Core\SingletonExTrait`
- 事件名常量（`User` 别名）：`EVENT_ACTION_USER_REGISTERING` / `EVENT_ACTION_USER_REGISTERED` / `EVENT_ACTION_USER_LOGINING` / `EVENT_ACTION_USER_LOGINED` / `EVENT_ACTION_USER_LOGOUTING` / `EVENT_ACTION_USER_LOGOUTED`
- 事件名常量（`Admin` 别名）：`EVENT_ACTION_ADMIN_LOGINING` / `EVENT_ACTION_ADMIN_LOGINED` / `EVENT_ACTION_ADMIN_LOGOUTING` / `EVENT_ACTION_ADMIN_LOGOUTED`
- 异常码/消息常量（`User` 别名）：`EXCEPTION_CODE_USER_NEED_LOGIN` / `EXCEPTION_MESSAGE_USER_NEED_LOGIN` / `EXCEPTION_CODE_USER_NEED_PERMISSION` / `EXCEPTION_MESSAGE_USER_NEED_PERMISSION`
- 异常码/消息常量（`Admin` 别名）：`EXCEPTION_CODE_ADMIN_NEED_LOGIN` / `EXCEPTION_MESSAGE_ADMIN_NEED_LOGIN` / `EXCEPTION_CODE_ADMIN_NEED_PERMISSION` / `EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION`

## 使用方式

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\ControllerHelper;

// 工程惯例：本层 Helper 只是薄薄一层，方便加本项目自己的便捷方法
class Helper extends ControllerHelper
{
}

// 在 Controller 里（Helper 与控制器同命名空间）：
public function action_login()
{
    $name = Helper::POST('name');
    if (Helper::IsAjax()) {
        Helper::ShowJson(['ok' => true]);
        return;
    }
    Helper::assignViewData('name', $name);
    Helper::Show(get_defined_vars(), 'login');
}
```
## 注意事项

- `Show($data, $view)` 最终走 `App::_()->_Show()`（含页眉页脚包裹与视图文件查找）；`Render` 走 `View::_Render()`（不含页眉页脚）。
- `GET/POST/REQUEST/COOKIE/SERVER` 均来自 `SuperGlobal`，返回 `$default` 兜底。
- `Admin/AdminId/AdminName/User/UserId/UserName` 对应 `GlobalAdmin`/`GlobalUser` 的动作接口与登录查询；`AdminService/UserService` 取 service。
- `PageHtml($total, $options)` 由 `Pager` 生成 HTML 分页条。
- 控制器里建议用 `Show302/Show404` 而非直接 `exit`（更可测）；需直出时可 `exit()`（经 SystemWrapper）。
- 本类的全部事件名常量与异常码/消息常量均为 `GlobalUser\User` / `GlobalAdmin\Admin` 对应常量的别名，直接复用框架既有值，无额外行为。

## 方法列表

### 公共方法

    public static function Setting($key = null, $default = null)
读取应用设置（等价 `App::Setting`）。

    public static function AppOptions(string $key, $default = null)
读取应用 options 中某键。

    public static function XpCall($callback, ...$args)
异常封装调用（转发 `CoreHelper::_XpCall`）。

    public static function Config($file_basename, $key = null, $default = null)
读取 `config/` 下配置文件内容（转发 `Configer`）。

    public static function getRouteCallingClass(): ?string
当前路由命中的控制器类（`Route` 上下文）。

    public static function getRouteCallingMethod(): ?string
当前路由命中的方法名。

    public static function PathInfo(): ?string
当前 PATH_INFO（`Route::PathInfo`）。

    public static function Url($url = null)
生成应用内 URL（`Route::_Url`）。

    public static function Domain(bool $use_scheme = false): string
当前域名（`Route::_Domain`）。

    public static function Res($url = null)
生成资源 URL（`Route::_Res`）。

    public static function Parameter($key = null, $default = null)
取路由参数（`Route::Parameter`）。

    public static function Render($view, $data = null)
渲染视图并返回（`View::_Render`）。

    public static function Show($data = [], $view = '')
渲染页面并输出（走 `App::_Show`，含页眉页脚）。

    public static function checkInstall(?string $url_install = null)
未安装时跳转到安装页（`App::checkInstallToPage`）。

    public static function setViewHeaderFooter($header_file = null, $footer_file = null)
设置视图的页眉/页脚模板（转发 `View::setViewHeaderFooter`；参数是**视图名**，相对 `path_view`）。

    public static function assignViewData($key, $value = null)
向视图数据赋值（转发 `View::assignViewData`）。

    public static function IsAjax()
是否 Ajax 请求（`CoreHelper::IsAjax`）。

    public static function Show302($url)
302 跳转（`CoreHelper::Show302`）。

    public static function Show404()
输出 404 页（`CoreHelper::Show404`）。

    public static function ShowJson($ret, $flags = 0)
输出 JSON（`CoreHelper::ShowJson`）。

    public static function header($output, bool $replace = true, int $http_response_code = 0)
发送 HTTP 头（经 SystemWrapper）。

    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
写 Cookie（经 SystemWrapper）。

    public static function exit($code = 0)
退出（经 SystemWrapper，可配为抛异常）。

    public static function assignExceptionHandler($classes, $callback = null)
为特定异常类注册处理器（转发 `ExceptionManager`）。

    public static function setMultiExceptionHandler(array $classes, $callback)
为多个异常类注册同一处理器。

    public static function setDefaultExceptionHandler($callback)
设置默认异常处理器。

    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
`$flag` 为真时抛控制器异常。

    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
`ControllerThrowOn` 的简写别名（同走控制器异常）。

    public static function IsPost()
当前请求是否为 POST（经 `SuperGlobal` 读 `REQUEST_METHOD`）。

    public static function GET($key = null, $default = null)
读 `$_GET`（经 `SuperGlobal`）。

    public static function POST($key = null, $default = null)
读 `$_POST`。

    public static function REQUEST($key = null, $default = null)
读 `$_REQUEST`。

    public static function COOKIE($key = null, $default = null)
读 `$_COOKIE`。

    public static function SERVER($key = null, $default = null)
读 `$_SERVER`。

    public static function Pager($new = null)
取（或替换）分页组件实例。

    public static function PageNo($new_value = null)
取/设当前页码。

    public static function PageWindow($new_value = null)
取/设分页窗口。

    public static function PageHtml($total, $options = [])
生成分页 HTML（`Pager::PageHtml`）。

    public static function FireGlobalEvent($event, ...$args)
触发全局事件。

    public static function OnGlobalEvent($event, $callback)
注册全局事件监听。

    public static function Admin()
返回管理员动作接口（`GlobalAdmin::_()`）。

    public static function AdminId(bool $check_login = true)
当前管理员 ID（未登录按 `$check_login` 处理）。

    public static function AdminName(bool $check_login = true)
当前管理员名。

    public static function AdminService()
管理员 service（`GlobalAdmin::_()->service()`）。

    public static function User()
返回用户动作接口（`GlobalUser::_()`）。

    public static function UserId(bool $check_login = true)
当前用户 ID。

    public static function UserName(bool $check_login = true)
当前用户名。

    public static function UserService()
用户 service（`GlobalUser::_()->service()`）。

## 相关链接

- [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) — 业务层助手（本类也含其 `AdminService/UserService` 等）
- [DuckPhp\Foundation\System\SystemHelper](Foundation-System-SystemHelper.md) — 应用级助手
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — 四层并集门面（`__callStatic`，本层是它的第二查找目标）
