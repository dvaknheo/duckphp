# DuckPhp\Ext\JsonRpcExt
[toc]

## 简介
 `组件类` json-rpc 扩展
## 选项

'jsonrpc_namespace' => 'JsonRpc',
'jsonrpc_backend' => 'https://127.0.0.1',
'jsonrpc_is_debug' => false,
'jsonrpc_enable_autoload' => true,
'jsonrpc_check_token_handler' => null,
'jsonrpc_wrap_auto_adjust' => true,
'jsonrpc_service_interface' => '',
'jsonrpc_service_namespace' => '',

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
use DuckPhp\Core\SingletonEx;
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

如果你要 做自己的权限处理，则重写 protected function prepare_token($ch)。


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
    protected function prepare_token($ch)