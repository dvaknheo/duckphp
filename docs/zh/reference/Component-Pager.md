# DuckPhp\Component\Pager

简单分页器：负责“第几页/每页量”、生成每页 URL，并输出一串 `page` 链接的 HTML（page window 窗口）。

## 简介

`Pager extends ComponentBase implements PagerInterface` 把最简分页逻辑集中：页面计数、URL 组合、HTML 渲染。

- current：默认自 GET `{page_key}`（默认 `page`）得到第几页，≥1；
- page_size 每页数量；
- `getPageCount(total)`=ceil；
- `getUrl(page)`：无 rewrite 回调则按 `url`(可含占位 `{page}`) 或 requestUri 拼出；第一页不置 page 参数；
- `render(total,options)`：不足一页空；否则给出 window=`3` 的下标 + “1 … x … tail”，生成带 class 的内联 HTML（默认无外部 CSS，样式类如 `.page/.current/.page_blank` 供你做样式）。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class Pager extends ComponentBase implements PagerInterface`

## 选项

`Pager::$options`：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `url` | null | 手工给分页基底 URL（空则 requestUri）。可含 `{page}` 占位符替换。 |
| `current` | null | 手动指定当前页；缺省由 GET {page_key} 得。 |
| `page_size` | `30` | 每页条数。 |
| `page_key` | `'page'` | 当前页 URL 参数名。 |
| `rewrite` | null | （callable）自定义写 URL 的回调当作 getUrl 分支；否则走 defaultGetUrl。 |

## 使用方式

```php
use DuckPhp\Component\Pager;

Pager::_()->init([
    'page_size' => 12,
    'url' => '/list?cat=1&{page}',
])-> render? //可用：
echo Pager::_()->render(230);   // 230 条记录 → 总页 20 的自盒

Pager::PageNo(3);                       // 静态设/取 current
Pager::PageWindow(20);                  // 设 page_size
Pager::PageHtml(500, ['page_size'=>20]); //静态渲染
```

在 controller/show 里拿总条数交给 render 即可。

## 说明

- current/pageSize 可读可设；init 会归 one 补默认 current。
- HTML 是轻量自渲染：想接入自己 CSS 请基于 class 名（page/page_wraper/current/page_blank/page_spliter）。
- `defaultGetUrl`：优先用 url 里的 `{page}` 占位替换（page=1 清空），否则组合 path+query。

## 方法列表

### 公共静态方法

    static PageNo($new_value = null)
读/设当前页（→current）。

    static PageWindow($new_value = null)
读/设 page_size。

    static PageHtml($total, $options = [])
渲染一段分页 HTML（→render）。

### 公共实例方法

    public function current($new_value = null): int
当前页 getter/setter（缺省推算）。

    public function pageSize($new_value = null): int
每页数量 getter/setter。

    public function getPageCount(int $total): int
总页＝ceil(total/page_size)。

    public function getUrl($page): string
单页 URL；rewrite 回调优先。

    public function defaultGetUrl(int $page): string
“url 模板/请求 + page param”组装，page=1 不出现页参数。

    public function render($total, $options = []): string
结合上述渲染 window HTML。

### 受保护方法

    protected function getDefaultUrl(): string
取 requestUri 作为默认 url（无 __SUPERGLOBAL_CONTEXT 时全局）。

    protected function getDefaultPageNo(): int
从 GET[page_key] 取页（缺 1）。

## 相关链接

- [DuckPhp\Component\PagerInterface](Component-PagerInterface.md)
