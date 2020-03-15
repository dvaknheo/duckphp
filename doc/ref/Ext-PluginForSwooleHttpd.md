# Ext\PluginForSwooleHttpd

## 简介
SwooleHttpd 的插件
组件类， 实验性
## 选项
        'swoole_ext_class' => 'SwooleHttpd\\SwooleExt',

## 公开方法


## 详解

    public function init($options, $context)
    public function run()
    public function onSwooleHttpdInit($SwooleHttpd = null, ?callable $RunHandler = null)
    public function onSwooleHttpdStart($SwooleHttpd = null)
    public function onSwooleHttpdRequest($SwooleHttpd)
    public function getStaticComponentClasses()
    public function getDynamicComponentClasses()
    public static function Hook($path_info)
    public function _Hook($path_info)