# DuckPhp\Ext\StrictCheckObjectTrait

## 简介

这个 trait 用于你自己的 Business 或 Model 的基类捆绑，和 StrickCheck 类合作。

替换默认的  SingletonEx 的 G 方法。
## 方法

public static function G($object = null)

    这个 trait 替换了默认的 G 函数。
    给默认 G 函数调用前加了严格检查