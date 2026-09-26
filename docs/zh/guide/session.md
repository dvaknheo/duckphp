# 2-11 会话

> 解决什么问题：Session 怎么读写、前缀怎么隔离、会话里该放什么、以及「会话」和「登录态」的分工。
> 前置：[第 2-5 章 控制器](controllers.md)、[第 1-5 章 配置与设置](configuration.md)。预计 10 分钟。
> 示例：`demo/src/Controller/Session.php`（框架骨架里最小的会话类）。

## 最小示例

会话能力就是一个 trait + 一个薄壳类。注意：**Trait 给的是 `protected` 的 `get()`/`set()`/`unset()`，所以要在会话类里包一层公开的语义化方法**——键名收在类里，调用点只看方法名：

```php
<?php declare(strict_types=1);
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\Controller\SessionTrait;

class Session
{
    use SessionTrait;

    public function setLastNote(int $id): void { $this->set('last_note', $id); }
    public function getLastNote(): ?int        { return $this->get('last_note') ?: null; }
}
```

控制器里只调那些公开方法：

```php
public function remember()
{
    Session::_()->setLastNote((int)Helper::POST('id'));
    Helper::Show(['last' => Session::_()->getLastNote()], 'note/index');
}
```

> ⚠️ 别在控制器里直接写 `Session::_()->set('k', $v)`：Trait 的方法是 `protected`，会报
> `Error: Call to protected method`。骨架 `skeleton/src/Controller/Session.php`（= `demo/src/Controller/Session.php`）
> 里的示例方法默认是注释掉的，你自己加。

## 机制说明

### 1. SessionTrait：带前缀的读写

[DuckPhp\Foundation\Controller\SessionTrait](../reference/Foundation-Controller-SessionTrait.md)（源码 `src/Foundation/Controller/SessionTrait.php`）给任意类加上三个 `protected` 方法：

| 方法 | 作用 |
|---|---|
| `get(string $key, $default = null)` | 读 `session_prefix . $key` |
| `set(string $key, $value)` | 写 `session_prefix . $key` |
| `unset(string $key)` | 删 `session_prefix . $key` |
| `checkSessionStart(): void` | 确保会话已启动并把 `session_prefix` 缓存下来（幂等，内部方法） |

- 首次读写时自动 `session_start()`（经 [`SystemWrapper`](../reference/Core-SystemWrapper.md) 调用，测试里可以替换掉它，见[第 2-17 章](testing.md)）；
- 底层走 [DuckPhp\Core\SuperGlobal](../reference/Core-SuperGlobal.md) 的 `_SessionGet/_SessionSet/_SessionUnset`；
- 三个方法都是 `protected`，**只给自己的类用**，不做成静态 API——会话读写应该收在工程的 `Controller\Session` 一类里。

### 2. `session_prefix`：多应用同进程的隔离

`session_prefix` 是隐藏选项（默认空串，见[参考手册的设置页](../reference/setting.md)）。同一个 PHP 进程里挂多个应用（[第 4-6 章](multi-entry.md)）时，给每个应用配不同的前缀，避免会话键互相覆盖：

```php
// 子应用的 App 里
public $options = [
    'session_prefix' => 'shop_',      // 于是会话类里的 $this->set('uid', 1) 实际写的是 shop_uid
];
```

### 3. 会话里放什么：只放「票据」，不放「数据」

会话只适合放**标识与少量状态**（当前用户 id、上一步的 URL、一次性提示），其余一律按 id 回查数据库：

| 放什么 | 例子 | 为什么 |
|---|---|---|
| ✅ 标识 | 会话类里的 `$this->set('uid', $id)` | 会话是客户端凭据，越少越好 |
| ✅ 一次性提示 | flash 消息、`url_back` | 下次请求即用即丢 |
| ❌ 业务数据快照 | 整个订单数组 | 会过期、会膨胀、会和数据库不一致 |

### 4. 会话 ≠ 登录态

`SessionTrait` 只解决「怎么读写会话」。**当前是谁、怎么登录、未登录怎么办**是另一套东西：

- 用户系统：[第 2-19 章 使用用户系统](user.md)（调用方视角）、[第 4-11 章 实现用户系统](impl-user.md)（`UserSessionTrait` 把当前用户存进会话键 `user`）；
- 管理员系统：[第 2-20 章 使用管理员系统](admin.md)、[第 4-12 章 实现管理员系统](impl-admin.md)（`AdminSessionTrait`，键为 `admin`）。

它们内部就是**组合 `SessionTrait`** 实现的——这也是为什么本章是那两章的前置。

## 常见写法

**① 会话类里包一层语义化方法**（推荐：调用点看不出键名）

```php
class Session
{
    use SessionTrait;

    public function setCurrentUserId(int $id): void   { $this->set('uid', $id); }
    public function getCurrentUserId(): ?int          { return $this->get('uid') ?: null; }
    public function forgetCurrentUser(): void         { $this->unset('uid'); }
}
```

**② 一次性提示（flash）：写入 + 取出即清**

```php
// 会话类里：一对方法，取出时顺手 unset
public function flash(string $msg): void { $this->set('flash', $msg); }
public function takeFlash(): ?string
{
    $msg = (string)$this->get('flash');
    $this->unset('flash');
    return $msg ?: null;
}

// 控制器里
Session::_()->flash('保存成功');     // 写入
$msg = Session::_()->takeFlash();    // 取出并立刻清掉，避免重复显示
```

**③ 换掉会话实现（测试 / 常驻进程）**

```php
Helper::system_wrapper_replace([
    'session_start' => function ($options = []) { /* 假装启动，比如挂到数组上 */ },
]);
```

**④ 用会话接住「登录后回到原页」**

```php
// 会话类里
public function setUrlBack(string $url): void { $this->set('url_back', $url); }
public function takeUrlBack(string $fallback = 'home/index'): string
{
    return (string)$this->get('url_back', $fallback);
}

// 未登录跳转前
Session::_()->setUrlBack(Helper::PathInfo());
// 登录成功后
Helper::Show302(Session::_()->takeUrlBack());
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 写了读不到 | 两次请求的 `session_prefix` 不同（多应用/多子目录） | 前缀不同就是不同的键；对齐前缀或改用同一应用 |
| `session_start()` 报「headers already sent」 | 输出先于会话启动（视图里 `echo` 的副作用） | 让会话第一次读写在输出之前；或继承 `SessionTrait` 的类在构造函数里先探一次 |
| 测试里会话状态串味 | 会话是进程/全局状态 | 用 `system_wrapper_replace` 替换 `session_*`，或 `Session::_()` 换成本地实现 |
| 调用 `Session::_()->set('k', $v)` 报 `Call to protected method` | `SessionTrait` 的三个方法是 `protected`，只能在会话类内部调 | 在会话类里包一层 `public` 语义化方法，调用点只用那些方法（见「常见写法」①②④） |
| 会话里塞了大数组后变慢 | 会话每请求全量读写、还要序列化 | 只存标识，数据放数据库/缓存 |

## 下一步

- [第 2-19 章 使用用户系统](user.md)：会话里放的那点「登录票据」怎么变成「当前用户」。
- [第 2-20 章 使用管理员系统](admin.md)：后台登录、权限判断与菜单。
- [第 2-2 章 请求生命周期](lifecycle.md)：这一次请求框架内部都做了什么。
- 参考手册：[Foundation\Controller\SessionTrait](../reference/Foundation-Controller-SessionTrait.md)、[Core\SuperGlobal](../reference/Core-SuperGlobal.md)、[Core\SystemWrapper](../reference/Core-SystemWrapper.md)、[应用设置 Setting](../reference/setting.md)