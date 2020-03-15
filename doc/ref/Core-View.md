# Core\View

## 简介
视图类
## 选项
'path' => '',

    // xx
'path_view' => 'view',

    // xx
'path_view_override' => '',

    // xx
## 公开方法
__construct()

​	构造函数

init($options=[], $context=null)

_Show($data=[], $view)
_ShowBlock($view, $data=null)
setViewWrapper($head_file, $foot_file)
assignViewData($key, $value=null)
setOverridePath($path)


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    public function _Show($data = [], $view)
    public function _ShowBlock($view, $data = null)
    public function setViewWrapper($head_file, $foot_file)
    public function assignViewData($key, $value = null)
    public function setOverridePath($path)
    protected function getViewFile($path, $view)