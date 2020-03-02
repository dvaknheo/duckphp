# Core\View

## 简介
视图类
## 选项
'path' => '',
'path_view' => 'view',
'path_view_override' => '',
## 公开方法
public function __construct()
public function init($options=[], $context=null)
public function _Show($data=[], $view)
public function _ShowBlock($view, $data=null)
public function setViewWrapper($head_file, $foot_file)
public function assignViewData($key, $value=null)
public function setOverridePath($path)


## 详解

