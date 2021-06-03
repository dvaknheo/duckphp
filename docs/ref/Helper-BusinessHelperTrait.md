# DuckPhp\Helper\BusinessHelperTrait
[toc]

## 简介

BusinessHelper 绑定了 [DuckPhp\Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md) ，BusinessHelperTrait

## 方法

    public static function Setting($key)
获得设置信息

    public static function Config($key, $file_basename = 'config')
获得配置

    public static function LoadConfig($file_basename)
获得配置数组

    public static function FireEvent($event, ...$args)
触发事件

    public static function OnEvent($event, $callback)
绑定事件

    public static function Cache($object = null)
获得缓存对象

    public static function XpCall($callback, ...$args)
调用，如果产生异常则返回异常，否则返回正常数据

ControllerHelperTrait 比 BusinessHelperTrait 少了 Cache()