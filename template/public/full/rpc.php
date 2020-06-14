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
