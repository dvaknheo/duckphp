# DuckPhp\Ext\JsonRpcExt
[toc]

## 简介
 `组件类` json-rpc 扩展
## 选项

        'jsonrpc_namespace' => 'JsonRpc',
jsonrpc 默认jsonrpc 的命名空间

        'jsonrpc_backend' => 'https://127.0.0.1',
jsonrpc 后端地址

        'jsonrpc_is_debug' => false,
jsonrpc 是否调试

        'jsonrpc_enable_autoload' => true,
jsonrpc 是否要自动加载

        'jsonrpc_check_token_handler' => null,
jsonrpc Token 处理

        'jsonrpc_wrap_auto_adjust' => true,
jsonrpc 封装调整

        'jsonrpc_service_interface' => '',
jsonrpc 限制指定接口或者基类——todo 调整名字

        'jsonrpc_service_namespace' => '',
jsonrpc 限定服务命名空间
## 方法

    public function __construct()
    public function init(array $options, object $context = null)
    public function clear()
    public function getRealClass($object)
    public static function Wrap($class)
    public static function _Wrap($class)
    public function _autoload($class)
    public function callRpc($classname, $method, $arguments)
    public function onRpcCall(array $input)
    protected function curl_file_get_contents($url, $post)
    protected function prepare_token($ch)    protected function initOptions(array $options)
    
    public function _autoload($class): void
    
    protected function adjustService($service)
    
    protected function prepare_token($ch)

    protected function initOptions(array $options)


## 示例
```php
// Base\App onInit;
$this->options['ext']['Ext\JsonRpcExt']=[
    'jsonrpc_backend'=>['http://test.duckphp.dev/json_rpc','127.0.0.1:80'], 
];
```

/////////////
```php
<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require(__DIR__.'/../../../autoload.php');  // @DUCKPHP_HEADFILE

use DuckPhp\DuckPhp;
use DuckPhp\SingletonEx\SingletonEx;
use DuckPhp\Ext\JsonRpcExt;

use JsonRpc\CalcService as RemoteCalcService;

class CalcService
{
    use SingletonEx;
    public function add($a,$b)
    {
        return $a+$b;
    }
}
class Main
{
    public function index()
    {
        $t=CalcService::G()->add(1,2);
        var_dump($t);
        
        CalcService::G(JsonRpcExt::Wrap(CalcService::class));
        $t=CalcService::G()->add(3,4);
        var_dump($t);
        
        $t=RemoteCalcService::G()->add(5,6);
        var_dump($t);
        
        var_dump(DATE(DATE_ATOM));
    }
    public function json_rpc()
    {
        $ret=JsonRpcExt::G()->onRpcCall($_POST);
        echo json_encode($ret);
    }
}

$options=[
    'is_debug'=>true,
    'skip_setting_file'=>true,
    'namespace_controller'=>'\\',
    'ext'=> [
        JsonRpcExt::class =>[
            'jsonrpc_namespace'=>'JsonRpc',
            'jsonrpc_is_debug'=>true,
            //'jsonrpc_backend'=>'';
        ],
    ],
    
];

DuckPhp::RunQuickly($options,function(){
    $url=DuckPhp::Domain().$_SERVER['SCRIPT_NAME'].'/json_rpc';
    $ip=$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'];
    JsonRpcExt::G()->options['jsonrpc_backend']=[$url,$ip];
});
```
这个例子，将会两次远程调用  json_rpc 的 CalcService 。

这里的 json_rpc 是服务端的实现

如果你要 做自己的权限处理，则重写


