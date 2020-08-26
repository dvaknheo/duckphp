<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class ExceptionWrapper extends ComponentBase
{
    protected $object;
    
    public static function Wrap($object)
    {
        return static::G()->doWrap($object);
    }
    public static function Release()
    {
        return static::G()->doRelease();
    }
    public function doWrap($object)
    {
        $this->object = $object;
        return $this;
    }
    public function doRelease()
    {
        $object = $this->object;
        $this->object = null;
        return $object;
    }
    public function __call($method, $args)
    {
        try {
            /** @var mixed */
            $caller = [$this->object,$method];
            return ($caller)(...$args); //phpstan
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}
