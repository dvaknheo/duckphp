# Ext\RouteHookOneFileMode

## 简介
单文件模式的扩展
## 选项

## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    public static function URL($url = null)
    public function onURL($url = null)
    protected function filteRewrite($url, &$ret = false)
    public static function Hook($path_info)
    public function _Hook($path_info)