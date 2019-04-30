<?php
namespace DNMVCS\Core\Glue;

use DNMVCS\Core\ExceptionManager;

trait GlueExceptionManager
{
    //exception manager
    public function assignExceptionHandler($classes, $callback=null)
    {
        return ExceptionManager::G()->assignExceptionHandler($classes, $callback);
    }
    public function setMultiExceptionHandler(array $classes, $callback)
    {
        return ExceptionManager::G()->setMultiExceptionHandler($classes, $callback);
    }
    public function setDefaultExceptionHandler($callback)
    {
        return ExceptionManager::G()->setDefaultExceptionHandler($callback);
    }
}
