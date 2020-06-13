# DuckPhp\Ext\CallableView
[toc]

## 简介
EmptyView 扩展扩充了默认的 view 类，用于用函数替代文件方式显示视图
## 选项

继承 DuckPhp\Core\View 的所有选项，且有

    'empty_view_key_view'=> 'view', // _Show 的时候给的$data 的key
    'empty_view_key_skip_head_foot'=> 'skip_head_foot',  //_Show 的时候给的$data 的 key 标记跳过页眉页脚
    'empty_view_view_wellcome'=> 'Main/', // view 为这个的时候跳过显示
    'empty_view_trim_view_wellcome'=> true,     // 剪掉 view。 
    'empty_view_skip_replace'=> false,     //替换默认的 view


## 方法

继承 Core\\View 的所有方法。


## 详解

这个类 是用函数来代替默认的文件 View。 默认没打开。
用途是得到空数据，不用于显示。作用和演示效果见：
`template/full/traditional.php`