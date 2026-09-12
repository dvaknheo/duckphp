# DuckPhp\GlobalAdmin\AdminSessionTrait

## 简介

`AdminSessionTrait` 是 `AdminSessionInterface` 的默认实现 Trait：用法是把它与一个提供 `get()/set()` 会话读写的宿主组合（典型是同时 `use Foundation\SessionTrait`），即得到“把当前管理员存于会话”的能力。

它以键 `'admin'` 存放管理员数组，并派生 id/name 便捷读取；`unsetCurrentAdmin()` 通过写入空数组实现“退出当前管理员”。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`trait AdminSessionTrait`
- 依赖宿主提供：`get(string $key, $default = null)`、`set(string $key, $value)`（如 `Foundation\SessionTrait`）

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\Foundation\SessionTrait;
use DuckPhp\GlobalAdmin\AdminSessionTrait;

class AdminSession
{
    use SessionTrait;        // get/set/unset（带 session_prefix）
    use AdminSessionTrait;   // 本 Trait

    // 现在具备：getCurrentAdminId()/getCurrentAdminName()/getCurrentAdmin()/setCurrentAdmin()/unsetCurrentAdmin()
}
```

## 注意事项

- 会话键固定为 `'admin'`（`$this->get('admin', [])`）；实际存储前缀由宿主的会话实现决定（如 `session_prefix`）。
- `getCurrentAdminId()` 缺省返回 `0`，`getCurrentAdminName()` 缺省返回 `''`，便于直接判空。
- `setCurrentAdmin($admin)` 直接写入传入值（登录时应写含 `id`/`name` 的数组）。

## 方法列表

### 公共方法

    public function getCurrentAdminId()
读当前管理员 ID（会话 `admin.id`，缺省 `0`）。

    public function getCurrentAdminName(): string
读当前管理员名（会话 `admin.name`，缺省 `''`）。

    public function getCurrentAdmin(): array
读当前管理员数组（会话 `admin`，缺省 `[]`）。

    public function setCurrentAdmin($admin)
把管理员数据写入会话。

    public function unsetCurrentAdmin()
清除当前管理员（写入 `[]`）。

## 相关链接

- [DuckPhp\GlobalAdmin\AdminSessionInterface](GlobalAdmin-AdminSessionInterface.md) — 本 Trait 实现的接口
- [DuckPhp\Foundation\SessionTrait](Foundation-SessionTrait.md) — 提供 `get/set` 的会话基座
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 使用会话的组件
