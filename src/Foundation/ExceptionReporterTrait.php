<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Core\App;
use DuckPhp\Core\SingletonTrait;

trait ExceptionReporterTrait
{
    use SingletonTrait;
    public static function OnException($ex)
    {
        $class = get_class($ex);
        $namespace_prefix = App::_()->options['namespace'] ."\\";
        if ($namespace_prefix !== substr($class, 0, strlen($namespace_prefix))) {
            return static::_()->defaultException($ex);
        }
        $t = explode("\\", $class);
        $class = array_pop($t);
        $method = 'on'.$class;
        $object = static::_();
        if (!is_callable([$object,$method])) {
            return static::_()->defaultException($ex);
        }
        return $object->$method($ex);
    }
    protected function defaultSystemException(\Throwable $ex): void
    {
        App::_()->_OnDefaultException($ex);
    }
    public function defaultException(\Throwable $ex): void
    {
        $this->defaultSystemException($ex);
    }
}
