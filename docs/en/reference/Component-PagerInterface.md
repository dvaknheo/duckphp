# DuckPhp\Component\PagerInterface

Pager contract interface: specifies that a pager component must at least expose a consistent API for "current page / page size / rendering".

## Introduction

`PagerInterface` defines the minimal interface a pager implementation needs: read/write the current page and the page size, and render (an HTML string by default) given a total record count.

The framework ships `DuckPhp\Component\Pager` implementing this interface (`class Pager extends ComponentBase implements PagerInterface`). Paging-related business code mostly works on Pager instances; coding against the interface lets you swap the implementation without changing the upper layers.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `interface PagerInterface`

## Usage

```php
use DuckPhp\Component\PagerInterface;

function renderList(PagerInterface $pager, int $total) {
    return $pager->render($total, []);
}
```

Custom implementation example → extend the framework `Pager` and override the rendering details; it still satisfies the interface.

## Methods

### Interface methods

    public function current($new_value = null): int
(Read) returns the current page number; (write) sets the current page and returns it.

    public function pageSize($new_value = null): int
(Read) records per page; pass a value to set it and get it back.

    public function render($total, $options = []): string
Render the paging result as a string, given the total record count (total pages are derived together with the page size).

## Related links

- [DuckPhp\Component\Pager](Component-Pager.md) — the standard implementation
