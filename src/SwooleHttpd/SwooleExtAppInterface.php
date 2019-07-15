<?php
interface SwooleExtAppInterface
{
    public static function G($object=null);
    public static function OnException($ex);
    
    public function init($options=[], $context=null);
    public function run();
    public function system_wrapper_replace($funcs=[]);
//
//($this->appClass)::G()->options['error_404']=[static::class,'_EmptyFunction']; // do not double 404;
//($this->appClass)::G()->options['use_super_global']=true;
//
    
    public function addBeforeRunHandler($handler);
    public function getDynamicComponentClasses();
    public function getStaticComponentClasses();
}
