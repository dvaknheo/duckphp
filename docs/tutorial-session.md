# 会话
[toc]
## 相关类


## 开始

通常，你把你的 Session 处理的相关动作放在一个单独的 SessionManager 里。
SessionManager 调用 App 类的 Session 系列方法。以便于在不同环境中处理 不同的 session 。

异常处理名字则是 SessionException 。

然后控制器通过 C::SessionManager() 或 App::SessionManager() 来调用。

例子:
```php
namespace UserSystemDemo\System;

use DuckPhp\SingletonEx\SingletonExTrait;
use UserSystemDemo\System\App;

class SessionBusiness
{
    use SingletonExTrait;
    
    public function __construct()
    {
        App::session_start();
    }
    public function getCurrentUser()
    {
        App::SessionGet('user', []);
        SessionException::ThrowOn(empty($ret), '请重新登录');
        
        return $ret;
    }
    public function setCurrentUser($user)
    {
        App::SessionSet('user', $user);
    }
}

```
```php
namespace UserSystemDemo\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
```
```php
namespace UserSystemDemo\Helper;

use DuckPhp\Helper\ControllerHelper as Helper;

class ControllerHelper extends Helper
{

    public static function SessionManager()
    {
        return SessionManager::G();
    }
}

```


在 controller 里调用 C::SessionManager()->getCurrentUser() 得到当前用户数据。


如果还有什么需要 Session 数据的地方，继续填充这个 Session 类，而不是到处直接使用 session 。

免得发生不知道这个 session 数据在哪里用的？ 这样的疑问。

如果你要做成插件使用，还要注意 session 的前缀

或许下个版本会提供 DuckPhp\Ext\SessionManager 便于扩充