# DuckPhp\Foundation\SimpleSingletonTrait

简单单例 Trait。

## 简介

`DuckPhp\Foundation\SimpleSingletonTrait` 是最简单的单例组合 Trait，仅引入了 `DuckPhp\Core\SingletonTrait`，使类具备单例访问能力。

## 选项

无。

## 使用方式

### 在类中使用

```php
use DuckPhp\Foundation\SimpleSingletonTrait;

class MyService
{
    use SimpleSingletonTrait;

    public function doSomething()
    {
        // ...
    }
}
```

### 单例调用

```php
MyService::_()->doSomething();
```

## 注意事项

1. 该 Trait 本身没有定义方法，所有方法均来自 `SingletonTrait`。
2. 需要单例访问能力但不需要额外 Trait 能力的类可使用此 Trait。

## 方法列表

### 公共方法

来自引入的 Trait，无自有方法。

## 相关链接

- [DuckPhp\Core\SingletonTrait](Core-SingletonTrait.md)
