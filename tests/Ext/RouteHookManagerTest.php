<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Ext\RouteHookManager;

class RouteHookManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookManager::class);
////[[[[

$options = [];

$options['is_debug'] = true;
$options['override_class'] = '';
$options['skip_setting_file'] = true;
// $options['path_namespace'] = 'app';
$options['ext']['DuckPhp\\Ext\\RouteHookPathInfoByGet']=true; //@DUCKPHP_DELETE

DuckPhp::G()->init($options);
Route::G()->bindServerData($_SERVER)->run();
//Route::G()->addRouteHook(function(){},'prepend-inner');
echo "<pre>\n";
echo RouteHookManager::G()->dump();

RouteHookManager::G()->attachPostRun()->removeAll(['DuckPhp\\Ext\\RouteHookRouteMap','AppendHook'])->detach();
RouteHookManager::G()->attachPreRun()->moveBefore(['DuckPhp\\Ext\\RouteHookRouteMap','PrependHook'],['DuckPhp\\Ext\\RouteHookPathInfoByGet','Hook'])->detach();
$list=RouteHookManager::G()->attachPostRun()->getHookList();
$list[]="abc";
RouteHookManager::G()->attachPostRun()->setHookList($list);


echo "\n------------------------------------\n";
echo RouteHookManager::G()->dump();
echo "\n<pre>\n";
////]]]]
        
        \MyCodeCoverage::G()->end();
       
    }
}
