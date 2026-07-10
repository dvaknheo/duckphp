# DuckPhp\Ext\StrictCheck

> ⚠️ 警告：该扩展是实验性的或已废弃，不建议在新项目中使用。

## 简介

`StrictCheck` 是一个用于严格检查分层调用关系的组件。它试图在 debug 模式下限制 Controller、Business、Model 等层之间的调用规则，防止跨层调用。但该组件当前 `initOptions` 直接抛出异常，明确标记为不工作，不建议在新项目中使用。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 应用命名空间前缀。 |
| `namespace_controller` | `'Controller'` | 控制器层命名空间。 |
| `namespace_business` | `''` | 业务层命名空间。 |
| `namespace_model` | `''` | 模型层命名空间。 |
| `controller_base_class` | `null` | 控制器基类。 |
| `is_debug` | `false` | 是否开启严格检查。 |
| `strict_check_context_class` | `null` | 严格检查上下文类。 |
| `strict_check_enable` | `true` | 是否启用严格检查。 |
| `postfix_batch_business` | `'BatchBusiness'` | 批量业务类后缀。 |
| `postfix_business_lib` | `'Lib'` | 业务库类后缀。 |
| `postfix_ex_model` | `'ExModel'` | 扩展模型类后缀。 |
| `postfix_model` | `'Model'` | 模型类后缀。 |

## 使用方式

该组件当前无法正常工作，以下用法仅供参考：

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            DuckPhp\Ext\StrictCheck::class => true,
        ],
        'is_debug' => true,
        'namespace' => 'App',
        'namespace_controller' => 'App\\Controller',
        'namespace_business' => 'App\\Business',
        'namespace_model' => 'App\\Model',
    ];
}
```

## 配置示例

无。该组件当前初始化时会抛出异常：

```php
throw new \Exception("It's not work , TODO fix me to work in new version.");
```

## 注意事项

1. 该组件在 `initOptions()` 中直接抛出异常，无法初始化使用。
2. 设计目标是限制 Controller 直接调用 Db/Model，Business 之间互相调用，Model 只能被 Business 或 ExModel 调用等。
3. `checkStrictComponent()` 用于检查组件（如 Db）是否被非法调用。
4. `check_strict_class()` 用于检查类调用是否符合分层规则。
5. 源码中包含大量 TODO 和 `codeCoverageIgnore` 标记，表明实现未完善。

## 全部选项

        'namespace' => '',
        'namespace_controller' => 'Controller',
        'namespace_business' => '',
        'namespace_model' => '',
        'controller_base_class' => null,
        'is_debug' => false,
        'strict_check_context_class' => null,
        'strict_check_enable' => true,
        'postfix_batch_business' => 'BatchBusiness',
        'postfix_business_lib' => 'Lib',
        'postfix_ex_model' => 'ExModel',
        'postfix_model' => 'Model',

## 方法列表

### 公共方法

    public static function CheckStrictDb()
检查数据库调用的调用者是否合法。返回检查结果。

    public function getCallerByLevel($level, $parent_classes_to_skip = [])
从调用栈中获取指定层级的调用者类名，可跳过指定父类。

    public function checkEnv(): bool
检查当前环境是否满足严格检查条件（要求 `is_debug` 为 `true`）。

    public function checkStrictComponent($component_name, $trace_level, $parent_classes_to_skip = [])
检查指定组件是否被允许调用。如果不允许，抛出 `ErrorException`。

    public function check_strict_class(string $class): void
检查指定类的调用是否符合分层规则。如果不符合，抛出 `ErrorException`。

### 受保护方法

    protected function initOptions(array $options): void
初始化选项。当前直接抛出异常，标记组件不可用。

    protected function initContext(object $context): void
初始化上下文，尝试注册数据库严格检查钩子。

    protected function hit_class(string $caller_class, array $parent_classes_to_skip): bool
判断调用者类是否属于需要跳过的父类。

    protected static function StartWith($str, $prefix)
字符串前缀匹配工具方法。

    protected static function EndWith($str, $postfix)
字符串后缀匹配工具方法。

## 相关链接

- [DuckPhp\Component\DbManager](Component-DbManager.md)
