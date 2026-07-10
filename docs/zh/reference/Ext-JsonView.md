# DuckPhp\Ext\JsonView

JSON 视图扩展组件。

## 简介

`JsonView` 继承自 `DuckPhp\Core\View`。当调用 `_Show()` 或 `_Display()` 时，它不会渲染模板，而是将数据以 JSON 格式输出。适合 API 接口或纯数据响应的场景。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `json_view_skip_replace` | `false` | 为 `true` 时，初始化后不把全局 `View` 单例替换为当前组件。 |
| `json_view_skip_vars` | `[]` | 输出 JSON 前需要从数据数组中移除的键名列表。 |

## 使用方式

### 作为全局视图组件加载

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\JsonView::class => true,
        ],
        'json_view_skip_vars' => ['internal_token'],
    ];
}
```

### 在 Controller 中返回 JSON

```php
$this->_Show(['status' => 1, 'data' => $list], 'api/response');
```

输出结果为：

```json
{"status": 1, "data": [...]}
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\JsonView::class => true,
        ],
        'json_view_skip_replace' => false,
        'json_view_skip_vars' => ['csrf_token'],
    ];
}
```

## 注意事项

1. 构造方法会合并父类 `View` 的选项。
2. 默认初始化后会把全局 `View` 单例替换为 `JsonView`，除非设置 `json_view_skip_replace => true`。
3. 输出 JSON 通过 `DuckPhp\Core\CoreHelper::ShowJson()` 完成。
4. `json_view_skip_vars` 中的键会在 JSON 输出前从数据数组中移除，避免泄露内部字段。

## 全部选项

```php
    public $options = [
        'json_view_skip_replace' => false,
        'json_view_skip_vars' => [],
    ];
```

## 方法列表

### 公共方法

    public function init(array $options, object $context = null)
初始化组件，默认将全局 `View` 单例替换为当前实例（除非 `json_view_skip_replace` 为 `true`）。

    public function _Show(array $data, string $view): void
输出数据的 JSON 表示。视图参数被忽略。

    public function _Display(string $view, ?array $data = null): void
输出数据的 JSON 表示。视图参数被忽略。

## 相关链接

- [DuckPhp\Core\View](Core-View.md)
- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md)
