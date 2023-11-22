# DuckPhp\Component\RouteHookPathInfoCompat
[toc]


## 简介
`组件类` 在不配置 PathInfo 下模拟 PathInfo 

## 选项
全部选项

        'path_info_compact_enable' => false,
无PATH_INFO兼容，启用
这项开启扩展才启用。

        'path_info_compact_action_key' => '_r',
无PATH_INFO兼容，替代的 action

        'path_info_compact_class_key' => '',
无PATH_INFO兼容，替代的 class

## 方法

    public static function Url($url = null)
    public function onUrl($url = null)
Url方法替换
    
    public static function Hook($path_info)
    public function _Hook($path_info)
路由钩子

    protected function initContext(object $context)
初始化

    protected function filteRewrite($url, &$ret = false)
过滤rewrite 模式，（未使用

## 详解

有时候，你只是做个局部项目，不打算修改 web 服务器配置，你可以使用无 PATH_INFO 的路由。

在选项里取消注释的代码加载以下代码

```php
$options['path_info_compact_enable'] = true;
//$options['path_info_compact_action_key'] = "_r";
//$options['path_info_compact_class_key'] = "";
```
选项说明： path_info_compact_action_key 就是 用于路由的 $\_GET 参数

如果没有 path_info_compact_class_key ，直接就是  `?\_r=/test/done` ,  有，就成了 `?\_m=test&_r=done`

`URL ($url) `函数也被接管。 自动替换成相应的实现。

函数模式，见另一扩展
[DuckPhp\Ext\RouteHookFunctionMode](Ext-RouteHookFunctionMode.md)

##完毕

