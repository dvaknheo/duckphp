# DuckPhp\GlobalUser\GlobalUser

全局用户组件。

## 简介

`GlobalUser` 组件为用户系统提供统一的访问入口。它基于**回调配置**模式，通过选项指定用户相关的回调函数，而非传统的继承/接口实现。

## 选项

### 回调选项

通过 `user_callback_*` 选项指定回调函数，每个回调对应一个方法：

| 选项 | 对应方法 | 说明 |
|---|---|---|
| `user_callback_get_id` | `id()` | 获取当前用户 ID，参数 `(bool $check_login)` |
| `user_callback_get_name` | `name()` | 获取当前用户名 |
| `user_callback_get_data` | `data()` | 获取当前用户数据数组 |
| `user_callback_get_service` | `localService()` | 返回 `UserServiceInterface` 实例 |
| `user_callback_url_home` | `urlForHome()` | 生成首页 URL |
| `user_callback_url_regist` | `urlForRegist()` | 生成注册页 URL |
| `user_callback_url_login` | `urlForLogin()` | 生成登录页 URL |
| `user_callback_url_logout` | `urlForLogout()` | 生成登出页 URL |

### 直接 URL 选项

如果未设置对应回调，可用直接 URL：

| 选项 | 说明 |
|---|---|
| `user_url_home` | 首页 URL |
| `user_url_regist` | 注册页 URL |
| `user_url_login` | 登录页 URL |
| `user_url_logout` | 登出页 URL |

### 视图选项

| 选项 | 说明 |
|---|---|
| `user_view_file_header` | 用户界面 header 视图文件 |
| `user_view_file_footer` | 用户界面 footer 视图文件 |

## 使用方式

### 配置回调

在子 App 的 `$options` 中配置回调，回调数组 `[ClassName::class, 'method']` 会被自动实例化：

```php
$options = [
    'user_callback_get_id' => [UserAction::class, 'id'],
    'user_callback_get_name' => [UserAction::class, 'name'],
    'user_callback_get_service' => [UserAction::class, 'service'],
    // ...
];
```

### 基础调用

```php
use DuckPhp\GlobalUser\GlobalUser;

$userId = GlobalUser::_()->id();              // 当前用户 ID
$userName = GlobalUser::_()->name();           // 当前用户名
$userData = GlobalUser::_()->data();           // 当前用户数据
```

### URL 生成

```php
$url = GlobalUser::_()->urlForLogin('/back');  // 登录后跳回 /back
$url = GlobalUser::_()->urlForLogout();
$url = GlobalUser::_()->urlForHome();
$url = GlobalUser::_()->urlForRegist();
```

### 服务委托

```php
$service = GlobalUser::_()->service();          // UserServiceInterface 的 PhaseProxy
$service->checkAccess($userId, __CLASS__, __METHOD__);
$service->log($userId, '操作', 'audit');
$usernames = $service->batchGetUsernames([1, 2, 3]);
```

### 视图融合

```php
$data = GlobalUser::_()->mergeViewData($input);
// $data['__view_data']['header'] 和 ['footer'] 已填充
```

## 方法列表

| 方法 | 说明 |
|---|---|
| `id(bool $check_login = true)` | 获取当前用户 ID。`$check_login = true` 且未登录时抛出异常（来自回调实现） |
| `name(bool $check_login = true): string` | 获取当前用户名 |
| `data(bool $check_login = true): array` | 获取当前用户数据 |
| `service()` | 返回 `UserServiceInterface` 的 PhaseProxy 实例 |
| `localService()` | 返回本地 `UserServiceInterface` 实例 |
| `urlForHome(?string $url_back, ?array $ext): string` | 生成首页 URL |
| `urlForRegist(?string $url_back, ?array $ext): string` | 生成注册页 URL |
| `urlForLogin(?string $url_back, ?array $ext): string` | 生成登录页 URL |
| `urlForLogout(?string $url_back, ?array $ext): string` | 生成登出页 URL |
| `mergeViewData(array $input): array` | 融合用户视图头尾数据 |
| `checkAccess($class, $method, $url)` | 检查权限，委托给 `localService()->checkAccess()` |
| `log($string, $type, $ext)` | 记录用户操作日志，委托给 `localService()->log()` |
| `batchGetUsernames(array $ids): array` | 批量获取用户名，委托给 `localService()->batchGetUsernames()` |

## 相关链接

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md)
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md)
