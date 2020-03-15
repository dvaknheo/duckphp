# Ext\Lazybones

## 简介
懒汉模式， 旧类
## 选项

## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    public function MapToService($serviceClass, $input)
    public function explodeService($object, $namespace = null)
    public function runRoute()
    protected function getCallback($full_class, $method)
    protected function getRouteDispatchInfo($blocks, $method)
    protected function getFullClassByNoNameSpace($path_class, $confirm = false)
    protected function checkLoadClass($path_class)
    protected function includeControllerFile($file)
    protected function getClassMethodAndParameters($blocks, $method)
    protected function getControllerByFiles()