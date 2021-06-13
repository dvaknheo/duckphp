<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require(__DIR__.'/../../../autoload.php');  // @DUCKPHP_HEADFILE

use DuckPhp\DuckPhp;
use DuckPhp\Ext\JsonRpcExt;
use DuckPhp\SingletonEx\SingletonExTrait;

class CalcService
{
    use SingletonExTrait;
    public function add($a, $b)
    {
        return $a + $b;
    }
}
class Main
{
    public function index()
    {
        $t1 = CalcService::G()->add(1, 2);
        
        
        CalcService::G(JsonRpcExt::Wrap(CalcService::class));
        $t2 = CalcService::G()->add(3, 4);
        
        $t3 = \JsonRpc\CalcService::G()->add(5, 6);
        echo <<<EOT
本地调用 1 + 2 = $t1 <br />
远程调用 3 + 4 = $t2 <br />
远程调用 5 + 6 = $t3 <br />
EOT;
        
        var_dump(DATE(DATE_ATOM));
    }
    public function json_rpc()
    {
        $ret = JsonRpcExt::G()->onRpcCall($_POST);
        echo json_encode($ret);
    }
}

$options = [
    'is_debug'=>true,
    'namespace_controller' => '\\',
    'ext' => [
        JsonRpcExt::class => [
            'jsonrpc_namespace' => 'JsonRpc',  //对应  \JsonRpc\ 这个命名空间
            'jsonrpc_is_debug' => true,
            //'jsonrpc_backend'=>'';
        ],
    ],
    
];

DuckPhp::RunQuickly($options, function () {
    $url = DuckPhp::Domain(true).$_SERVER['SCRIPT_NAME'].'/json_rpc';
    $ip = ($_SERVER['SERVER_ADDR'] ?? '127.0.0.1').':'.$_SERVER['SERVER_PORT'];
    JsonRpcExt::G()->options['jsonrpc_backend'] = [$url,$ip];
});
