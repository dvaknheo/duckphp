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
use DuckPhp\Foundation\SingletonTrait;
use DuckPhp\Foundation\Controller\ControllerHelper as Helper;

class CalcService
{
    use SingletonTrait;
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
        // {name} placeholders are filled in by Lang::format()
        echo __l('rpc.result', ['local' => $t1, 'remote1' => $t2, 'remote2' => $t3, 'date' => $date]);
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
    'controller_method_prefix' => 'action_',
    // find `demo/config/lang-*.php` (this entry point is its own app)
    'path' => __DIR__ . '/../',
    'lang_default' => 'en',
    'ext' => [
        JsonRpcExt::class => [
            'jsonrpc_namespace' => 'JsonRpc',  // corresponds to the \JsonRpc\ namespace
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
