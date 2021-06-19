# DuckPhp\Ext\CallableView
[toc]

## 简介
JsonView 扩展扩充了默认的 view 类，用于用函数替代文件方式显示视图
## 选项


继承 [DuckPhp\Core\View](Core-View.md) 的所有选项。

        'json_view_skip_replace' => false,
jsonview, 跳过替换默认的View

        'json_view_skip_vars' => [],
jsonview, 排除变量
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

继承 DuckPhp\Core\View 的所有方法。没有其他方法。
以下是重写的方法：

    public function __construct()
    protected function initContext(object $context)
    public function init(array $options, object $context = null)
    public function _Show(array $data, string $view): void
    public function _Display(string $view, ?array $data = null): void
    
## 详解

这个类 是用函数来代替默认的文件 View。 默认没打开

这个类是输出忽略视图选项，输出 json 。    


