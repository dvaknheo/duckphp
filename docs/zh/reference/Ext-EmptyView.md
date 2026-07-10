# DuckPhp\Ext\EmptyView

空视图扩展组件。

## 简介

`EmptyView` 继承自 `DuckPhp\Core\View`。它不会渲染真正的模板文件，而是把视图名和头尾文件信息存入数据数组中，方便由调用方自行处理或输出。该组件常用于需要完全由前端或上层逻辑决定输出的场景。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `empty_view_key_view` | `'view'` | 存储当前视图名的数据键名。 |
| `empty_view_key_wellcome_class` | `'Main/'` | 默认入口类前缀。如果视图名以此前缀开头，可选择去除。 |
| `empty_view_trim_view_wellcome` | `true` | 为 `true` 时，自动去除视图名开头的 `empty_view_key_wellcome_class` 前缀。 |
| `empty_view_skip_replace` | `false` | 为 `true` 时，初始化后不把全局 `View` 单例替换为当前组件。 |

## 使用方式

### 作为全局视图组件加载

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\EmptyView::class => true,
        ],
        'empty_view_key_view' => 'view',
        'empty_view_key_wellcome_class' => 'Main/',
    ];
}
```

### 在 Controller 中赋值

```php
$this->_Show(['title' => 'Hello'], 'Main/index');
```

调用后数据数组中会包含：

```php
[
    'title' => 'Hello',
    'view' => 'index',
    'view_head' => '...', // 头文件路径
    'view_foot' => '...', // 尾文件路径
]
```

上层代码可以读取 `view` 键自行渲染。

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\EmptyView::class => true,
        ],
        'empty_view_key_view' => 'page',
        'empty_view_trim_view_wellcome' => true,
        'empty_view_skip_replace' => false,
    ];
}
```

## 注意事项

1. 构造方法会合并父类 `View` 的选项，因此 `View` 原有选项仍然可用。
2. 默认初始化后会把全局 `View` 单例替换为 `EmptyView`，除非设置 `empty_view_skip_replace => true`。
3. 视图名仅做字符串处理，不会真正读取模板文件内容。
4. 头尾文件通过 `getViewFile()` 获取路径，同样不会读取文件内容。

## 全部选项

```php
    public $options = [
        'empty_view_key_view' => 'view',
        'empty_view_key_wellcome_class' => 'Main/',
        'empty_view_trim_view_wellcome' => true,
        'empty_view_skip_replace' => false,
    ];
```

## 方法列表

### 公共方法

    public function init(array $options, object $context = null)
初始化组件，默认将全局 `View` 单例替换为当前实例（除非 `empty_view_skip_replace` 为 `true`）。

    public function _Show(array $data, string $view): void
将数据合并到视图对象，并把视图名、头文件、尾文件分别存入数据数组。

    public function _Display(string $view, ?array $data = null): void
设置视图数据并记录当前视图文件路径。

## 相关链接

- [DuckPhp\Core\View](Core-View.md)
