# DuckPhp\Foundation\SimpleSessionTrait

简单 Session 管理 Trait。

## 简介

`DuckPhp\Foundation\SimpleSessionTrait` 提供了一套简单的 Session 读写封装。它会自动启动 Session，并支持通过 `session_prefix` 配置实现键前缀隔离。

## 选项

### 依赖配置

| 配置 | 说明 |
|---|---|
| `session_prefix` | 应用配置中的 Session 键前缀。读取 `App::Current()->options['session_prefix']` |

## 使用方式

### 在 Session 管理类中使用

```php
use DuckPhp\Foundation\SimpleSessionTrait;

class MySession
{
    use SimpleSessionTrait;

    public function getUserId()
    {
        return $this->get('user_id');
    }

    public function setUserId($id)
    {
        $this->set('user_id', $id);
    }

    public function clearUserId()
    {
        $this->unset('user_id');
    }
}
```

### 单例调用

```php
$session = MySession::_();
$session->setUserId(123);
$id = $session->getUserId();
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'session_prefix' => 'myapp_',
    ];
}
```

## 注意事项

1. 该 Trait 使用 `DuckPhp\Core\SingletonTrait`，内部以单例形式工作。
2. 首次读写时会自动调用 `SystemWrapper::_()->_session_start()` 启动 Session。
3. 键名会自动拼接 `App::Current()->options['session_prefix']`。
4. `unset` 是 PHP 保留关键字，但此处作为方法名是被允许的。

## 方法列表

### 受保护方法

| 方法 | 说明 |
|---|---|
| `checkSessionStart()` | 检查并启动 Session |
| `get($key, $default = null)` | 读取 Session 值 |
| `set($key, $value)` | 写入 Session 值 |
| `unset($key)` | 删除 Session 值 |

## 相关链接

- [DuckPhp\Core\SingletonTrait](Core-SingletonTrait.md)
- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md)
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md)
- [DuckPhp\Core\App](Core-App.md)
