<?php
function _dnmvcs_namespace_autoload($class) {
    $path=__DIR__.'/src/';
    $namespace='DuckPhp\\';
    if (strncmp($namespace, $class, strlen($namespace)) !== 0) {
        return false;
    }
    if (strncmp("DuckPhp\\Core\\Helper\\", $class, strlen("DuckPhp\\Core\\Helper\\"))=== 0) {
        return false;
    }
    $file = $path . str_replace('\\', '/', substr($class, strlen($namespace))) . '.php';
    //if (!file_exists($file)) {
    //    return false;
    //}
    require_once $file;
    return true;
}
spl_autoload_register('_dnmvcs_namespace_autoload');
function _dnmvcs_namespace_autoload_q($class)
{
    static $classes=array (
  'DuckPhp\\App' => '/App.php',
  'DuckPhp\\Core\\App' => '/Core/App.php',
  'DuckPhp\\Core\\AppPluginTrait' => '/Core/AppPluginTrait.php',
  'DuckPhp\\Core\\AutoLoader' => '/Core/AutoLoader.php',
  'DuckPhp\\Core\\ComponentBase' => '/Core/ComponentBase.php',
  'DuckPhp\\Core\\ComponentInterface' => '/Core/ComponentInterface.php',
  'DuckPhp\\Core\\Configer' => '/Core/Configer.php',
  'DuckPhp\\Core\\ExceptionManager' => '/Core/ExceptionManager.php',
  'DuckPhp\\Core\\ExtendableStaticCallTrait' => '/Core/ExtendableStaticCallTrait.php',
  'DuckPhp\\Core\\Functions' => '/Core/Functions.php',
  'DuckPhp\\Core\\HttpServer' => '/Core/HttpServer.php',
  'DuckPhp\\Core\\Kernel' => '/Core/Kernel.php',
  'DuckPhp\\Core\\Logger' => '/Core/Logger.php',
  'DuckPhp\\Core\\Route' => '/Core/Route.php',
  'DuckPhp\\Core\\RuntimeState' => '/Core/RuntimeState.php',
  'DuckPhp\\Core\\SingletonEx' => '/Core/SingletonEx.php',
  'DuckPhp\\Core\\SuperGlobal' => '/Core/SuperGlobal.php',
  'DuckPhp\\Core\\SystemWrapperTrait' => '/Core/SystemWrapperTrait.php',
  'DuckPhp\\Core\\ThrowOn' => '/Core/ThrowOn.php',
  'DuckPhp\\Core\\View' => '/Core/View.php',
  'DuckPhp\\DB\\DB' => '/DB/DB.php',
  'DuckPhp\\DB\\DBAdvanceTrait' => '/DB/DBAdvanceTrait.php',
  'DuckPhp\\DB\\DBInterface' => '/DB/DBInterface.php',
  'DuckPhp\\Ext\\CallableView' => '/Ext/CallableView.php',
  'DuckPhp\\Ext\\DBManager' => '/Ext/DBManager.php',
  'DuckPhp\\Ext\\DBReusePoolProxy' => '/Ext/DBReusePoolProxy.php',
  'DuckPhp\\Ext\\FacadesAutoLoader' => '/Ext/FacadesAutoLoader.php',
  'DuckPhp\\Ext\\FacadesBase' => '/Ext/FacadesBase.php',
  'DuckPhp\\Ext\\JsonRpcClientBase' => '/Ext/JsonRpcClientBase.php',
  'DuckPhp\\Ext\\JsonRpcExt' => '/Ext/JsonRpcExt.php',
  'DuckPhp\\Ext\\Misc' => '/Ext/Misc.php',
  'DuckPhp\\Ext\\Pager' => '/Ext/Pager.php',
  'DuckPhp\\Ext\\PagerInterface' => '/Ext/PagerInterface.php',
  'DuckPhp\\Ext\\PluginForSwooleHttpd' => '/Ext/PluginForSwooleHttpd.php',
  'DuckPhp\\Ext\\RedisManager' => '/Ext/RedisManager.php',
  'DuckPhp\\Ext\\RedisSimpleCache' => '/Ext/RedisSimpleCache.php',
  'DuckPhp\\Ext\\RouteHookDirectoryMode' => '/Ext/RouteHookDirectoryMode.php',
  'DuckPhp\\Ext\\RouteHookOneFileMode' => '/Ext/RouteHookOneFileMode.php',
  'DuckPhp\\Ext\\RouteHookRewrite' => '/Ext/RouteHookRewrite.php',
  'DuckPhp\\Ext\\RouteHookRouteMap' => '/Ext/RouteHookRouteMap.php',
  'DuckPhp\\Ext\\StrictCheck' => '/Ext/StrictCheck.php',
  'DuckPhp\\Ext\\StrictCheckModelTrait' => '/Ext/StrictCheckModelTrait.php',
  'DuckPhp\\Ext\\StrictCheckServiceTrait' => '/Ext/StrictCheckServiceTrait.php',
  'DuckPhp\\Helper\\AppHelper' => '/Helper/AppHelper.php',
  'DuckPhp\\Helper\\ControllerHelper' => '/Helper/ControllerHelper.php',
  'DuckPhp\\Helper\\HelperTrait' => '/Helper/HelperTrait.php',
  'DuckPhp\\Helper\\ModelHelper' => '/Helper/ModelHelper.php',
  'DuckPhp\\Helper\\ServiceHelper' => '/Helper/ServiceHelper.php',
  'DuckPhp\\Helper\\ViewHelper' => '/Helper/ViewHelper.php',
  'DuckPhp\\HttpServer' => '/HttpServer.php',
  'DuckPhp\\SingletonEx' => '/SingletonEx.php',
  'DuckPhp\\ThrowOn' => '/ThrowOn.php',
  );
    if(!isset($classes[$class])){
        return;
    }
    require_once __DIR__.'/src'.$classes[$class];
    return true;
};