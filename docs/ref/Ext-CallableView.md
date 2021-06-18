# DuckPhp\Ext\CallableView
[toc]

## 简介
CallableView 扩展扩充了默认的 view 类，用于用函数替代文件方式显示视图
## 选项

继承 [DuckPhp\Core\View](Core-View.md) 的所有选项，且有

        'callable_view_head' => null,
CallableView 页眉函数

        'callable_view_foot' => null,
CallableView 页脚函数

        'callable_view_class' => null,
CallableView 限定于此类内 callable_view_class 。
可以为 object;如果 callable_view_class 为 null 则为全局函数

        'callable_view_prefix' => null,
CallableView 视图方法前缀
callable_view_prefix 是方法前缀。 方法名都会把view 的 / 替换成 _ 下划线

        'callable_view_skip_replace' => false,
CallableView 是否替换默认 View
callable_view_skip_replace 打开的时候会在 初始化的时候替换默认的 Core\View

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

继承 [DuckPhp\Core\View](Core-View.md) 的所有方法。没有额外的公开方法。

    public function __construct()
默认的构造函数合并 View 类的选项

    public function init(array $options, object $context = null)
初始化

    public function _Show(array $data, string $view): void
重写

    public function _Display(string $view, ?array $data = null): void
重写

    protected function viewToCallback($func)
把view 转成 callback

## 详解

这个类 是用函数来代替默认的文件 View。 默认没打开

