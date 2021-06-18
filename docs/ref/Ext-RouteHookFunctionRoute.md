# DuckPhp\Ext\RouteHookFunctionRoute

## 简介
//
##### 
## 选项
全部选项:

        'function_route' => false,
函数模式路由，开关

        'function_route_method_prefix' => 'action_',
函数模式路由，函数前缀

        'function_route_404_to_index' => false,
函数模式路由，404 是否由 index 来执行

## 方法

    protected function initContext(object $context)
初始化方法

    public static function Hook($path_info)
    public function _Hook($path_info = '/')
路由钩子

    private function runCallback($callback)
内部方法

## 详解

参见例子。

action_Class_Method 。 action_Class_do_PostMethod 。 这么来
action_index

ToDo 目前不区分大小写。