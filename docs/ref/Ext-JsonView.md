# DuckPhp\Ext\CallableView
[toc]

## 简介
JsonView 扩展扩充了默认的 view 类，用于用函数替代文件方式显示视图
## 选项

    
继承 [DuckPhp\Core\View](Core-View.md) 的所有选项。

        'json_view_skip_replace' => false,
跳过替换默认的View
## 方法

继承 DuckPhp\Core\View 的所有方法。


## 详解

这个类 是用函数来代替默认的文件 View。 默认没打开

这个类是输出忽略视图选项，输出 json 。