<?php
namespace DNMVCS\SwooleHttpd;

interface SwooleExtServerInterface
{
    public static function G($object=null);
    public static function ReplaceDefaultSingletonHandler();
    
    public function init($options=[], $context=null);
    public function run();
    
    public function is_with_http_handler_root();
    public function system_wrapper_get_providers();
    public function set_http_exception_handler(callable $callback);
    public function set_http_404_handler(callable $callback);
    
    public function getDynamicComponentClasses();
    public function resetInstances();
    public function forkMasterInstances();
}
