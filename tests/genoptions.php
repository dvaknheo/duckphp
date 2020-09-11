<?php
require_once(__DIR__.'/../autoload.php');

GenOptionsGenerator::G()->init([])->run();

//以options 为核心，其他都是 key ， 只要一个 value

//echo getDefaultOptions();
//echo dumpByFile();
//dumpByFile();

// $options['handle_all_dev_error'] = true;
// $options['handle_all_exception'] = true;
// $options['route_map'] = array ( );
// $options['route_map_by_config_name'] = '';
// $options['route_map_important'] = array ( );
        
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
    public function run()
    {
        $options=$this->getAllOptions();
        echo $this->getDefaultOptionsString($options);
        echo "\n";
        echo $this->getExtOptionsString($options);
    }
    
    function getAllOptions()
    {
        $classes=getAllComponentClasses();
        
        $ret=[];
        foreach($classes as $class){
            $options=(new $class())->options;
            foreach($options as $option => $value){
                $ret[$option]=$ret[$option]??[];
                $v= &$ret[$option];
                $v['solid']=in_array($option,getKernelOptions());
                $v['is_default']=$v['is_default']??false;
                $v['is_default']=$v['is_default'] || in_array($class,getDefaultComponentClasses());

                $v['defaut_value']=$value;
                $v['class']=$v['class']??[];
                $v['class'][]=$class;
                unset($v);
            }
        }
        
        ksort($ret);
        return $ret;
    }
    protected function getDefaultOptionsString($input)
    {
        $data=[];
        foreach($input as $option => $attrs) {
            
            if($attrs['solid']){ continue; }
            if(!$attrs['is_default']){ continue; }
            $v=$attrs['defaut_value'];
            $s=var_export($v,true);
            if(is_array($v)){
                $s=str_replace("\n",' ',$s);
            }
            $data[$option]=$s;
        }
        $ret=[];
        foreach($data as $k =>$v){
            $ret[]="        // \$options['$k'] = {$v};\n            // --\n";
        }
        return implode("",$ret);
    }
    protected function getExtOptionsString()
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
function getInDependComponentClasses()
{
$classes="DuckPhp\\HttpServer
DuckPhp\\Ext\\Pager
";
    $classes=explode("\n",$classes);
    return $classes;
}
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
DuckPhp\\Ext\\Cache
DuckPhp\\Ext\\DbManager
DuckPhp\\Ext\\EventManager
DuckPhp\\Ext\\RouteHookPathInfoByGet
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
DuckPhp\\Core\\Logger
DuckPhp\\Core\\Route
DuckPhp\\Core\\RuntimeState
DuckPhp\\Core\\SuperGlobal
DuckPhp\\Core\\View
DuckPhp\\Ext\\CallableView
DuckPhp\\Ext\\Cache
DuckPhp\\Ext\\DBManager
DuckPhp\\Ext\\EmptyView
DuckPhp\\Ext\\EventManager
DuckPhp\\Ext\\FacadesAutoLoader
DuckPhp\\Ext\\JsonRpcExt
DuckPhp\\Ext\\Misc
DuckPhp\\Ext\\RedisCache
DuckPhp\\Ext\\RedisManager
DuckPhp\\Ext\\RouteHookApiServer
DuckPhp\\Ext\\RouteHookDirectoryMode
DuckPhp\\Ext\\RouteHookPathInfoByGet
DuckPhp\\Ext\\RouteHookRewrite
DuckPhp\\Ext\\RouteHookRouteMap
DuckPhp\\Ext\\StrictCheck";
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



