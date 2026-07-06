<?php
require_once realpath (__DIR__.'/../../../../autoload.php');
use DuckPhp\DuckPhpAllInOne;
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
class JsonExtApp extends DuckPhpAllInOne
{
    public $options = [
        'is_debug'=>true,
        //'path_info_compact_enable' => false,

    ];
    public function __construct()
    {
        parent::__construct();
        $this->options['path_info_compact_enable'] = false;
    }
    public function action_index()
    {
        echo (DATE(DATE_ATOM));
    }
    public function action_json_rpc()
    {
        $post=static::POST(null);
        $method =  $post['method']??null;
        if($method==='TestService.the500'){
            var_dump(DATE(DATE_ATOM));
            return;
        }
        $ret= JsonRpcExt::_()->onRpcCall(static::POST(null));
        
        static::ShowJson($ret);
    }
}



$flag=JsonExtApp::RunQuickly([]);

//var_dump(\DuckPhp\Core\Route::_()->options);