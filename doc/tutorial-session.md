# 会话
## 相关类

`Helper/AppHelper`

## 开始

通常，你把你的 Session 处理的相关动作放在一个单独的 SessionService 里。
这个 SessionService  有些特殊，调用 App 类的 Session 系列方法。以便于在不同环境中处理 不同的 session 。

异常处理也最好弄 SessionException 。

例子:
```php
namespace UserSystemDemo\Service;

use UserSystemDemo\Base\BaseService;
use UserSystemDemo\Base\Helper\ServiceHelper as S;
use UserSystemDemo\Base\App;

class SessionService extends BaseService
{
    public function __construct()
    {
        App::session_start();
    }
    public function getCurrentUser()
    {
        $ret = App::SG()->_SESSION['user'] ?? [];
        SessionServiceException::ThrowOn(empty($ret), '请重新登录');
        
        return $ret;
    }
        public function setCurrentUser($user)
    {
        App::SG()->_SESSION['user'] = $user;
    }
}

```
```php
namespace UserSystemDemo\Service;

use UserSystemDemo\Base\BaseException;

class UserServiceException extends BaseException
{
    //
}
```