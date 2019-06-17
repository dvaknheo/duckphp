<?php
interface SwooleExtServerInterface
{
    public static function G($object=null);
    public static function ReplaceDefaultSingletonHandler();
    public static function set_exception_handler(callable $callback);
    
    public function init($options=[], $context=null);
    public function run();
    public function getDynamicClasses();
    public function resetInstances();
    public function forkMasterInstances();
}
