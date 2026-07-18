# DuckPhp\Foundation\ControllerTrait

简单控制器类 Trait。

## 简介

`DuckPhp\Foundation\ControllerTrait` 是控制器层类的简化组合 Trait。它在引入 `ZCallTrait` 的基础上，重写了 `_()` 静态方法，根据 `Route` 配置的控制器类后缀和控制器基类判断当前类是否为控制器，并据此返回实例或阶段容器对象；同时提供了 `_Z()` 阶段代理和 `OverrideParent()` 父类覆盖方法。

## 选项

无。

## 使用方式

### 在控制器类中使用

```php
use DuckPhp\Foundation\ControllerTrait;

class HomeController
{
    use ControllerTrait;

    public function index()
    {
        // ...
    }
}
```

### 单例/实例调用

```php
// 在控制器内部或外部调用
HomeController::_()->index();
```

### 阶段代理

```php
$proxy = HomeController::_Z($phase);
```

### 覆盖父控制器

```php
HomeController::OverrideParent();
```

## 注意事项

1. `_()` 的行为会根据 `Route` 的 `controller_class_postfix` 和 `controller_class_base` 配置动态判断当前类是否为控制器。
2. 如果是控制器，则创建无构造参数的新实例；否则从 `PhaseContainer` 获取对象。
3. `OverrideParent()` 会调用 `Route::replaceController($parent, static::class)`，用于控制器继承场景。

## 方法列表

### 公共方法

| 方法 | 说明 |
|---|---|
| `_($object = null)` | 获取当前类实例或阶段容器对象 |
| `_Z($phase = null)` | 创建阶段代理对象 |
| `OverrideParent()` | 用当前类覆盖父类路由映射 |

## 相关链接

- [DuckPhp\Component\ZCallTrait](Component-ZCallTrait.md)
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md)
- [DuckPhp\Core\Route](Core-Route.md)
- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md)
