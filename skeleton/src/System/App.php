<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace YourProjectName\System;

use DuckPhp\DuckPhp;
use YourProjectName\Controller\ExceptionReporter;

class App extends DuckPhp
{
    //@override
    public $options = [
        'path' => __DIR__ . '/../../',
        //'path_info_compact_enable' => false,
        
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
		
        'exception_for_project'  => ProjectException::class,
        'exception_for_business'  => BusinessException::class,
        'exception_for_controller'  => ControllerException::class,
        'exception_reporter' =>  ExceptionReporter::class,
        //...
    ];
    //@override
    protected function onInited()
    {
        parent::onInited();
        // your code here
    }
}
