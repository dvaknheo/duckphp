# DuckPhp\Ext\RouteHookDirectoryMode

## 简介
多目录模式的 hook

##### 
## 选项
全部选项:

    'mode_dir_basepath' => '',
基准路径

## 方法

    public function __construct()
    public function init(array $options, object $context = null)
    protected function adjustPathinfo($basepath, $path_info)
    public function onURL($url = null)
    public static function Hook($path_info)
    public function _Hook($path_info)    protected function initOptions(array $options)
    
    protected function initContext(object $context)
    
    public static function Url($url = null)
    
    public function onUrl($url = null)
    
    public function _Hook($path_info)


    protected function initOptions(array $options)



## 详解


