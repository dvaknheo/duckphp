# Ext\StrictCheckServiceTrait

## 简介
这个 trait 用于你自己的 Service 的基类捆绑，和 strickcheck 合作

## 详解
这个 trait 替换了默认的 G 函数。
给默认 G 函数调用前加了严格检查

public static function G($object = null)
