# DuckPhp\Core\ComponentInterface
[toc]

## 简介

`组件类` 接口

## 选项

## 公开方法

//public $options; /* array() */;

    //选项
public static function G($new_object = null);

    可变单例函数
public function init(array $options, $contetxt = null);/*return this */

    初始化
public function isInited():bool;

    是否已经初始化,DuckPhp 系统中没使用到，或许会有使用到的