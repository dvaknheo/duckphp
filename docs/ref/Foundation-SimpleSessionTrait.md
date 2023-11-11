# DuckPhp\Foundation\SimpleSessionTrait
[toc]

## 简介

SimpleSessionTrait 提供了一个 Session 数据管理的基类，你可以在这上面扩充。


## 方法

    protected function checkSessionStart()
检查 session 是否启动

    protected function get($key, $default = null)
获取seesion

    protected function set($key, $value)
设置 seesion

    protected function unset($key)
取消 session

## 说明

SimpleSessionTrait 用到 App 的一个选项 `session_prefix`;

你可以像 model 那么使用 SimpleSessionTrait;

```php
namespace AdvanceDemo\Controller;

use DuckPhp\Foundation\SimpleSessionTrait;

class Session
{
    use SimpleSessionTrait;
    public function getCurrentUser()
    {
        return $this->get('user', []);
    }
    public function setCurrentUser($user)
    {
        return $this->set('user', $user);
    }
}

```
