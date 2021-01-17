<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Ext\RouteHookManager;

class RouteHookManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookManager::class);
////[[[[

$options = [];

$options['is_debug'] = true;
$options['override_class'] = '';
$options['path_info_compact_enable'] = true;

DuckPhp::G()->init($options);

var_dump(DuckPhp::G()->options);
///////////////////////////

Route::G()->reset()->run();
//Route::G()->addRouteHook(function(){},'prepend-inner');
echo "<pre>\n";
echo RouteHookManager::G()->dump();

RouteHookManager::G()->attachPostRun()->removeAll(['DuckPhp\\Component\\RouteHookRouteMap','AppendHook'])->detach();
RouteHookManager::G()->attachPreRun()->moveBefore(['DuckPhp\\Component\\RouteHookRouteMap','PrependHook'],['DuckPhp\\Component\\RouteHookPathInfoCompat','Hook'])->detach();
$list=RouteHookManager::G()->attachPostRun()->getHookList();
$list[]="abc";
RouteHookManager::G()->attachPostRun()->setHookList($list);

RouteHookManager::G()->attachPostRun()->append(['DuckPhp\\Component\\RouteHookRouteMap','AppendHook']);


echo "\n------------------------------------\n";
echo RouteHookManager::G()->dump();
echo "\n<pre>\n";
////]]]]
        
        \LibCoverage\LibCoverage::End();
       
    }
}
