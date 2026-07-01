# DuckPhp\Ext\JsonRpcClientBase

## 简介
JsonRPC 的客户端基类
## 选项

## 公开方法
    public function __construct()
    public function init(array $options, ?object $context = null)
    
    public function isInited(): bool
    
    public function __call($method, $arguments)
    
    public function setJsonRpcClientBase($class)

## 详解
查看 [JsonRpcExt](Ext-JsonRpcExt.md)文档
注意， ComponentBase 的方法不能被重写


