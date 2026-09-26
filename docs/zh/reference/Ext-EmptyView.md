# DuckPhp\Ext\EmptyView

## 简介

`EmptyView` 是 `Core\View` 的扩展：它**不渲染模板文件**，而是把“要渲染的视图信息”装配进数据后交给外层处理——适合把渲染工作委托给其它模板引擎、前端框架或自研输出器的场景。

行为：
- `_Show($data, $view)`：合并数据后把 `$view` 写入 `$data['view']`（键名由 `empty_view_key_view` 指定），并附带 `view_header`/`view_footer`（父类 `$header_file`/`$footer_file` 解析出的模板文件全路径）。若开启 `empty_view_trim_view_wellcome`，视图名以 `Main/`（`empty_view_key_wellcome_class`）开头时去掉该前缀。
- `_Display($view, $data)`：只把 `$data['view']` 设为视图文件全路径。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class EmptyView extends DuckPhp\Core\View`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `empty_view_key_view` | `'view'` | 装配进数据的“视图键名”。 |
| `empty_view_key_wellcome_class` | `'Main/'` | 欢迎视图前缀（配合 trim 用）。 |
| `empty_view_trim_view_wellcome` | `true` | 是否去掉视图名的欢迎前缀。 |
| `empty_view_skip_replace` | `false` | 为 `true` 时不替换全局 `View::_()`。 |

## 使用方式

```php
\DuckPhp\Ext\EmptyView::_()->init([], $app);

// 之后 Helper::Show($data, 'user/list') 不渲染文件，
// 而是把 $data['view']='user/list'（及 view_header/view_footer 路径）留给外层处理。
```

## 注意事项

- `_Show` 与 `_Display` 都不输出任何 HTML；真正输出由使用方依据 `$data` 完成。
- `view_header`/`view_footer` 存的是 `getViewFile()` 解析后的全路径，不是内容。

## 方法列表

### 公共方法

    public function __construct()
合并父类选项后构造。

    public function init(array $options, ?object $context = null)
初始化；默认把 `View::_()` 替换为本实例。

    public function _Show(array $data, string $view)
装配视图信息（view/view_header/view_footer）进数据，不渲染文件。

    public function _Display(string $view, ?array $data = null): void
只设置 `$data['view']` 为视图文件全路径。

## 相关链接

- [DuckPhp\Core\View](Core-View.md) — 父类
- [DuckPhp\Ext\CallableView](Ext-CallableView.md) — 用回调代替模板的扩展
- [DuckPhp\Ext\JsonView](Ext-JsonView.md) — 输出 JSON 的扩展
