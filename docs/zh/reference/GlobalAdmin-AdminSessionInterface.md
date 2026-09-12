# DuckPhp\GlobalAdmin\AdminSessionInterface

## 简介

`AdminSessionInterface` 是「管理员会话」契约接口：定义把“当前管理员”存入/取出与会话的能力，共 5 个方法：`setCurrentAdmin($admin)`、`unsetCurrentAdmin()`、`getCurrentAdmin()`、`getCurrentAdminName()`、`getCurrentAdminId()`。

它与 `AdminSessionTrait`（默认实现，基于宿主的 `get()/set()` 会话读写）配套；`GlobalAdmin` 通过 `admin_callback_for_session` 选项拿到本接口的实现，从而以“会话”方式获取当前管理员（见 `GlobalAdmin::id()/name()`）。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`interface AdminSessionInterface`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminSessionInterface;
use DuckPhp\GlobalAdmin\AdminSessionTrait;
use DuckPhp\Foundation\SessionTrait;

class AdminSession implements AdminSessionInterface
{
    use SessionTrait;        // 提供 get/set/unset（带 session_prefix）
    use AdminSessionTrait;   // 实现本接口的 5 个方法
}

// App 选项：'admin_callback_for_session' => [AdminSession::class, ???]
```

## 注意事项

- `GlobalAdmin` 只要检测到配置了 `admin_callback_for_session`，`id()/name()` 就会优先走会话路径，而不再用 `admin_callback_for_id/name`。
- `getCurrentAdmin()` 约定返回管理员数组（含 `id`/`name` 等键）；`getCurrentAdminId()`/`getCurrentAdminName()` 是便捷读法。

## 方法列表

### 公共方法

    public function setCurrentAdmin($admin)
写入当前管理员（通常是登录成功后）。

    public function unsetCurrentAdmin()
清除当前管理员（退出登录）。

    public function getCurrentAdmin()
读取当前管理员（数组）。

    public function getCurrentAdminName()
读取当前管理员名。

    public function getCurrentAdminId()
读取当前管理员 ID。

## 相关链接

- [DuckPhp\GlobalAdmin\AdminSessionTrait](GlobalAdmin-AdminSessionTrait.md) — 默认实现
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 通过 `admin_callback_for_session` 使用本接口
