# DuckPhp\Foundation\ExceptionReporterTrait

异常报告器 Trait。

## 简介

`DuckPhp\Foundation\ExceptionReporterTrait` 提供了一套异常分发机制：根据当前应用命名空间，将抛出的异常转发到对应的 `on{ShortClassName}` 方法处理；若找不到对应的处理方法，则回退到默认异常处理。

## 选项

无。

## 使用方式

### 在异常处理类中使用

```php
use DuckPhp\Foundation\ExceptionReporterTrait;

class MyExceptionReporter
{
    use ExceptionReporterTrait;

    public function onMyException(\MyApp\Exception\MyException $ex)
    {
        // 处理 MyApp\Exception\MyException
    }
}
```

### 处理入口

```php
MyExceptionReporter::OnException($exception);
```

### 异常分发规则

1. 获取异常对象的完整类名。
2. 如果异常不在当前应用的命名空间下，直接调用默认异常处理。
3. 否则，取类名短名（不含命名空间），并尝试调用 `on{ShortClassName}` 方法。
4. 如果对应方法不存在，则回退到默认异常处理。

## 注意事项

1. 该 Trait 使用 `DuckPhp\Core\SingletonTrait`，内部以单例形式工作。
2. 分发逻辑依赖 `App::Current()->options['namespace']`，需确保应用已初始化。
3. 默认异常处理会调用 `App::Current()->_OnDefaultException($ex)`。

## 方法列表

### 公共方法

| 方法 | 说明 |
|---|---|
| `OnException($ex)` | 异常入口，按规则分发到对应的处理方法或默认处理 |
| `defaultException($ex)` | 默认异常处理，调用 `defaultSystemException` |

### 受保护方法

| 方法 | 说明 |
|---|---|
| `defaultSystemException($ex)` | 调用系统默认异常处理 |

## 相关链接

- [DuckPhp\Core\SingletonTrait](Core-SingletonTrait.md)
- [DuckPhp\Core\App](Core-App.md)
