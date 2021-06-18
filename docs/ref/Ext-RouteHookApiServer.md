# DuckPhp\Ext\RouteHookApiServer
[toc]

## 简介

本扩展用于实现一个 api 服务器，参见示例
## 选项
全部选项

        'namespace' => '',
命名空间
        'api_server_namespace' => 'Api',
API服务器， 命名空间，配合 namespace选项使用

        'api_server_base_class' => '',
API服务器，限定的接口或基类，  ~ 开始的表示是当前命名空间

        'api_server_class_postfix' => '',
API服务器， 限定类名后缀

        'api_server_use_singletonex' => false,
API服务器， 使用可变单例模式，方便替换实现

        'api_server_404_as_exception' => false,
API服务器， 404 引发异常的模式
如果设置为 true ，则 404 的时候抛出异常


## 公开方法

    protected function initContext(object $context)
    
    public static function Hook($path_info)
    
    public function _Hook($path_info)
    
    protected function onMissing()
    
    public static function OnJsonError($e)
    
    public function _OnJsonError($e)
    
    protected function getComponenetNamespace($namespace_key)
    
    protected function getObjectAndMethod($path_info)
    
    protected function getInputs($path_info)
    
    protected function exitJson($ret, $exit = true)
    
    protected function callAPI($object, $method, $input)



## 详解

