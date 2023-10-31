<?php
require_once realpath (__DIR__.'/../../../../autoload.php');
use DuckPhp\DuckPhpAllInOne as DuckPhp;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;
use DuckPhp\Ext\JsonRpcExt;
class TestService
{
    use SingletonExTrait;
    public function foo()
    {
        return 'Server:'.DATE(DATE_ATOM);
    }
    public function throwException()
    {
        throw new  \Exception ("serverException",1024);
    }
}
class MainController
{
    public function action_index()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function action_json_rpc()
    {
        $post=DuckPhp::POST(null);
        $method =  $post['method']??null;
        if($method==='TestService.the500'){
            var_dump(DATE(DATE_ATOM));
            return;
        }
        $ret= JsonRpcExt::_()->onRpcCall(DuckPhp::POST(null));
        
        DuckPhp::ExitJson($ret);
    }
}


$options=[
    'is_debug'=>true,
    'namespace_controller'=>'\\',
//'controller_class_postfix' => 'Controller',
//'controller_method_prefix' => 'action_',
];

$flag=DuckPhp::RunQuickly($options);

var_dump(\DuckPhp\Core\Route::_()->options);