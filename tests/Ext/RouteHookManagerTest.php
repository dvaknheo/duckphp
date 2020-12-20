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
$options['path_info_compact_enable'] = true;

DuckPhp::G()->init($options);

var_dump(DuckPhp::G()->options);
///////////////////////////

Route::G()->reset()->run();
//Route::G()->addRouteHook(function(){},'prepend-inner');
echo "<pre>\n";
echo RouteHookManager::G()->dump();

RouteHookManager::G()->attachPostRun()->removeAll(['DuckPhp\\Ext\\RouteHookRouteMap','AppendHook'])->detach();
RouteHookManager::G()->attachPreRun()->moveBefore(['DuckPhp\\Ext\\RouteHookRouteMap','PrependHook'],['DuckPhp\\Ext\\RouteHookPathInfoCompat','Hook'])->detach();
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
