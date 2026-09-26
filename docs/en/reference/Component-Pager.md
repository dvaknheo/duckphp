# DuckPhp\Component\Pager

A simple paginator: handles "which page / page size", generates per-page URLs, and outputs the HTML of a row of `page` links (the page window).

## Introduction

`Pager extends ComponentBase implements PagerInterface` centralizes the simplest pagination logic: page counting, URL composition, HTML rendering.

- current: by default the page number comes from GET `{page_key}` (default `page`), ≥1;
- page_size: items per page;
- `getPageCount(total)`=ceil;
- `getUrl(page)`: without a rewrite callback, built from `url` (may contain the `{page}` placeholder) or the requestUri; page 1 gets no page parameter;
- `render(total,options)`: empty when there is less than one page; otherwise renders indexes with window=`3` plus "1 … x … tail", generating inline HTML with classes (no external CSS by default; style classes such as `.page/.current/.page_blank` are there for you to style).

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class Pager extends ComponentBase implements PagerInterface`

## Options

`Pager::$options`:

| Option | Default | Description |
|---|---|---|
| `url` | null | Manually given pagination base URL (requestUri when empty). May contain a `{page}` placeholder to replace. |
| `current` | null | Manually set the current page; by default derived from GET {page_key}. |
| `page_size` | `30` | Items per page. |
| `page_key` | `'page'` | URL parameter name of the current page. |
| `rewrite` | null | (callable) custom URL-writing callback used as the getUrl branch; otherwise defaultGetUrl runs. |

## Usage

```php
use DuckPhp\Component\Pager;

Pager::_()->init([
    'page_size' => 12,
    'url' => '/list?cat=1&{page}',
]);
echo Pager::_()->render(230);   // 230 records, 12 per page → 20 pages

Pager::PageNo(3);                       // static get/set of current
Pager::PageWindow(20);                  // set page_size
Pager::PageHtml(500, ['page_size'=>20]); // static render
```

In controller/show, hand the total count to render.

## Notes

- current/pageSize are readable and writable; init normalizes and fills a default current.
- The HTML is lightweight self-rendered: to plug in your own CSS, target the class names (page/page_wraper/current/page_blank/page_spliter).
- `defaultGetUrl`: prefers replacing the `{page}` placeholder in url (cleared when page=1), otherwise composes path+query.

## Methods

### Public methods

    public static function PageNo($new_value = null)
Read/set the current page (→ `current`).

    public static function PageWindow($new_value = null)
Read/set page_size.

    public static function PageHtml($total, $options = [])
Render a block of pagination HTML (→ `render`).

    public function init(array $options, ?object $context = null)
Component initialization (takes options and context).

    public function current($new_value = null): int
Current page getter/setter (derived by default).

    public function pageSize($new_value = null): int
Items-per-page getter/setter.

    public function getPageCount(int $total): int
Total pages = ceil(total / page_size).

    public function getUrl($page): string
Single-page URL; the rewrite callback wins.

    public function defaultGetUrl(int $page): string
"url template/request + page parameter" composition; the page parameter is absent when page=1.

    public function render($total, $options = []): string
Combines the above to render the window HTML.

### Protected methods

    protected function getDefaultUrl(): string
Takes the requestUri as the default url (reads globals when no `__SUPERGLOBAL_CONTEXT`).

    protected function getDefaultPageNo(): int
Reads the page from `GET[page_key]` (default 1).


## Related links

- [DuckPhp\Component\PagerInterface](Component-PagerInterface.md)
