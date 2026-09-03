# DuckPhp\Ext\JsonView

## 简介

`JsonView` 是 `Core\View` 的扩展：把渲染输出改为 **JSON**。启用后 `Helper::Show($data, $view)`/`View::_Display()` 都会把（可选的）`$data` 经 `CoreHelper::ShowJson()` 输出为 JSON，适用于纯 API/JSON 返回的应用。

可配置 `json_view_skip_vars` 指定从数据中剔除的键（如框架自动注入的 `__view_data` 等）。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class JsonView extends DuckPhp\Core\View`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `json_view_skip_replace` | `false` | 为 `true` 时不替换全局 `View::_()`。 |
| `json_view_skip_vars` | `[]` | 输出前需要从 `$data` 中剔除的键列表。 |

## 使用方式

```php
\DuckPhp\Ext\JsonView::_()->init([
    'json_view_skip_vars' => ['__view_data'],
], $app);

// 之后 Controller 里：
Helper::ShowJson(['ok' => true]);      // 直接输出
Helper::Show($data, 'unused');         // 也会被本组件转成 JSON 输出
```

## 注意事项

- `_Show`/`_Display` 忽略 `$view` 参数（视图名不再有意义），只输出 JSON。
- `json_view_skip_vars` 在输出前逐个 `unset`，避免把不该暴露的内部数据带出去。

## 方法列表

### 公共方法

    public function __construct()
合并父类选项后构造。

    public function init(array $options, ?object $context = null)
初始化；默认把 `View::_()` 替换为本实例。

    public function _Show(array $data, string $view)
剔除 skip 键后把 `$data` 以 JSON 输出。

    public function _Display(string $view, ?array $data = null): void
剔除 skip 键后把 `$data` 以 JSON 输出。

## 相关链接

- [DuckPhp\Core\View](Core-View.md) — 父类
- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) — ShowJson 的实现方
