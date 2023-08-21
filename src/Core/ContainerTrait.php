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
            static::ContainerInstance()->shared[static::class] = true;
            return true;
        }
        return false;
    }
    public static function GetObject($class, $object = null)
    {
        return static::ContainerInstance()->GetObject($class, $object);
    }
    public static function ContainerInstance()
    {
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    public static function SetDefaultContainer($class)
    {
        return static::ContainerInstance()->_SetDefaultContainer($class);
    }
    public static function AddPublicClasses($classes)
    {
        return static::ContainerInstance()->_AddPublicClasses($classes);
    }
    public static function SwitchContainer($container)
    {
        return static::ContainerInstance()->_SwitchContainer($container);
    }
    public static function DumpAllObject()
    {
        return static::ContainerInstance()->_DumpAllObject();
    }
    ////////////////////////////////
    public function _GetObject($class, $object = null)
    {
        if (isset($containers[$this->current][$class])) {
            if ($object) {
                $containers[$this->current][$class] = $object;
            }
            return $containers[$this->current][$class];
        }
        if (isset($this->publics[$class])) {
            if (isset($this->containers[$this->default][$class])) {
                if ($object) {
                    $this->containers[$this->default][$class] = $object;
                }
                return $this->containers[$this->default][$class];
            }
            $result = $object ?? new $class;
            $this->containers[$this->default][$class] = $result;
            return $result;
        }
        $result = $object ?? new $class;
        $this->containers[$this->current][$class] = $result;
        return $result;
    }
    public function _SetDefaultContainer($class)
    {
        $this->default = $class;
    }
    public static function _AddPublicClasses($classes)
    {
        foreach ($classes as $class) {
            $this->publics[$class] = true;
        }
    }
    public static function _SwitchContainer($container)
    {
        static::ReplaceSingletonImplement();
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
