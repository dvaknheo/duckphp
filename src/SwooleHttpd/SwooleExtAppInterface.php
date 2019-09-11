<?php
namespace DNMVCS\SwooleHttpd;

interface SwooleExtAppInterface
{
    public static function G($object=null);
    
    //public static function OnException($ex);
    //public static function On404();
    //public function system_wrapper_replace($funcs=[]);
    
    public function init($options=[], $context=null);
    public function run();
    
    public function onSwooleHttpdInit($SwooleHttpd);
    
    public function addBeforeRunHandler($handler); // TODO kill
    public function getDynamicComponentClasses();
    public function getStaticComponentClasses();
}
