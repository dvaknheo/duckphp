# DuckPhp\GlobalAdmin\AdminException

管理员异常类。

## 简介

`AdminException` 是管理员模块的基础异常类，继承自 `DuckPhp\Core\DuckPhpSystemException`。

当管理员操作（如身份验证、权限检查）失败时（如 `GlobalAdmin::id()` 在未登录时调用），抛出此异常。

## 类定义

```php
namespace DuckPhp\GlobalAdmin;

use DuckPhp\Core\DuckPhpSystemException;

class AdminException extends DuckPhpSystemException
{
    //
}
```

## 使用方式

```php
use DuckPhp\GlobalAdmin\AdminException;

throw new AdminException('管理员未登录');
```

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md)
