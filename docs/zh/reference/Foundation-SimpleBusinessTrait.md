# DuckPhp\Foundation\SimpleBusinessTrait

简单业务类 Trait。

## 简介

`DuckPhp\Foundation\SimpleBusinessTrait` 是业务层类的简化组合 Trait，同时引入了 `DuckPhp\Core\SingletonTrait` 和 `DuckPhp\Component\ZCallTrait`，使业务类具备单例能力和快速调用能力。

## 选项

无。

## 使用方式

### 在业务类中使用

```php
use DuckPhp\Foundation\SimpleBusinessTrait;

class UserBusiness
{
    use SimpleBusinessTrait;

    public function getUser($id)
    {
        // ...
    }
}
```

### 单例调用

```php
$user = UserBusiness::_()->getUser($id);
```

### 快速调用

```php
$ret = UserBusiness::_Z()->getUser($id);
```

## 注意事项

1. 该 Trait 本身没有定义方法，所有方法均来自 `SingletonTrait` 和 `ZCallTrait`。
2. 业务类使用此 Trait 后，可以像 `Business::method()` 一样通过单例访问。

## 方法列表

### 公共方法

来自引入的 Trait，无自有方法。

## 相关链接

- [DuckPhp\Core\SingletonTrait](Core-SingletonTrait.md)
- [DuckPhp\Component\ZCallTrait](Component-ZCallTrait.md)
