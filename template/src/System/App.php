<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\System;

use DuckPhp\DuckPhp;
use ProjectNameTemplate\Controller\ExceptionReporter;

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
        parent::onPrepare();
        // your code here
        // this is show use DbTestApp as a child app
        require_once __DIR__. '/../../public/dbtest.php';
        $this->options['app']['DbTestApp']=[
            'controller_url_prefix'=>'db_test/',
        ];
        //*/
    }
    //@override
    protected function onInited()
    {
        parent::Inited();
        // your code here
    }
    /**
     * console command sample
     */
    public function command_hello()
    {
        //this is show a command `hello`
        echo "hello ". static::class ."\n";
    }
}
