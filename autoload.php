<?php
function _dnmvcs_namespace_autoload($class) {
    $path=__DIR__.'/src/';
    $namespace='DuckPhp\\';
    if (strncmp($namespace, $class, strlen($namespace)) !== 0) {
        return false;
    }
    $file = $path . str_replace('\\', '/', substr($class, strlen($namespace))) . '.php';
    require_once $file;
    return true;
}
spl_autoload_register('_dnmvcs_namespace_autoload');
