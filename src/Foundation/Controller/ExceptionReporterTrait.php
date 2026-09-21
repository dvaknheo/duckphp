<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation\Controller;

use DuckPhp\Core\App;
use DuckPhp\Core\SingletonExTrait;

trait ExceptionReporterTrait
{
    use SingletonExTrait;
    public static function OnException(\Throwable $ex)
    {
        return static::_()->_OnException($ex);
    }
    public function _OnException($ex)
    {
        $class = get_class($ex);
        $t = explode("\\", $class);
        $class_basename = array_pop($t);
        $method = 'on'.$class_basename;
        // PHP method names are case-insensitive, avoid infinite recursion
        if (strtolower($method) === strtolower('OnException') || strtolower($method) === strtolower('_OnException')) {
            return App::_()->_OnDefaultException($ex);
        }
        if (!is_callable([$this,$method])) {
            return App::_()->_OnDefaultException($ex);
        }
        return $this->$method($ex);
    }
}
