# DuckPhp\Core\ComponentInterface

组件接口。

## 简介

`ComponentInterface` 定义了 DuckPHP 组件的基本契约。所有核心组件都应遵循该接口约定，提供可变单例访问、初始化和状态检查能力。

## 接口定义

```php
namespace DuckPhp\Core;

interface ComponentInterface
{
    public static function _($new_object = null);
    public function init(array $options, ?object $contetxt = null);/*return this */
    public function isInited():bool;
}
```

## 方法说明

    public static function _($new_object = null)
获取或设置组件单例实例。传入对象时替换单例，不传时返回当前实例

    public function init(array $options, ?object $contetxt = null);/*return this */
初始化组件，合并选项并传入上下文。返回当前实例

    public function isInited():bool;
返回组件是否已完成初始化

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\SingletonTrait](Core-SingletonTrait.md)
