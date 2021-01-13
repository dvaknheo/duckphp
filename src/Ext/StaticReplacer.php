<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class StaticReplacer extends ComponentBase
{
    public $GLOBALS = [];
    public $STATICS = [];
    public $CLASS_STATICS = [];
    ///////////////////////////////
    //TODO 添加 Replace
    public function &_GLOBALS($k, $v = null)
    {
        if (!isset($this->GLOBALS[$k])) {
            $this->GLOBALS[$k] = $v;
        }
        return $this->GLOBALS[$k];
    }
    public function &_STATICS($name, $value = null, $parent = 0)
    {
        $t = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, $parent + 2)[$parent + 1] ?? [];
        $k = '';
        $k .= isset($t['object'])?'object_'.spl_object_hash($t['object']):'';
        $k .= $t['class'] ?? '';
        $k .= $t['type'] ?? '';
        $k .= $t['function'] ?? '';
        $k .= $k?'$':'';
        $k .= $name;
        
        if (!isset($this->STATICS[$k])) {
            $this->STATICS[$k] = $value;
        }
        return $this->STATICS[$k];
    }
    public function &_CLASS_STATICS($class_name, $var_name)
    {
        $k = $class_name.'::$'.$var_name;
        if (!isset($this->CLASS_STATICS[$k])) {
            $ref = new \ReflectionClass($class_name);
            $reflectedProperty = $ref->getProperty($var_name);
            $reflectedProperty->setAccessible(true);
            $this->CLASS_STATICS[$k] = $reflectedProperty->getValue();
        }
        return $this->CLASS_STATICS[$k];
    }
}
