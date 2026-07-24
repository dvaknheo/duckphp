# DuckPhp\GlobalAdmin\GlobalAdmin

全局管理员组件。

## 简介

`GlobalAdmin` 组件为管理员系统提供统一的访问入口。与 `GlobalUser` 相同，它基于**回调配置**模式，通过选项指定管理员相关的回调函数。

## 选项

### 回调选项

| 选项 | 对应方法 | 说明 |
|---|---|---|
| `admin_callback_get_id` | `id()` | 获取当前管理员 ID，参数 `(bool $check_login)` |
| `admin_callback_get_name` | `name()` | 获取当前管理员名 |
| `admin_callback_get_data` | `data()` | 获取当前管理员数据数组 |
| `admin_callback_get_service` | `localService()` | 返回 `AdminServiceInterface` 实例 |
| `admin_callback_url_home` | `urlForHome()` | 生成后台首页 URL |
| `admin_callback_url_login` | `urlForLogin()` | 生成登录页 URL |
| `admin_callback_url_logout` | `urlForLogout()` | 生成登出页 URL |

### 直接 URL 选项

| 选项 | 说明 |
|---|---|
| `admin_url_home` | 后台首页 URL |
| `admin_url_login` | 登录页 URL |
| `admin_url_logout` | 登出页 URL |

### 视图选项

| 选项 | 说明 |
|---|---|
| `admin_view_file_header` | 后台界面 header 视图文件 |
| `admin_view_file_footer` | 后台界面 footer 视图文件 |

## 使用方式

### 基础调用

```php
use DuckPhp\GlobalAdmin\GlobalAdmin;

$adminId = GlobalAdmin::_()->id();              // 当前管理员 ID
$adminName = GlobalAdmin::_()->name();           // 当前管理员名
$adminData = GlobalAdmin::_()->data();           // 当前管理员数据
```

### URL 生成

```php
$url = GlobalAdmin::_()->urlForLogin('/back');
$url = GlobalAdmin::_()->urlForLogout();
$url = GlobalAdmin::_()->urlForHome();
```

### 服务委托

```php
$service = GlobalAdmin::_()->service();          // AdminServiceInterface 的 PhaseProxy
$service->checkAccess($adminId, __CLASS__, __METHOD__);
$service->isSuper($adminId);
$service->log($adminId, '操作', 'audit');
```

### 视图融合

```php
$data = GlobalAdmin::_()->mergeViewData($input);
```

## 方法列表

| 方法 | 说明 |
|---|---|
| `id(bool $check_login = true)` | 获取当前管理员 ID。`$check_login = true` 且未登录时抛出异常（来自回调实现） |
| `name(bool $check_login = true): string` | 获取当前管理员名 |
| `data(bool $check_login = true): array` | 获取当前管理员数据 |
| `service()` | 返回 `AdminServiceInterface` 的 PhaseProxy 实例 |
| `localService()` | 返回本地 `AdminServiceInterface` 实例 |
| `urlForHome(?string $url_back, ?array $ext): string` | 生成后台首页 URL |
| `urlForLogin(?string $url_back, ?array $ext): string` | 生成登录页 URL |
| `urlForLogout(?string $url_back, ?array $ext): string` | 生成登出页 URL |
| `mergeViewData(array $input): array` | 融合管理员视图头尾数据 |
| `checkAccess($class, $method, $url)` | 检查权限，委托给 `localService()->checkAccess()` |
| `log($string, $type, $ext)` | 记录管理员操作日志，委托给 `localService()->log()` |
| `isSuper(): bool` | 判断是否超级管理员，委托给 `localService()->isSuper()` |

## 相关链接

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md)
