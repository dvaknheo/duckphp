# DuckPhp\Ext\CallableView

可调用视图（Callable View）扩展组件。

## 简介

`CallableView` 继承自 `DuckPhp\Core\View`，它允许把视图名称映射为可调用的函数或类方法。在渲染视图时，组件会先把视图名转换为对应的 `callable`；如果找不到可调用项，则回退到父类 `View` 的常规模板渲染流程。

该组件常用于需要在视图层使用纯 PHP 函数或 Helper 类来渲染片段的场景。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `callable_view_head` | `null` | 头部回调名称。如果未设置，则使用 `head_file` 对应的视图名。 |
| `callable_view_foot` | `null` | 底部回调名称。如果未设置，则使用 `foot_file` 对应的视图名。 |
| `callable_view_class` | `null` | 承载回调方法的类名。为空时，回调被视为全局函数。 |
| `callable_view_is_object_call` | `true` | 当 `callable_view_class` 有效时，是否以对象方式调用方法。为 `true` 时优先使用 `Class::_()`，否则直接使用类名字符串。 |
| `callable_view_prefix` | `null` | 回调方法名前缀。视图名会先拼接此前缀。 |
| `callable_view_skip_replace` | `false` | 为 `true` 时，初始化后不把全局 `View` 单例替换为当前组件。 |

## 使用方式

### 作为全局视图组件加载

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\CallableView::class => true,
        ],
        'callable_view_class' => \MyApp\ViewHelper::class,
        'callable_view_prefix' => 'render_',
        'callable_view_head' => 'header',
        'callable_view_foot' => 'footer',
    ];
}
```

在 `MyApp\ViewHelper` 中定义对应的方法：

```php
class ViewHelper
{
    public static function render_header(array $data)
    {
        // 渲染头部
    }
    public static function render_footer(array $data)
    {
        // 渲染底部
    }
    public static function render_user_profile(array $data)
    {
        // 渲染 user/profile 视图
    }
}
```

### 在 Controller 中渲染

```php
$this->_Show($data, 'user/profile');
```

如果 `user/profile` 找不到对应的回调，组件会回退到父类 `View` 的模板渲染。

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\CallableView::class => true,
        ],
        'callable_view_class' => \MyApp\ViewHelper::class,
        'callable_view_prefix' => 'render_',
        'callable_view_skip_replace' => false,
    ];
}
```

## 注意事项

1. 构造方法会合并父类 `View` 的选项，因此 `View` 原有选项仍然可用。
2. 默认情况下，初始化后会把全局 `View` 单例替换为 `CallableView`，除非设置 `callable_view_skip_replace => true`。
3. 视图名中的 `/` 会被替换为 `_`，再拼接前缀作为回调方法名。
4. 如果配置了 `callable_view_class`，组件会尝试按对象方法 `[实例, 方法名]` 或静态方法 `[类名, 方法名]` 进行调用。

## 全部选项

```php
    public $options = [
        'callable_view_head' => null,
        'callable_view_foot' => null,
        'callable_view_class' => null,
        'callable_view_is_object_call' => true,
        'callable_view_prefix' => null,
        'callable_view_skip_replace' => false,
    ];
```

## 方法列表

### 公共方法

    public function init(array $options, object $context = null)
初始化组件，默认将全局 `View` 单例替换为当前实例（除非 `callable_view_skip_replace` 为 `true`）。

    public function _Show(array $data, string $view): void
渲染视图。先尝试按回调方式渲染头部、视图、底部；如果找不到回调，则回退到父类模板渲染。

    public function _Display(string $view, ?array $data = null): void
直接显示视图。如果找到对应回调则执行；否则回退到父类实现。

### 受保护方法

    protected function viewToCallback(?string $func)
将视图名（含前缀、类名等）转换为可调用回调。如果不可调用则返回 `null`。

## 相关链接

- [DuckPhp\Core\View](Core-View.md)
