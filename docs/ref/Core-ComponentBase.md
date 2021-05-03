# DuckPhp\Core\ComponentBase
[toc]

## 简介

`组件`的基类 

+ 实现 ComponentInterface 接口

`ComponentBase` 也内嵌实现了可变单例方法

## 方法

    public $options; /* array() */;
选项

    public function __construct()
用于重写的空构造函数

    public static function G($object = null)
可变单例函数

    public function isInited(): bool
是否已经初始化,DuckPhp 系统中没使用到

    public function init(array $options, ?object $context = null)
初始化

    protected function initOptions(array $options)
空函数，你可以 override 做额外选项处理。

    protected function initContext(object $context)
空函数，你可以 override 做 context 处理。

    protected function getComponenetPathByKey($path_key, $path_key_parent = 'path'): string
便于获得 path - path_key 组合的 路径

    public function checkInstall($context)
检查是否安装初始化

## 说明

ComponentBase 是所有组件类的基类。

ComponentBase 裁剪你只需要的选项。

你只需要 override initOptions 和 initContext 即可

组件类的 选项会被 trim 到只需要的组件

getComponenetPathByKey 这个方法，用于辅助  'path' ,'path' 这样的联合 path 选项

如果 'path_x' 是绝对路径，则返回 'path_x' ，否则返回 'path'.'path_x'
