<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class Console extends ComponentBase
{
    public $options = [
        'console_mode' => 'replace',
        //'default'
    ];
    
    //@override
    protected function initContext(object $context)
    {
        if (PHP_SAPI !== 'cli') {
            return $this; // @codeCoverageIgnore
        }
        $context->replaceDefaultRunHandler([static::class,'OnRun']);
    }
    public static function OnRun()
    {
        return static::G()->run();
    }
    public function run()
    {
        $argv=$_SERVER['argv'];
        $cli = array_shift($argv);
        
        $parameters = $this->parseArgs($argv);
        $func_args = $parameters['command'];
        if(!is_array($func_args)){
            $class=ConsoleCommand::class;
            $method = $func_args?$func_args:'help';
            $func_args=[];
        }else{
            $cmd = array_shift($func_args);
            @list($class,$method)=explode(':', $cmd);
            if(empty($method)){
                $method = $class;
                $class = ConsoleCommand::class;
            }
        }
        
        $object=$this->getObject($class);
        
        if(isset($parameters['help'])){
            $method='help_'.$method;
            if(!is_callable($class,$method)){
                $method='help';
            }
        }
        $this->callObject($object, $method, $func_args, $parameters);
        
        return true;
    }
    protected function parseArgs($argv)
    {
        $ret=[];
        $lastkey='command';
        foreach($argv as $v){
            if (substr($v,0,2)==='--') {
                if(!isset($ret[$lastkey])){
                    $ret[$lastkey]=true;
                }
                $lastkey=substr($v,2);
            }else if(!isset($ret[$lastkey])){
                $ret[$lastkey]=$v;
            }else if(is_array($ret[$latkey])){
                $ret[$lastkey][]=$v;
            }else{
                $t=$ret[$lastkey];
                $t=is_array($t)?$t:[$t];
                $t[] = $v;
                $ret[$lastkey]=$t;
            }
        }
        if(!isset($ret[$lastkey])){
            $ret[$lastkey]=true;
        }
        $ret['parameters']=$ret;
        return $ret;
    }
    
    protected function getObject($class)
    {
        if(is_callable([$class,'G'])){
            return $class::G();
        }
        return new $class();
    }
    protected function callObject($object, $method, $args, $input)
    {        
        $reflect = new \ReflectionMethod($object, $method);
        $params = $reflect->getParameters();
        foreach ($params as $i => $param) {
            $name = $param->getName();
            if (isset($input[$name])) {
                $args[$i] = $input[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[$i] = $param->getDefaultValue();
            } else {
                throw new \ReflectionException("Need Parameter: {$name}", -2);
            }
        }
        
        $ret = $reflect->invokeArgs($object, $args);
        return $ret;
    }
}