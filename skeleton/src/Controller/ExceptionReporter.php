<?php declare(strict_types=1);
/**
 * DuckPhp
 */
namespace YourProjectName\Controller;

use DuckPhp\Foundation\ExceptionReporterTrait;

class ExceptionReporter
{
    use ExceptionReporterTrait;
    //public function defaultException($ex)
    //{
    //return App::Current()->_OnDefaultException($ex);
    //}
    public function onBusinessException($ex)
    {
        var_dump(__METHOD__);
    }
    public static function onControllerException($ex)
    {
        var_dump(__METHOD__);
    }
}
