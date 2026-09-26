# DuckPhp\Ext\CallableView

## 简介

`CallableView` 是 `Core\View` 的扩展：把「视图」从“模板文件”替换为「可调用对象/函数」。启用后 `_Show($data, $view)` 会把 `$view` 按 `callable_view_prefix` 加工成回调名（如 `view_index`），再从 `callable_view_class` 指定的类（支持对象/类名/`_()` 单例）解析出可调用体，按 head → 主体 → foot 顺序调用。

用途：不写模板文件、用 PHP 方法（或闭包）直接输出的场景；`DuckPhpAllInOne` 的内部 `_Show` 也采用类似思路。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class CallableView extends DuckPhp\Core\View`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `callable_view_header` | `null` | 页眉视图回调名（缺省用父类 `View::$header_file`）。 |
| `callable_view_footer` | `null` | 页脚视图回调名（缺省用父类 `View::$footer_file`）。 |
| `callable_view_class` | `null` | 提供视图回调的类（可为类名字符串或对象）。 |
| `callable_view_is_object_call` | `true` | 类名为字符串且可 `_()` 时转为单例实例，否则 `new`。 |
| `callable_view_prefix` | `null` | 视图名加工前缀（如 `view_`，与 `/`→`_` 替换）。 |
| `callable_view_skip_replace` | `false` | 为 `true` 时不把自身替换成全局 `View` 单例。 |

## 使用方式

```php
$options = [
    'callable_view_class' => MyView::class,   // MyView 提供 view_index() 等方法
    'callable_view_prefix' => 'view_',
];
// 启用（缺省会把 View::_() 指向本实例）：
\DuckPhp\Ext\CallableView::_()->init($options, $app);
```

```php
class MyView
{
    public function view_index($data)
    {
        echo 'Index: ' . ($data['title'] ?? '');
    }
}
```

## 注意事项

- `viewToCallback()`：把视图名中的 `/` 换成 `_` 并加前缀；有 `callable_view_class` 时组装 `[$obj, $func]`；不可调用返回 `null`（此时回退父类 `_Show` 走模板）。
- `_Show`：header/footer 也按回调解析并先/后调用；`_Display` 同理（无页眉页脚）。
- 通过 `init()` 默认替换全局 `View::_()`（除非 `callable_view_skip_replace`），框架内 `Helper::Show()` 等即走向本实现。

## 方法列表

### 公共方法

    public function __construct()
合并父类 `View` 的默认选项后构造。

    public function init(array $options, ?object $context = null)
初始化（父类流程）；默认把 `View::_()` 替换为本实例。

    public function _Show(array $data, string $view)
把视图解析成回调并带页眉页脚调用；不可调用则回退父类模板渲染。

    public function _Display(string $view, ?array $data = null): void
把视图解析成回调直接调用；不可调用回退父类。

### 受保护方法

    protected function viewToCallback(?string $func)
把视图名加工成可调用体（前缀/`/`→`_`/类实例化）；不可调用返回 `null`。

## 相关链接

- [DuckPhp\Core\View](Core-View.md) — 父类
- [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) / [DuckPhp\Ext\JsonView](Ext-JsonView.md) — 其它视图扩展
