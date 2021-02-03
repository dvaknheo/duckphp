# DuckPhp\Core\ComponentBase
[toc]

## 简介

`组件`的基类 

+ 实现 ComponentInterface 接口
    
## 公开方法

//public $options; /* array() */;

    //选项
public static function G($new_object = null);

    可变单例函数
public function init(array $options, $contetxt = null);/*return this */

    初始化
public function isInited():bool;

    是否已经初始化,DuckPhp 系统中没使用到
## 内部方法
protected function initOptions(array $options);

    空函数，你可以 override 做额外选项处理。
protected function initContext(object $contetxt);

    空函数，你可以 override 做 context 处理。
## 说明

ComponentBase 是所有组件类的基类。

ComponentBase 裁剪你只需要的选项。

你只需要 override initOptions 和 initContext 即可

组件类的 选项会被 trim 到只需要的组件