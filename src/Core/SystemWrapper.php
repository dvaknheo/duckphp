<?php declare(strict_types=1);
namespace DNMVCS\Core;

trait SystemWrapper
{
    // protected $system_handlers=[];
    public static function system_wrapper_replace(array $funcs)
    {
        static::G()->system_handlers=array_replace(static::G()->system_handlers, $funcs)??[];
        return true;
    }
    public static function system_wrapper_get_providers():array
    {
        $ret=static::G()->system_handlers;
        
        $class=static::class;
        foreach ($ret as $k =>&$v) {
            $v=$v??[$class,$k];
        }
        unset($v);
        return $ret;
    }
    protected function system_wrapper_call_check($func)
    {
        $func=ltrim($func, '_');
        return isset($this->system_handlers[$func])?true:false;
    }
    protected function system_wrapper_call($func, $input_args)
    {
        $func=ltrim($func, '_');
        if (is_callable($this->system_handlers[$func]??null)) {
            return ($this->system_handlers[$func])(...$input_args);
        }
        if (!is_callable($func)) {
            throw new \Error("Call to undefined function $func");
        }
        return ($func)(...$input_args);
    }
}
