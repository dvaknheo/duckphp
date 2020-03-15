# Ext\Pager

## 简介
分页类的接口

## 公开方法


## 详解

    public function current() : int;
    public function pageSize($new_value = null) : int;
    public function render($total, $options = []) : string;