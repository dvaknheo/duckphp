<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\Controller\ExceptionReporterTrait;
use DuckPhp\Foundation\SingletonTrait;

class ExceptionAction
{
    use SingletonTrait;
    use ExceptionReporterTrait;
    public function onBusinessException($ex)
    {
        var_dump(__METHOD__);
    }
    public static function onControllerException($ex)
    {
        var_dump(__METHOD__);
    }
}
