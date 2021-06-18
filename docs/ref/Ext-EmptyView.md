# DuckPhp\Ext\EmptyView
[toc]

## 简介
EmptyView 组件 扩展扩充了默认的 View 类，用于用函数替代文件方式显示视图
## 选项

继承 [DuckPhp\Core\View](Core-View.md) 的所有选项，且有

        'empty_view_key_view' => 'view',
空视图扩展，_Show 的时候给的 $data 的key

        'empty_view_key_skip_head_foot' => 'skip_head_foot',
空视图扩展，_Show 的时候给的$data 的 key 标记跳过页眉页脚

        'empty_view_key_wellcome_class' => 'Main/',
空视图扩展，view 为这个的时候跳过显示

        'empty_view_trim_view_wellcome' => true,
空视图扩展，剪掉 view。 

        'empty_view_skip_replace' => false,
空视图扩展，替换默认的 view

### DuckPhp\Core\View的选项

        'path' => '',
路径

        'path_view' => 'view',
视图路径

        'path_view_override' => '',
用于覆盖的路径——用于插件模式

        'skip_view_notice_error' => true,
关闭 notice 警告，以避免麻烦的处理。


## 方法

继承 [DuckPhp\Core\View](Core-View.md) 的所有方法。没有额外的方法。

    public function __construct()
    
    public function init(array $options, object $context = null)
    
    public function _Show(array $data, string $view): void
    
    public function _Display(string $view, ?array $data = null): void
    
    protected function getViewFile(?string $view): string

## 详解

这个类 是用函数来代替默认的文件 View。 默认没打开。

用途是得到空数据，不用于显示。作用和演示效果见：

`template/full/traditional.php`

_Show 函数的时候，会把 $view 变量加进来

_Display 函数的时候，会把 $skip_head_foot 变量加进来。

empty_view_skip_replace  = true 则不替换默认的 View 类。


'empty_view_view_wellcome'=> 'Main/', // view 为这个的时候跳过显示
'empty_view_trim_view_wellcome'=> true,     // 剪掉 view。 

这两项用于 Main/index 之类的时候把 $view 前缀去掉。    





