# DuckPhp\Component\PagerInterface

分页组件接口。

## 简介

`PagerInterface` 定义了分页组件需要实现的最小方法集合。自定义分页组件可以实现此接口以替换默认的 `DuckPhp\Component\Pager`。

## 接口定义

```php
namespace DuckPhp\Component;

interface PagerInterface
{
    public function current($new_value = null) : int;
    public function pageSize($new_value = null) : int;
    public function render($total, $options = []) : string;
}
```

## 方法说明

    public function current($new_value = null) : int;
获取或设置当前页码

    public function pageSize($new_value = null) : int;
获取或设置每页条数

    public function render($total, $options = []) : string;
根据总记录数渲染分页 HTML

## 自定义分页组件

实现 `PagerInterface` 接口后，可以通过 `pager` 相关配置或替换组件的方式接入：

```php
namespace App\Component;

use DuckPhp\Component\PagerInterface;

class MyPager implements PagerInterface
{
    public function current($new_value = null) : int;
    {
        return $new_value ?? 1;
    }

    public function pageSize($new_value = null) : int;
    {
        return $new_value ?? 20;
    }

    public function render($total, $options = []) : string;
    {
        // 自定义分页 HTML 渲染
        return '';
    }
}
```

## 相关链接

- [DuckPhp\Component\Pager](Component-Pager.md)
