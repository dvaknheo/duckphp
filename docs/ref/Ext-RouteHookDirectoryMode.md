# DuckPhp\Ext\RouteHookDirectoryMode

## 简介
多目录模式的 hook

##### 
## 选项
全部选项:

        'mode_dir_basepath' => '',
目录模式的基准路径

## 方法

    protected function initOptions(array $options)

    protected function initContext(object $context)

    public static function Url($url = null)

    public function onUrl($url = null)

    protected function adjustPathinfo($basepath, $path_info)

    public static function Hook($path_info)

    public function _Hook($path_info)

## 详解





