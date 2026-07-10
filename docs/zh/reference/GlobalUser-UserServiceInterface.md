# DuckPhp\GlobalUser\UserServiceInterface

用户服务接口。

## 简介

`UserServiceInterface` 定义了用户服务层需要实现的批量获取用户名方法。它通常作为 `UserActionInterface` 中 `service()` 返回对象的接口约定。

## 接口定义

```php
namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    public function batchGetUsernames(array $ids);
}
```

## 方法说明

    public function batchGetUsernames(array $ids);
根据用户 ID 数组批量获取用户名，返回 ID 到用户名的映射数组

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md)
