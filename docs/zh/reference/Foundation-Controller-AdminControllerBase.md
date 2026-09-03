# DuckPhp\Foundation\Controller\AdminControllerBase

## 简介

`AdminControllerBase` 是工程「后台管理员控制器」的推荐基类：`implements AdminControllerInterface` 作标识，并在构造时自动执行登录/权限检查（`initController()`）：

1. `Helper::checkInstall(null)`：未安装则跳安装页；
2. `Helper::Admin()->canAccess()`：判断当前管理员是否能访问当前路由；
3. 无权限时：非 Ajax 请求 `Show302` 到登录页并 `exit()`；Ajax 请求则抛 `AdminException("can not access", -1)`。

因此继承它的控制器，在动作执行前已完成“是否后台、是否已登录、是否有权”的兜底。

## 类信息

- 命名空间：`DuckPhp\Foundation\Controller`
- 声明：`class AdminControllerBase implements AdminControllerInterface`
- 实现的接口：`DuckPhp\GlobalAdmin\AdminControllerInterface`

## 使用方式

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\AdminControllerBase;

class DashboardController extends AdminControllerBase
{
    public function action_index()
    {
        // 能执行到这里说明已通过后台登录与权限检查
    }
}
```

## 注意事项

- 权限判定依赖 `GlobalAdmin` 已正确配置（`Admin()`/`canAccess()`/`urlForLogin()` 均有 provider）；`AdminException` 用于 Ajax 无权场景，便于前端捕获。
- 若你的控制器需要更细粒度权限，可在动作内再调用 `Helper::Admin()->canAccess($class, $method)`。

## 方法列表

### 公共方法

    public function __construct()
构造时自动执行 `initController()`（登录/权限检查）。

### 受保护方法

    protected function initController()
检查安装与权限：无权时非 Ajax 跳登录页，Ajax 抛 `AdminException`。

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 权限与登录提供方
- [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) — 标记接口
- [DuckPhp\GlobalAdmin\AdminException](GlobalAdmin-AdminException.md) — Ajax 无权异常
- [DuckPhp\Foundation\Controller\Helper](Foundation-Controller-Helper.md) — 内部使用的静态助手
