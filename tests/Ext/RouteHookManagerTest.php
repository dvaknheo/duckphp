<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Ext\RouteHookManager;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Component\RouteHookPathInfoCompat;

class RouteHookManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookManager::class);
////[[[[

$options = [];

$options['is_debug'] = true;
$options['override_class'] = '';
//$options['path_info_compact_enable'] = true;
$options['ext'][RouteHookRouteMap::class] =true;
$options['ext'][RouteHookPathInfoCompat::class] =true;
DuckPhp::_()->init($options);

//var_dump(DuckPhp::_()->options);
///////////////////////////
Route::_()->run();
//Route::_()->addRouteHook(function(){},'prepend-inner');
echo "<pre>\n";
echo RouteHookManager::_()->dump();

RouteHookManager::_()->attachPostRun()->removeAll(['DuckPhp\\Component\\RouteHookRouteMap','AppendHook'])->detach();
RouteHookManager::_()->attachPreRun()->moveBefore(['DuckPhp\\Component\\RouteHookRouteMap','PrependHook'],['DuckPhp\\Component\\RouteHookPathInfoCompat','Hook'])->detach();
$list=RouteHookManager::_()->attachPostRun()->getHookList();
$list[]="abc";
RouteHookManager::_()->attachPostRun()->setHookList($list);

RouteHookManager::_()->attachPostRun()->append(['DuckPhp\\Component\\RouteHookRouteMap','AppendHook']);


echo "\n------------------------------------\n";
echo RouteHookManager::_()->dump();
echo "\n<pre>\n";
////]]]]
        
        \LibCoverage\LibCoverage::End();
       
    }
}
