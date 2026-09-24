# DuckPhp\Foundation\Controller\UserControllerBase

## 简介

`UserControllerBase` 是工程「前台登录用户控制器」的推荐基类：`implements UserControllerInterface` 作标识，并在构造时自动执行登录/权限检查（`initController()`）：

1. `Helper::checkInstall(null)`：未安装则跳安装页；
2. `Helper::User()->id(true)`：确保已登录；
3. `Helper::User()->canAccess()`：判断当前用户是否能访问当前路由；
4. 无权时：非 Ajax 请求 `Show302` 到登录页并 `exit()`；Ajax 请求则返回 JSON `{error_code: -2, error_message: 'NEED_PERMISSION'}`。

与 `AdminControllerBase` 结构完全同构，只是面向 `GlobalUser`。

## 类信息

- 命名空间：`DuckPhp\Foundation\Controller`
- 声明：`class UserControllerBase implements UserControllerInterface`
- 实现的接口：`DuckPhp\GlobalUser\UserControllerInterface`

## 使用方式

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\UserControllerBase;

class CenterController extends UserControllerBase
{
    public function action_index()
    {
        // 能执行到这里说明已通过前台登录与权限检查
    }
}
```

## 注意事项

- 依赖 `GlobalUser` 已正确配置 provider。
- `onNeedPermission()` 的 Ajax 分支硬编码 `error_code => -2`、`error_message => 'NEED_PERMISSION'`；非 Ajax 分支用 `ControllerHelper::SERVER('REQUEST_URI','')` 取 `PHP_URL_PATH` 作为 `url_back` 传给 `urlForLogin()`。

## 方法列表

### 公共方法

    public function __construct()
构造时自动执行 `initController()`（登录/权限检查）。

### 受保护方法

    protected function onNeedPermission()
无权时统一处理钩子：非 Ajax 跳登录页，Ajax 返回 `error_code:-2` JSON。

    protected function initController()
检查安装→强制登录→权限检查：无权时调用 `onNeedPermission()` 后 `exit()`。

## 相关链接

- [DuckPhp\GlobalUser\User](GlobalUser-GlobalUser.md) — 权限与登录提供方
- [DuckPhp\GlobalUser\UserControllerInterface](GlobalUser-UserControllerInterface.md) — 标记接口
- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — 内部使用的静态助手
