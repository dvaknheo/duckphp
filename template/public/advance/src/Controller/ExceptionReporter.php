<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace AdvanceDemo\Controller;

use DuckPhp\Foundation\ExceptionReportTrait;

class ExceptionReport
{
    use ExceptionReportTrait;
    //public function defaultException($ex)
    //{
        //return parent::defaultException($ex);
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