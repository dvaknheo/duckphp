# DuckPhp\Core\View
[toc]

## 简介
`组件类` 视图类
## 选项

        'path' => '',
路径

        'path_view' => 'view',
视图路径

        'path_view_override' => '',
用于覆盖的路径——用于插件模式

        'skip_view_notice_error' => true,
关闭  View 视图的 notice 警告，以避免麻烦的处理。

## 方法

    public static function Show(array $data = [], string $view = null): void
    public function _Show(array $data, string $view): void
显示文件，包括页眉页脚

    public static function Display(string $view, ?array $data = null): void
    public function _Display(string $view, ?array $data = null): void
显示文件，不包括页眉页脚。

    public static function Render(string $view, ?array $data = null): string
    public function _Render(string $view, ?array $data = null): string
渲染文件，不包括页眉页脚。得到输出的字符串。

    public function setViewHeadFoot(?string $head_file, ?string $foot_file): void
设置页眉页脚

    public function assignViewData($key, $value = null): void
设置要显示的数据，可批量

    public function reset(): void
重置

    public function getViewPath()
获取View目录

    public function getViewData(): array
获取View 的数据

    protected function getViewFile(?string $view): string
获取View的文件
## 详解

DuckPhp\Core\View 的选项共享一个 path,带一个 path_view.

path_view 如果是 / 开始的，会忽略 path 选项

当你想把视图目录 放入 app 目录的时候，请自行调整 path_view

