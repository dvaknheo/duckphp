# 会话
[toc]
## 相关类

`Helper/AppHelper`

## 开始

通常，你把你的 Session 处理的相关动作放在一个单独的 SessionBusiness 里。
这个 SessionBusiness  有些特殊，调用 App 类的 Session 系列方法。以便于在不同环境中处理 不同的 session 。

异常处理也最好成弄 SessionException 。

例子:
```php
namespace UserSystemDemo\Service;

use UserSystemDemo\Base\BaseService;
use UserSystemDemo\Base\Helper\ServiceHelper as S;
use UserSystemDemo\Base\App;

class SessionBusiness extends BaseService
{
    public function __construct()
    {
        App::session_start();
    }
    public function getCurrentUser()
    {
        $ret = $_SESSION['user'] ?? [];
        SessionBusinessException::ThrowOn(empty($ret), '请重新登录');
        
        return $ret;
    }
    public function setCurrentUser($user)
    {
        App::SuperGloabl()->_SESSION['user'] = $user;
    }
}

```
```php
namespace UserSystemDemo\Service;

use UserSystemDemo\Base\BaseException;

class SessionBusinessException extends BaseException
{
    //
}
```

这个例子，在 controller 里调用 SessionBusiness::G()->getCurrentUser() 得到当前用户数据。

如果得不到，就抛出 SessionBusinessException 。

如果还有什么需要 Session 数据的地方，继续填充这个 Session 类，而不是到处直接使用 session 。

免得发生不知道这个 session 数据在哪里用的？ 这样的疑问。