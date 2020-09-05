<?php
require_once realpath (__DIR__.'/../../../../autoload.php');
use DuckPhp\DuckPhp;
use DuckPhp\Core\SingletonEx;
use DuckPhp\Ext\JsonRpcExt;
class TestService
{
    use SingletonEx;
    public function foo()
    {
        return 'Server:'.DATE(DATE_ATOM);
    }
    public function throwException()
    {
        throw new  \Exception ("serverException",1024);
    }
}
class Main
{
    public function index()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function json_rpc()
    {
        $post=DuckPhp::POST(null);
        if($post['method']==='TestService.the500'){
            var_dump(DATE(DATE_ATOM));
            return;
        }
        $ret= JsonRpcExt::G()->onRpcCall(DuckPhp::POST(null));
        
        DuckPhp::ExitJson($ret);
    }
}


$options=[
    'is_debug'=>true,
    'skip_setting_file'=>true,
    'namespace_controller'=>'\\',
];

$flag=DuckPhp::RunQuickly($options);