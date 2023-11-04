# DuckPhp\Ext\Ext-RouteHookResource

## 简介
用于 __res 实现

## 选项
        'path' => '',

        'path_resource' => 'res',
资源目录
        'controller_resource_prefix' => '',
资源前缀

## 方法
    public static function Hook($path_info)

    protected function initContext(object $context)

    public function _Hook($path_info)


## 说明

一般用 插件模式的 资源处理
比如

res/ 目录，对应 admin/res/ 之类等