# DuckPhp\Core\PhaseContainer
[toc]
## 简介

**核心容器容器**

DuckPhp 的单例模类都放这里

## 选项

无

## 方法
    public static function ReplaceSingletonImplement()

    public static function GetObject($class, $object = null)

    public static function GetContainerInstanceEx($object = null)

    public static function GetContainer()

    public function _GetObject(string $class, $object = null)


    public function setDefaultContainer($class)

    public function addPublicClasses($classes)

    public function removePublicClasses($classes)

    public function setCurrentContainer($container)

    public function getCurrentContainer()

    public function dumpAllObject()

    public function __construct()

    protected function createObject($class)
说明