# DuckPhp\Ext\RouteHookApiServer
[toc]

## 简介

本扩展用于实现一个 api 服务器，参见示例
## 选项
全部选项

        'namespace' => '',
命名空间

        'api_server_base_class' => '',
限定于基本类或接口

        'api_server_namespace' => 'Api',
api服务器的命名空间

        'api_server_class_postfix' => '',
类后缀

        'api_server_use_singletonex' => false,
api 使用单例模式

        'api_server_404_as_exception' => false,
api 404 抛异常

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

