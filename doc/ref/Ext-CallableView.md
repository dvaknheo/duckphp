# Ext\CallableView

## 简介
CallableView 扩展扩充了默认的 view 类，用于用函数替代文件方式显示视图
## 选项

继承 DuckPhp\Core\View 的所有选项，且有

'callable_view_head' => null,
   //  页眉函数

'callable_view_foot' => null,

    页脚
'callable_view_class' => null,

    现定于此类内 callable_view_class 可以为 object;如果 callable_view_class 为 null 则为全局函数
'callable_view_prefix' => null,

    视图方法前缀 callable_view_prefix 是方法前缀。 方法名都会把view 的 / 替换成 _ 下划线
'callable_view_skip_replace' => false,

    替换默认view; callable_view_skip_replace 打开的时候会在 初始化的时候替换默认的 Core\View

## 方法

    继承 Core\\View 的所有方法。
    protected function viewToCallback($func)

## 详解

这个类 是用函数来代替默认的文件 View。 默认没打开
