<?php
interface SwooleExtAppInterface
{
    public static function G($object=null);
    public static function OnException($ex);
    
    public function init($options=[], $context=null);
    public function run();
    public function addBeforeRunHandler();
    public function getDynamicComponentClasses();
    public function getStaticComponentClasses();
    public function system_wrapper_replace($funcs=[]);
}
