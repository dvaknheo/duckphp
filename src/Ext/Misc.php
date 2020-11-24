<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use ReflectionException;
use ReflectionMethod;

class Misc extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_lib' => 'lib',
        'misc_auto_method_extend' => true,
    ];
    protected $path = null;
    protected $context_class;
    protected $_di_container;

    //@override ComponentBase
    protected function initOptions(array $options)
    {
        if (substr($this->options['path_lib'], 0, 1) === '/') {
            $this->path = rtrim($this->options['path_lib'], '/').'/';
        } else {
            $this->path = $this->options['path'].rtrim($this->options['path_lib'], '/').'/';
        }
    }
    //@override ComponentBase
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        
        if ($this->options['misc_auto_method_extend'] && \method_exists($context, 'extendComponents')) {
            $context->extendComponents(
                [
                    'Import' => [static::class,'Import'],
                    'DI' => [static::class,'DI'],
                ],
                ['A']
            );
            $context->extendComponents(
                [
                    'RecordsetUrl' => [static::class,'RecordsetUrl'],
                    'RecordsetH' => [static::class,'RecordsetH'],
                    'CallAPI' => [static::class,'CallAPI'],
                ],
                ['C','A']
            );
        }
    }

    public static function Import($file)
    {
        return static::G()->_Import($file);
    }
    public static function RecordsetUrl($data, $cols_map = [])
    {
        return static::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH($data, $cols = [])
    {
        return static::G()->_RecordsetH($data, $cols);
    }
    public static function DI($name, $object = null)
    {
        return static::G()->_DI($name, $object);
    }
    public function CallAPI($class, $method, $input, $interface = '')
    {
        return static::G()->_CallAPI($class, $method, $input, $interface);
    }
    public function _DI($name, $object = null)
    {
        if (null === $object) {
            return $this->_di_container[$name] ?? null;
        }
        $this->_di_container[$name] = $object;
        return $object;
    }
    public function _Import($file)
    {
        $file = preg_replace('/\.php$/', '', $file).'.php';
        include_once $this->path.$file;
    }
    
    public function _RecordsetUrl($data, $cols_map = [])
    {
        //need more quickly;
        if ($data === []) {
            return $data;
        }
        if ($cols_map === []) {
            return $data;
        }
        $keys = array_keys($data[0]);
        array_walk(
            $keys,
            function (&$val, $k) {
                $val = '{'.$val.'}';
            }
        );
        foreach ($data as &$v) {
            foreach ($cols_map as $k => $r) {
                $values = array_values($v);
                $changed_value = str_replace($keys, $values, $r);
                $v[$k] = $this->context_class::Url($changed_value);
            }
        }
        unset($v);
        return $data;
    }
    public function _RecordsetH($data, $cols = [])
    {
        if ($data === []) {
            return $data;
        }
        $cols = is_array($cols)?$cols:array($cols);
        if ($cols === []) {
            $cols = array_keys($data[0]);
        }
        foreach ($data as &$v) {
            foreach ($cols as $k) {
                $v[$k] = $this->context_class::H($v[$k], ENT_QUOTES);
            }
        }
        return $data;
    }
    public function _CallAPI($class, $method, $input, $interface = '')
    {
        $f = [
            'bool' => FILTER_VALIDATE_BOOLEAN  ,
            'int' => FILTER_VALIDATE_INT,
            'float' => FILTER_VALIDATE_FLOAT,
            'string' => FILTER_SANITIZE_STRING,
        ];
        if ($interface && !is_a($class, $interface)) {
            throw new ReflectionException("Bad interface", -3);
        }
        $reflect = new ReflectionMethod($class, $method);
        
        $params = $reflect->getParameters();
        $args = array();
        foreach ($params as $i => $param) {
            $name = $param->getName();
            if (isset($input[$name])) {
                $type = $param->getType();
                if (null !== $type) {
                    $type = ''.$type;
                    if (in_array($type, array_keys($f))) {
                        $flag = filter_var($input[$name], $f[$type], FILTER_NULL_ON_FAILURE);
                        if ($flag === null) {
                            throw new ReflectionException("Type Unmatch: {$name}", -1); //throw
                        }
                    }
                }
                $args[] = $input[$name];
                continue;
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new ReflectionException("Need Parameter: {$name}", -2);
            }
        }
        
        $ret = $reflect->invokeArgs(new $class(), $args);
        return $ret;
    }
}
