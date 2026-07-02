# DuckPhp\Component\Pager

分页组件。

## 简介

`Pager` 组件用于生成分页 HTML，支持从 URL 参数自动识别当前页码，并支持自定义 URL 生成规则。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。 //TODO 并没有

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `url` | `null` | 基础 URL。为空时使用当前请求 URL。 |
| `current` | `null` | 当前页码。为空时从 URL 参数自动获取。 |
| `page_size` | `30` | 每页记录数。 |
| `page_key` | `'page'` | URL 参数名。 |
| `rewrite` | `null` | URL 重写回调。为空时使用默认 URL 生成逻辑。 |

## 使用方式

### 全局函数
//TODO 没有全局函数
```php
$pageNo = __page_no();      // 当前页码
$pageSize = __page_size();  // 每页条数
$html = __page_html($total); // 生成分页 HTML
```

### 在 Controller 中使用

```php
use DuckPhp\Foundation\Controller\Helper;

class PostController
{
    public function index()
    {
        $pageNo = Helper::PageNo();
        $pageSize = Helper::PageSize();
        $total = PostModel::getTotal();
        $posts = PostModel::getList($pageNo, $pageSize);
        $pagerHtml = Helper::PageHtml($total);
        
        Helper::Show(get_defined_vars(), ['pager' => $pagerHtml]);
    }
}
```

### 直接调用组件

```php
use DuckPhp\Component\Pager;

$pager = Pager::_();
$pageNo = $pager->current();
$pageSize = $pager->pageSize();
$html = $pager->render($total);
```

## URL 生成规则

### 默认 URL 生成

默认从 `$_SERVER['REQUEST_URI']` 解析，并在 query string 中附加 `page` 参数：

- 第 1 页：`/post`
- 第 2 页：`/post?page=2`

### 使用占位符

如果 `url` 选项包含 `{page}` 占位符，则直接替换：

```php
class App extends DuckPhp
{
    public $options = [
        'pager' => [
            'url' => '/post/list/{page}',
        ],
    ];
}
```

生成结果：

- 第 1 页：`/post/list/`
- 第 2 页：`/post/list/2`

### 自定义重写函数

```php
class App extends DuckPhp
{
    public $options = [
        'pager' => [
            'rewrite' => function ($page) {
                return '/post/page/' . $page;
            },
        ],
    ];
}
```

## 注意事项

1. 当前页码会自动校正为大于等于 1 的整数。
2. 总页数小于等于 1 时，`render()` 返回空字符串。
3. 分页窗口默认显示 3 页，超过范围时会显示省略号。
4. 第 1 页的 `page` 参数会自动省略。

## 全部选项

```php
public $options = [
    'url' => null,
    'current' => null,
    'page_size' => 30,
    'page_key' => 'page',
    'rewrite' => null,
];
```

## 方法列表

### 公共方法

    public static function PageNo($new_value = null)
获取或设置当前页码

    public static function PageWindow($new_value = null)
获取或设置每页条数

    public static function PageHtml($total, $options = [])
渲染分页 HTML

    public function init(array $options, object $context = null)
初始化组件，设置当前页码

    public function current($new_value = null): int
获取或设置当前页码

    public function pageSize($new_value = null): int
获取或设置每页条数

    public function getPageCount($total): int
根据总数计算总页数

    public function getUrl($page)
获取指定页的 URL

    public function defaultGetUrl($page)
默认 URL 生成逻辑

    public function render($total, $options = []): string
渲染分页 HTML

### 受保护方法

    protected function getDefaultUrl()
从 `$_SERVER['REQUEST_URI']` 获取默认 URL

    protected function getDefaultPageNo()
从 `$_GET[$page_key]` 获取默认页码

## 相关链接

- [DuckPhp\Component\PagerInterface](Component-PagerInterface.md)
- [DuckPhp\Foundation\Controller\Helper](Foundation-Controller-Helper.md)
