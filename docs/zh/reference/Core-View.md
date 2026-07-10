# DuckPhp\Core\View

视图组件。

## 简介

`View` 提供基于 PHP 文件的视图渲染能力。支持头部/尾部布局、视图数据赋值、返回渲染字符串或输出，并能在渲染时临时忽略 `E_NOTICE` 错误。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径。 |
| `path_view` | `'view'` | 视图文件目录，相对于 `path`。 |
| `view_skip_notice_error` | `true` | 渲染视图时是否忽略 `E_NOTICE` 错误。 |

## 使用方式

### 显示视图

```php
use DuckPhp\Core\View;

View::Show([
    'title' => '首页',
    'user' => $user,
], 'index');
```

### 显示视图片段

```php
use DuckPhp\Core\View;

View::Display('partials/header', ['title' => '标题']);
```

### 获取渲染结果

```php
use DuckPhp\Core\View;

$html = View::Render('emails/welcome', ['user' => $user]);
```

### 全局函数

```php
__display('partials/header', ['title' => '标题']);
```

### 设置布局

```php
use DuckPhp\Core\View;

View::_()->setViewHeadFoot('header', 'footer');
View::Show(['title' => '带布局的页面'], 'content');
```

### 赋值视图数据

```php
use DuckPhp\Core\View;

View::_()->assignViewData('title', '页面标题');
View::_()->assignViewData([
    'user' => $user,
    'items' => $items,
]);
```

## 视图文件

视图文件默认位于 `view/` 目录，扩展名为 `.php`。例如调用 `View::Show([], 'user/profile')` 会加载 `view/user/profile.php`。

视图模板示例：

```php
<!-- view/index.php -->
<h1><?= __h($title); ?></h1>
<p>Hello, <?= __h($user['name']); ?></p>
```

## 配置示例

### 基础配置

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'path_view' => 'view',
        'view_skip_notice_error' => true,
    ];
}
```

### 自定义视图目录

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'path' => __DIR__,
        'path_view' => 'templates',
    ];
}
```

## 注意事项

1. 视图文件中的变量会通过 `extract()` 展开，渲染前传入的数组键名可直接作为变量使用。
2. 如果开启 `view_skip_notice_error`，渲染期间会临时关闭 `E_NOTICE` 报告，渲染结束后会恢复。
3. 使用 `View::Show()` 时，如果设置了 `head_file` 和 `foot_file`，会自动包含头部和尾部文件。
4. 调用 `reset()` 可以清空视图数据和布局设置。

## 全部选项

```php
    'path' => '',
    'path_view' => 'view',
    'view_skip_notice_error' => true,
```

## 方法列表

### 公共方法

    public static function Show(array $data = [], string $view = null): void
渲染并输出视图，支持头部和尾部布局

    public static function Display(string $view, ?array $data = null): void
渲染并输出单个视图文件，不包含布局

    public static function Render(string $view, ?array $data = null): string
渲染视图并返回字符串

    public function _Show(array $data, string $view): void
`Show` 的内部实现

    public function _Display(string $view, ?array $data = null): void
`Display` 的内部实现

    public function _Render(string $view, ?array $data = null): string
`Render` 的内部实现

    public function reset()
重置视图数据、布局文件和错误报告状态

    public function getViewData(): array
获取当前视图数据

    public function setViewHeadFoot(?string $head_file, ?string $foot_file): void
设置头部和尾部布局文件

    public function assignViewData($key, $value = null): void
赋值视图数据，支持数组批量赋值

### 受保护方法

    protected function getViewFile(?string $view): string
根据视图名获取完整视图文件路径

## 相关链接

- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md)
- [DuckPhp\Core\App](Core-App.md)
