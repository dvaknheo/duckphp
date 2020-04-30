# Core\View

## 简介
`组件类` 视图类
## 选项
'path' => '',

    路径
'path_view' => 'view',

    视图路径
'path_view_override' => '',

    用于覆盖的路径——用于插件模式
## 公开方法

public function __construct()

    空构造函数
public function init(array $options, object $context = null)

    初始化
public function _Show($data = [], $view)

    显示文件，包括页眉页脚
public function _Display($view, $data = null)

    显示文件，不包括页眉页脚
public function setViewWrapper($head_file, $foot_file)

    设置页眉页脚
public function assignViewData($key, $value = null)

    设置要显示的数据，可批量
public function setOverridePath($path)

    插件模式下设置视图路径
protected function getViewFile($path, $view)

    获得 View 文件。

## 详解

Core\View 的选项共享一个 path,带一个 path_view.

path_view 如果是 / 开始的，会忽略 path 选项

当你想把视图目录 放入 app 目录的时候，请自行调整 path_view