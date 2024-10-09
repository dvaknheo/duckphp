<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\System;

use DuckPhp\DuckPhp;
use ProjectNameTemplate\Controller\ExceptionReporter;

class PureApp extends DuckPhp
{
    //@override
    public $options = [
        'path' => __DIR__ . '/../../',
        //...
    ];
    //@override
    protected function onInited()
    {
        parent::onInited();
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
