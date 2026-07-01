# DuckPhp\Component\PagerInterface

## 简介
分页类的接口

## 详解

    public function current($new_value = null) : int;
对应 C::PageNo ，获得当前页码

    public function pageSize($new_value = null) : int;
如果$new_value 不为空，设置当前分页大小，否则获得当前分页大小

    public function render($total, $options = []) : string;
渲染页面， $total 是条目数字，后者是配置。
