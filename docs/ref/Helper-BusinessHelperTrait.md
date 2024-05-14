# DuckPhp\Helper\BusinessHelperTrait
[toc]

## 简介

BusinessHelper 绑定了 [DuckPhp\Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md) ，BusinessHelperTrait

## 方法

    public static function Setting($key = null, $default = null)
获得设置信息

    public static function Config($file_basename, $key = null, $default = null)
获得配置


    public static function FireEvent($event, ...$args)
触发事件

    public static function OnEvent($event, $callback)
绑定事件

    public static function Cache($object = null)
获得缓存对象

    public static function XpCall($callback, ...$args)
调用，如果产生异常则返回异常，否则返回正常数据



## 说明

ControllerHelperTrait 相比比 BusinessHelperTrait 少了 Cache()







    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)

    public static function AdminService()

    public static function UserService()

    public static function PathForProject()

    public static function PathForRuntime()

