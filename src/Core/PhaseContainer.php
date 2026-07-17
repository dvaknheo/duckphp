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
    public $current = '';
    public $default = '';
    public $publics = [];
    
    public static function GetObject(string $class, ?object $object = null)
    {
        return static::_()->_GetObject($class, $object);
    }
    public static function _(?object $object = null)
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
    public static function RestAllContainerForTesting()
    {
        static::_(new static());
    }
    public static function Dump()
    {
        static::_()->dumpAllObject();
    }
    ////////////////////////////////
    public function _GetObject(string $class, ?object $object = null): object
    {
        $ret = $this->getObjectInContainer($this->current, $class, $object);
        if ($ret) {
            return $ret;
        }

        $container_name = $this->current;
        if (isset($this->publics[$class])) {
            $container_name = $this->default;
            $ret = $this->getObjectInContainer($container_name, $class, $object);
            if ($ret) {
                return $ret;
            }
        }
        return $this->createObjectToContainer($container_name, $class, $object);
    }
    protected function getObjectInContainer($container_name, $class, $object)
    {
        if (isset($this->containers[$container_name][$class])) {
            if ($object) {
                $this->containers[$container_name][$class] = $object;
            }
            return $this->containers[$container_name][$class];
        }
        return null;
    }
    protected function createObjectToContainer($container_name, $class, $object)
    {
        $result = $object ?? $this->createObject($class);
        $this->containers[$container_name][$class] = $result;
        return $result;
    }
    protected function createObject(string $class): object
    {
        return new $class;
    }
    ///////////////
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
    public function issetContainer($phase)
    {
        return isset($this->containers[$phase]);
    }
    public function createLocalObject($class, $object = null)
    {
        $result = $object ?? $this->createObject($class);
        $this->containers[$this->current][$class] = $result;
        return $result;
    }
    public function removeLocalObject($class)
    {
        unset($this->containers[$this->current][$class]);
    }
    public function getClassOfContainer($class, $phase = '')
    {
        return $this->containers[$phase][$class] ?? null;
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
                } else {
                    echo " ";
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
        echo "\n        * is public";
        echo "\n--end--- </pre> \n";
    }
}
