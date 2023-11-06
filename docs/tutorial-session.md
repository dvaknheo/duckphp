# 会话
[toc]
## 相关类

## 开始


例子:
```php
namespace UserSystemDemo\System;

use DuckPhp\Foundation\Simple;
use UserSystemDemo\System\App;

class SessionBusiness
{
    use SingletonExTrait;
    
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
