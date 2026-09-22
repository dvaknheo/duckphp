<?php declare(strict_types=1);
/**
 * DuckPhp
 * You System Call Business Collection Here.
 */
namespace YourProjectName\Controller;

use DuckPhp\Foundation\Controller\ExceptionReporterTrait;
class AppAction extends Base
{
    use ExceptionReporterTrait;
	public function __construct()
	{
		// Must override parent to stop init;
	}
    
    public function foo()
    {
        //
    }
    /**
     * Print a "hello world" message.
     */
    public function command_hello()
    {
        echo "hello world";
    }
    public function onControllerException($ex)
    {
        //
    }
    public function onBusinessException($ex)
    {
        //
    }
}
