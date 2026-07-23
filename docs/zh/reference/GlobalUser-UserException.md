# DuckPhp\GlobalUser\UserException

用户异常类。

## 简介

`UserException` 是用户模块的基础异常类，继承自 `DuckPhp\Core\DuckPhpSystemException`。

当用户操作（如身份验证、注册失败）时抛出此异常。

## 类定义

```php
namespace DuckPhp\GlobalUser;

use DuckPhp\Core\DuckPhpSystemException;

class UserException extends DuckPhpSystemException
{
    //
}
```

## 使用方式

```php
use DuckPhp\GlobalUser\UserException;

throw new UserException('用户未登录');
```

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md)
