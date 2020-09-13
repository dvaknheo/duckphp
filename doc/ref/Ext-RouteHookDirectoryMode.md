# DuckPhp\Ext\RouteHookDirectoryMode

## 简介
多目录模式的 hook

##### 
## 选项
'mode_dir_use_path_info'=>true,

## 公开方法


## 详解


    public function __construct()
    public function init(array $options, object $context = null)
    protected function adjustPathinfo($basepath, $path_info)
    public function onURL($url = null)
    public static function Hook($path_info)
    public function _Hook($path_info)