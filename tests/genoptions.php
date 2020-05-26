<?php
require_once(__DIR__.'/../../autoload.php');

$classes="DuckPhp/Ext/CallableView
DuckPhp/Ext/DBManager
DuckPhp/Ext/DBReusePoolProxy
DuckPhp/Ext/FacadesAutoLoader
DuckPhp/Ext/JsonRpcExt
DuckPhp/Ext/Misc
DuckPhp/Ext/PluginForSwooleHttpd
DuckPhp/Ext/RedisManager
DuckPhp/Ext/RedisSimpleCache
DuckPhp/Ext/RouteHookDirectoryMode
DuckPhp/Ext/RouteHookOneFileMode
DuckPhp/Ext/RouteHookRewrite
DuckPhp/Ext/RouteHookRouteMap
DuckPhp/Ext/StrictCheck";
$classes=explode("\n",str_replace("/","\\",$classes));

foreach($classes as $class){
    $options=(new $class())->options;
    ksort($options);
    echo "/*\n";
    echo "\$options['ext'][".var_export($class,true)."] = true;\n";
    foreach($options as $k =>$v){
        echo "    \$options[".var_export($k,true)."]=".var_export($v,true).";\n";
    }
    echo "//*/\n";
}

return;
/*
$classes="DuckPhp/App
DuckPhp/Core/AutoLoader
DuckPhp/Core/Configer
DuckPhp/Core/ExceptionManager
DuckPhp/Core/Logger
DuckPhp/Core/Route
DuckPhp/Core/RuntimeState
DuckPhp/Core/SuperGlobal
DuckPhp/Core/View
DuckPhp/Ext/DBManager";
$classes=explode("\n",$classes);

dumpX($classes);

return;
function dumpX($classes)
{
    $ret=[];
    foreach($classes as $v){
            $class=str_replace("/","\\",$v);
        $options=(new $class())->options;
        foreach($options as $k =>$v){
            $k="'$k' => ".json_encode($v)."";
            if(!isset($ret[$k])){
                $ret[$k]=[];
            }
            $ret[$k][]=$class;
        }
    }
    ksort($ret);
    foreach($ret as $k =>$v){
        echo "$k,\n";
    }
}
*/
$classes="DuckPhp/App
DuckPhp/Core/App
DuckPhp/Core/AutoLoader
DuckPhp/Core/Configer
DuckPhp/Core/ExceptionManager
DuckPhp/Core/HttpServer
DuckPhp/Core/Logger
DuckPhp/Core/Route
DuckPhp/Core/RuntimeState
DuckPhp/Core/SuperGlobal
DuckPhp/Core/View
DuckPhp/Ext/CallableView
DuckPhp/Ext/DBManager
DuckPhp/Ext/DBReusePoolProxy
DuckPhp/Ext/FacadesAutoLoader
DuckPhp/Ext/JsonRpcExt
DuckPhp/Ext/Misc
DuckPhp/Ext/Pager
DuckPhp/Ext/PluginForSwooleHttpd
DuckPhp/Ext/RedisManager
DuckPhp/Ext/RedisSimpleCache
DuckPhp/Ext/RouteHookDirectoryMode
DuckPhp/Ext/RouteHookOneFileMode
DuckPhp/Ext/RouteHookRewrite
DuckPhp/Ext/RouteHookRouteMap
DuckPhp/Ext/StrictCheck
DuckPhp/HttpServer";
function classToLink($class)
{
    $name=$class;
    $name=str_replace('DuckPhp\\','',$name);
    $name=str_replace('\\','-',$name);
    return "[$class]({$name}.md)";
}
$classes=explode("\n",$classes);

dumpByClass($classes);
dumpByFile($classes);
function dumpByClass($classes)
{
    foreach($classes as $class){
        $options=(new $class())->options;
        ksort($options);
        
        echo  "+ ".classToLink($class)."\n";
        foreach($options as $k =>$v){
            echo "    - '$k' => ".json_encode($v)."\n";
        }
        echo "\n";
    }
}

function dumpByFile($classes)
{
    $ret=[];
    foreach($classes as $class){
        $options=(new $class())->options;
        foreach($options as $k =>$v){
            $k="'$k' => ".json_encode($v)."";
            if(!isset($ret[$k])){
                $ret[$k]=[];
            }
            $ret[$k][]=$class;
        }
    }
    ksort($ret);
    foreach($ret as $k =>$v){
        echo "+ $k\n";
        foreach($v as $class){
            echo "    - ".classToLink($class)."\n";
        }
        echo "\n";
    }
}