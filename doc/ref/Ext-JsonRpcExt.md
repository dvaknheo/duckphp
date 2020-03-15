# Ext\JsonRpcExt

## 简介
 json-rpc 扩展
## 选项
'jsonrpc_namespace'=>'JsonRpc',
'jsonrpc_backend'=>'https://127.0.0.1', 
//TODO
后端，允许用数组，后面表示是实际IP，用于方便调试，见例子。实际连的是 127.0.0.1。


## 公开方法


## 详解


#### 默认选项

'jsonrpc_namespace' => 'JsonRpc',
'jsonrpc_backend' => 'https://127.0.0.1',
'jsonrpc_is_debug' => false,
'jsonrpc_enable_autoload' => true,

## 示例
```php
// Base\App onInit;
$this->options['ext']['Ext\JsonRpcExt']=[
    'jsonrpc_backend'=>['http://test.duckphp.dev/json_rpc','127.0.0.1:80'], 
];
```

/////////////
```php
<?php
require_once(__DIR__.'/../vendor/autoload.php');

use DuckPHP\Core\Route;
use DuckPHP\Core\SingletonEx;
use DuckPHP\Ext\JsonRpcExt;
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
        
        $t=CalcService::G(JsonRpcExt::Wrap(CalcService::class))->add(3,4);
        var_dump($t);
    }
    public function json_rpc()
    {
        $ret=JsonRpcExt::G()->onRpcCall($_POST);
        echo json_encode($ret);
    }
}
$options=[
    'is_debug'=>true,
    'namespace_controller'=>'\\',
];
JsonRpcExt::G()->init([
    'jsonrpc_namespace'=>'JsonRpc',
    'jsonrpc_backend'=>['http://d.duckphp.dev/json_rpc','127.0.0.1:80'], //请自行修改这里。
    'jsonrpc_is_debug'=>true,
],null);
$flag=Route::RunQuickly($options);
if (!$flag) {
    header(404);
    echo "404!";
}
```
这个例子，将会两次远程调用 http://d.duckphp.dev/2.php/json_rpc 的 CalcService 。

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