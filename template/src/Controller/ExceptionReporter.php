<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\ExceptionReportTrait;

class ExceptionReport
{
    use ExceptionReportTrait;
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
