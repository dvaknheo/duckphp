<?php
namespace DNMVCS\SwooleHttpd;

interface SwooleExtServerInterface
{
    public static function G($object=null);
    public static function SG($replacement_object=null);
    public static function ReplaceDefaultSingletonHandler();
    public static function system_wrapper_get_providers();
    
    public function init(array $options, $server=null);
    public function run();
    
    public function is_with_http_handler_root();
    public function set_http_exception_handler(callable $callback);
    public function set_http_404_handler(callable $callback);
    
    public function getDynamicComponentClasses();
    public function forkMasterClassesToNewInstances();
    public function forkMasterInstances($classes, $exclude_classes=[]);
}
