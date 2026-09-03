# DuckPhp\Foundation\SessionTrait

## 简介

`SessionTrait` 为组合它的类提供**带前缀的 Session 读写**：首次访问时自动 `session_start()`（经 `SystemWrapper`），并用 `App::options['session_prefix']` 作为键前缀隔离命名空间，避免与其它应用/模块的会话键冲突。

实现要点：
- `checkSessionStart()`：已启动则跳过；否则 `_session_start()` 并把 `session_prefix` 从 App 选项读入缓存。
- `get/set/unset`：读写删除 `session_prefix + key` 对应的会话变量（经 `SuperGlobal`）。

## 类信息

- 命名空间：`DuckPhp\Foundation`
- 声明：`trait SessionTrait`
- 使用的 Trait：`DuckPhp\Core\SingletonExTrait`
- 使用方：工程中需要会话的控制器/系统类（如 `Controller\Session`）

## 使用方式

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\SessionTrait;

class Session
{
    use SessionTrait;

    public function remember($name)
    {
        $this->set('name', $name);      // 实际写入 session_prefix + 'name'
        return $this->get('name');
    }
    public function forget()
    {
        $this->unset('name');
    }
}
```

## 注意事项

- 三个读写方法都是 `protected`，供组合类内部使用（不暴露为静态 API）。
- Session 键前缀来自应用选项 `session_prefix`；未配置时前缀为空字符串。
- `session_start` 经 `SystemWrapper` 调用，测试环境可替换注入。

## 方法列表

### 受保护方法

    protected function checkSessionStart(): void
确保会话已启动并缓存 `session_prefix`（幂等）。

    protected function get(string $key, $default = null)
读会话变量（`session_prefix + $key`），无则返回 `$default`。

    protected function set(string $key, $value)
写会话变量（`session_prefix + $key`）。

    protected function unset(string $key)
删除会话变量（`session_prefix + $key`）。

## 相关链接

- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) — 会话读写的底层封装
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) — session_start 的可替换实现
