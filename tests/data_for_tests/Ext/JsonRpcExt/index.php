<?php
require_once realpath (__DIR__.'/../../../../autoload.php');
use DuckPhp\Ext\JsonRpcExt;
use DuckPhp\Core\SingletonEx;
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
        $post=DuckPhp\App::SG()->_POST;
        if($post['method']==='TestService.the500'){
            var_dump(DATE(DATE_ATOM));
            return;
        }
        $ret= JsonRpcExt::G()->onRpcCall(DuckPhp\App::SG()->_POST);
        
        DuckPhp\App::ExitJson($ret);
    }
}


$options=[
    'skip_setting_file'=>true,
    'namespace_controller'=>'\\',
];

$flag=DuckPhp\App::RunQuickly($options);