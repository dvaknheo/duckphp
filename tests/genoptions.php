<?php
require_once(__DIR__.'/../autoload.php');

$t = GenOptionsGenerator::G()->init([])->fetch();
var_dump($t);

//以options 为核心，其他都是 key ， 只要一个 value

//echo getDefaultOptions();
//echo dumpByFile();
//dumpByFile();

return;
class GenOptionsGenerator
{
    public static function G($object=null)
    {
        static $_instance;
        $_instance=$object?:($_instance??new static);
        return $_instance;
    }
    public function init(array $options, ?object $context = null)
    {
        return $this;
    }
    public function fetch()
    {
        $options=$this->getAllOptions();
    }
    function getAllOptions()
    {
        $classes=getAllComponentClasses();
        
        $ret=[];
        foreach($classes as $class){
            $options=(new $class())->options;
            foreach($options as $option){
                $ret[$option]=true;
            }
        }
        ksort($ret);
        return $ret;
    }
    
}
/**
$option[
    'value',
    'class',
    'is_default',
    
    ]
*/

function getDefaultOptions()
{
    $classes=getDefaultComponentClasses();
    $classes=explode("\n",$classes);
    $ret=[];
    foreach($classes as $class){
        $options=(new $class())->options;
        foreach($options as $k =>$v){
            $s=var_export($v,true);
            if(is_array($v)){
                $s=str_replace("\n",' ',$s);
            }
            $k="        // \$options['$k'] = ".$s.";";
            if(!isset($ret[$k])){
                $ret[$k]=[];
            }
            $ret[$k][]=$class;
        }
    }
    ksort($ret);
    return implode("\n",array_keys($ret))."\n";
}
function GetAviableExtentions()
{
    $classes=getAviableExtClasses();

    foreach($classes as $class){
        $options=(new $class())->options;
        ksort($options);
        echo "        /*\n";
        echo "        \$options['ext'][".var_export($class,true)."] = true;\n";
        foreach($options as $k =>$v){
            echo "            \$options[".var_export($k,true)."]=".var_export($v,true).";\n";
        }
        echo "        //*/\n";
    }
}



function classToLink($class)
{
    $name=$class;
    $name=str_replace('DuckPhp\\','',$name);
    $name=str_replace('\\','-',$name);
    return "[$class]({$name}.md)";
}
$classes=explode("\n",$classes);

function dumpByClass()
{
    $classes = getAllComponentClasses();
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

function dumpByFile()
{
    $classes=getAllComponentClasses();
    
    $ret=[];
    foreach($classes as $class){
        $options=(new $class())->options;
        foreach($options as $k =>$v){
            $k="'$k' => ".var_export($v,true)."";
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
/////////////////////
function getDefaultComponentClasses()
{
    $classes="DuckPhp\\DuckPhp
DuckPhp\\Core\\AutoLoader
DuckPhp\\Core\\Configer
DuckPhp\\Core\\Logger
DuckPhp\\Core\\Route
DuckPhp\\Core\\RuntimeState
DuckPhp\\Core\\SuperGlobal
DuckPhp\\Core\\View
DuckPhp\\Ext\\DBManager
DuckPhp\\Ext\\RouteHookRouteMap";
    $classes=explode("\n",$classes);
    return $classes;
}
function getAllComponentClasses()
{
$classes="DuckPhp\\DuckPhp
DuckPhp\\Core\\App
DuckPhp\\Core\\AutoLoader
DuckPhp\\Core\\Configer
DuckPhp\\Core\\ExceptionManager
DuckPhp\\Core\\HttpServer
DuckPhp\\Core\\Logger
DuckPhp\\Core\\Route
DuckPhp\\Core\\RuntimeState
DuckPhp\\Core\\SuperGlobal
DuckPhp\\Core\\View
DuckPhp\\Ext\\CallableView
DuckPhp\\Ext\\EmptyView
DuckPhp\\Ext\\DBManager
DuckPhp\\Ext\\DBReusePoolProxy
DuckPhp\\Ext\\FacadesAutoLoader
DuckPhp\\Ext\\JsonRpcExt
DuckPhp\\Ext\\Misc
DuckPhp\\Ext\\PluginForSwooleHttpd
DuckPhp\\Ext\\RedisManager
DuckPhp\\Ext\\RedisSimpleCache
DuckPhp\\Ext\\RouteHookDirectoryMode
DuckPhp\\Ext\\RouteHookOneFileMode
DuckPhp\\Ext\\RouteHookRewrite
DuckPhp\\Ext\\RouteHookRouteMap
DuckPhp\\Ext\\StrictCheck
DuckPhp\\HttpServer";
    $classes=explode("\n",$classes);
    return $classes;
}
function getAviableExtClasses()
{
    $default=getDefaultComponentClasses();
    $all=getAllComponentClasses();
    $ext=array_diff($all,$default);
    $ext=array_filter($ext,function($v){if(substr($v,0,strlen("DuckPhp\\Ext\\"))==="DuckPhp\\Ext\\"){return true;}else{return false;}});
    return $ext;
}

function getAllOptionDeclareClasses()
{
    $classes=getAllComponentClasses();
    
    $ret=[];
    foreach($classes as $class){
        $options=(new $class())->options;
        foreach($options as $k =>$v){
            if(!isset($ret[$k])){
                $ret[$k]=[];
            }
            $ret[$k][$class]=$v;
        }
    }
    ksort($ret);
    return $ret;
}

function getKernelOptions()
{
    return [
        'use_autoloader',
        'skip_plugin_mode_check',
        'handle_all_dev_error',
        'handle_all_exception',
        'override_class',
        'path_namespace',
    ];
}



