<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//autoload file
$autoload_file = __DIR__.'../vendor/autoload.php';
if (is_file($autoload_file)) {
    require_once $autoload_file;
} else {
    $autoload_file = __DIR__.'/../../vendor/autoload.php';
    if (is_file($autoload_file)) {
        require_once $autoload_file;
    }
}
////////////////////////////////////////

use DuckPhp\DuckPhp;
use DuckPhp\Ext\JsonRpcExt;
use DuckPhp\Foundation\BusinessTrait;
use DuckPhp\Foundation\Controller\Helper;

class CalcService
{
    use BusinessTrait;
    public function add($a, $b)
    {
        return $a + $b;
    }
}
class MainController
{
    public function action_index()
    {
        $t1 = CalcService::_()->add(1, 2);
        
        
        CalcService::_(JsonRpcExt::Wrap(CalcService::class));
        $t2 = CalcService::_()->add(3, 4);
        
        $t3 = \JsonRpc\CalcService::_()->add(5, 6);
        $date = DATE(DATE_ATOM);
        echo <<<EOT
本地调用 1 + 2 = $t1 <br />
远程调用 3 + 4 = $t2 <br />
远程调用 5 + 6 = $t3 <br />
调用时间 $date
EOT;
    }
    public function action_json_rpc()
    {
        $ret = JsonRpcExt::_()->onRpcCall($_POST);
        echo json_encode($ret);
    }
}

$options = [
    'is_debug' => true,
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
    $url = Helper::Domain(true).$_SERVER['SCRIPT_NAME'].'/json_rpc';
    $ip = ($_SERVER['SERVER_ADDR'] ?? '127.0.0.1').':'.$_SERVER['SERVER_PORT'];
    JsonRpcExt::_()->options['jsonrpc_backend'] = [$url,$ip];
});
