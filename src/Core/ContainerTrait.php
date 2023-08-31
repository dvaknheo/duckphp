<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

trait ContainerTrait
{
    public static $instance;
    
    public $containers = [];
    public $current;
    public $default;
    public $publics;
    
    public static function ReplaceSingletonImplement()
    {
        if (!defined('__SINGLETONEX_REPALACER')) {
            define('__SINGLETONEX_REPALACER', static::class . '::GetObject');
            define('__SINGLETONEX_REPALACER_CLASS', static::class);
            static::ContainerInstance()->default = static::class;
            static::ContainerInstance()->current = static::class;
            static::ContainerInstance()->publics[static::class] = true;
            return true;
        }
        return false;
    }
    public static function GetObject($class, $object = null)
    {
        return static::ContainerInstance()->_GetObject($class, $object);
    }
    public static function ContainerInstance($object = null)
    {
        if ($object) {
            static::$instance = $object;
            return $object;
        }
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    public static function GetCurrentContainer()
    {
        return static::ContainerInstance()->_GetCurrentContainer();
    }
    public static function SetCurrentContainer($container)
    {
        return static::ContainerInstance()->_SetCurrentContainer($container);
    }
    public static function SetDefaultContainer($class)
    {
        return static::ContainerInstance()->_SetDefaultContainer($class);
    }
    public static function AddPublicClasses($classes)
    {
        return static::ContainerInstance()->_AddPublicClasses($classes);
    }
    public static function RemovePublicClasses($classes)
    {
        return static::ContainerInstance()->_RemovePublicClasses($classes);
    }
    public static function DumpAllObject()
    {
        return static::ContainerInstance()->_DumpAllObject();
    }
    ////////////////////////////////
    public function _GetObject($class, $object = null)
    {
        if (isset($this->containers[$this->current][$class])) {
            if ($object) {
                $this->containers[$this->current][$class] = $object;
            }
            return $this->containers[$this->current][$class];
        }
        if (isset($this->publics[$class])) {
            if (isset($this->containers[$this->default][$class])) {
                if ($object) {
                    $this->containers[$this->default][$class] = $object;
                }
                return $this->containers[$this->default][$class];
            }
            $result = $object ?? $this->createObject($class);
            $this->containers[$this->default][$class] = $result;
            return $result;
        }
        $result = $object ?? new $class;
        $this->containers[$this->current][$class] = $result;
        return $result;
    }
    protected function createObject($class)
    {
        return new $class;
    }
    public function _SetDefaultContainer($class)
    {
        $this->default = $class;
    }
    public function _AddPublicClasses($classes)
    {
        foreach ($classes as $class) {
            $this->publics[$class] = true;
        }
    }
    public function _RemovePublicClasses($classes)
    {
        foreach ($classes as $class) {
            unset($this->publics[$class]);
        }
    }
    public function _SetCurrentContainer($container)
    {
        $this->current = $container;
    }
    public function _DumpAllObject()
    {
        var_dump($this->default);
        var_dump($this->current);
        var_dump($this->publics);
        var_dump($this->containers);
    }
}
