# DuckPhp\Component\PagerInterface

分页器契约接口：规定一个分页组件至少要能为“当前页 / 每页量 / 渲染”提供一致 API。

## 简介

`PagerInterface` 定义实现分页器所需的最小接口：能读/写当前页、每页数量、并在给定总条数时给出（默认是 HTML 字符串的）render 返回。

框架自带 `DuckPhp\Component\Pager` 实现了该接口（`class Pager extends ComponentBase implements PagerInterface`）。使用分页相关业务多基于 Pager 实例；基于接口可以让替换实现不变更上层。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`interface PagerInterface`

## 使用方式

```php
use DuckPhp\Component\PagerInterface;

function renderList(PagerInterface $pager, int $total) {
    return $pager->render($total, []);
}
```

定制实现示例 → 继承框架 `Pager` 并覆盖渲染细节即可仍满足接口。

## 方法列表

### 接口方法

    public function current($new_value = null): int
（读）返回当前页号；（写）设当前页后返回。

    public function pageSize($new_value = null): int
（读）每页条数；传值设置并返回。

    public function render($total, $options = []): string
给定总条数（与每页量一起推算总页）渲染分页结果为字符串。

## 相关链接

- [DuckPhp\Component\Pager](Component-Pager.md) —— 标准实现
