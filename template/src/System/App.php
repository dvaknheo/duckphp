<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\System;

use DuckPhp\DuckPhp;
use ProjectNameTemplate\Controller\ExceptionReporter;
use ProjectNameTemplate\Controller\Commands;

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
        //'app' => [],
    ];
    //@override
    public function onPrepare()
    {
        // your code here
        require_once __DIR__. '/../../public/dbtest.php';
        $this->options['app']['DbTestApp']=[
            'controller_url_prefix'=>'db_test/',
        ];
        //*/
    }
    //@override
    protected function onInit()
    {
    }
    /**
     * console command sample
     */
    public function command_hello()
    {
        //TODO Move this
        echo "hello ". static::class ."\n";
    }
}
