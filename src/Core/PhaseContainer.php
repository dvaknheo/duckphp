<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class PhaseContainer
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
            static::GetContainerInstanceEx()->default = static::class;
            static::GetContainerInstanceEx()->current = static::class;
            static::GetContainerInstanceEx()->publics[static::class] = true;
            return true;
        }
        return false;
    }
    public static function GetObject($class, $object = null)
    {
        return static::GetContainerInstanceEx()->_GetObject($class, $object);
    }
    public static function GetContainerInstanceEx($object = null)
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
    public static function GetContainer()
    {
        if (!defined('__SINGLETONEX_REPALACER_CLASS')) {
            return null;
        }
        $class = __SINGLETONEX_REPALACER_CLASS;
        return $class::GetContainerInstanceEx();
    }
    ////////////////////////////////
    public function _GetObject(string $class, $object = null)
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
        $result = $object ?? $this->createObject($class);
        $this->containers[$this->current][$class] = $result;
        return $result;
    }
    protected function createObject($class)
    {
        return new $class;
    }
    public function setDefaultContainer($class)
    {
        $this->default = $class;
    }
    public function addPublicClasses($classes)
    {
        foreach ($classes as $class) {
            $this->publics[$class] = true;
        }
    }
    public function removePublicClasses($classes)
    {
        foreach ($classes as $class) {
            unset($this->publics[$class]);
        }
    }
    public function setCurrentContainer($container)
    {
        $this->current = $container;
    }
    public function getCurrentContainer()
    {
        return $this->current;
    }
    public function dumpAllObject()
    {
        echo "-- begin dump---<pre> \n";
        echo "current:{$this->current};\n";
        echo "default:{$this->default};\n";
        echo "publics:\n";
        foreach ($this->publics as $k => $null) {
            echo "    $k;\n";
        }
        echo "contains:\n";
        foreach ($this->containers as $name => $container) {
            echo "    $name: \n";
            foreach ($container as $k => $v) {
                echo "        ";
                if (isset($this->publics[$k])) {
                    echo "*";
                }
                $c = $v?get_class($v):null;
                echo($v?md5(spl_object_hash($v)) :'NULL');
                echo ' '.$k;
                if ($c !== $k) {
                    echo " ($c)";
                }
                echo " ;\n";
            }
        }
        echo "\n --end--- </pre> \n";
    }
}
