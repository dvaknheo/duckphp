<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;
use ReflectionMethod;
use ReflectionException;

class Misc
{
    use SingletonEx;
    
    const DEFAULT_OPTIONS=[
        'path'=>'',
        'path_lib'=>'lib',
    ];
    protected $path=null;
    protected $context_class;
    protected $_di_container;
    
    public function __construct()
    {
    }
    public function init($options=[], $context=null)
    {
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, $options);
        if (substr($options['path_lib'], 0, 1)==='/') {
            $this->path=rtrim($options['path_lib'], '/').'/';
        } else {
            $this->path=$options['path'].rtrim($options['path_lib'], '/').'/';
        }
        $this->context_class=$context?get_class($context):null;
        
        //$this->context_class::G()->extendComponents(static::class,'Foo',['C']);
    }
    public static function Import($file)
    {
        return static::G()->_Import($file);
    }
    public static function RecordsetUrl(&$data, $cols_map=[])
    {
        return static::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH(&$data, $cols=[])
    {
        return static::G()->_RecordsetH($data, $cols);
    }
    public static function DI($name, $object=null)
    {
        return static::G()->_DI($name, $object);
    }
    public function _DI($name, $object=null)
    {
        if (null===$object) {
            return $this->_di_container[$name]??null;
        }
        $this->_di_container[$name]=$object;
        return $object;
    }
    public function _Import($file)
    {
        include_once $this->path.rtrim($file, '.php').'.php';
    }
    
    public function _RecordsetUrl(&$data, $cols_map=[])
    {
        //need more quickly;
        if ($data===[]) {
            return $data;
        }
        if ($cols_map===[]) {
            return $data;
        }
        $keys=array_keys($data[0]);
        array_walk(
            $keys,
            function (&$val, $k) {
                $val='{'.$val.'}';
            }
        );
        foreach ($data as &$v) {
            foreach ($cols_map as $k=>$r) {
                $values=array_values($v);
                $changed_value=str_replace($keys, $values, $r);
                $v[$k]=$this->context_class::URL($changed_value);
            }
        }
        unset($v);
        return $data;
    }
    public function _RecordsetH(&$data, $cols=[])
    {
        if ($data===[]) {
            return $data;
        }
        $cols=is_array($cols)?$cols:array($cols);
        if ($cols===[]) {
            $cols=array_keys($data[0]);
        }
        foreach ($data as &$v) {
            foreach ($cols as $k) {
                $v[$k]=$this->context_class::H($v[$k], ENT_QUOTES);
            }
        }
        return $data;
    }
    public function callAPI($class, $method, $input)
    {
        $f=[
            'bool'=>FILTER_VALIDATE_BOOLEAN  ,
            'int'=>FILTER_VALIDATE_INT,
            'float'=>FILTER_VALIDATE_FLOAT,
            'string'=>FILTER_SANITIZE_STRING,
        ];
        $reflect = new ReflectionMethod($class, $method);
        
        $params=$reflect->getParameters();
        $args=array();
        foreach ($params as $i => $param) {
            $name=$param->getName();
            if (isset($input[$name])) {
                $type=$param->getType();
                if (null!==$type) {
                    $type=''.$type;
                    if (in_array($type, array_keys($f))) {
                        $flag=filter_var($input[$name], $f[$type], FILTER_NULL_ON_FAILURE);
                        if ($flag===null) {
                            throw new ReflectionException("Type Unmatch: {$name}", -1); //throw
                        }
                    }
                }
                $args[]=$input[$name];
                continue;
            } elseif ($param->isDefaultValueAvailable()) {
                $args[]=$param->getDefaultValue();
            } else {
                throw new ReflectionException("Need Parameter: {$name}", -2);
            }
        }
        
        $ret=$reflect->invokeArgs(new $class(), $args);
        return $ret;
    }
    public function MapToService($serviceClass, $input)
    {
        try {
            $method=static::G()->context_class::G()->getRouteCallingMethod();
            $data=static::G()->context_class::G()->callAPI($serviceClass, $method, $input);
            if (!is_array($data) && !is_object($data)) {
                $data=['result'=>$data];
            }
        } catch (\Throwable $ex) {
            $data=[];
            $data['error_message']=$ex->getMessage();
            $data['error_code']=$ex->getCode();
        }
        static::G()->context_class::ExitJson($data);
    }
    public function explodeService($object, $namespace=null)
    {
        $namespace=$namespace??'MY\\Service';
        $vars=array_keys(get_object_vars($object));
        $l=strlen('Service');
        foreach ($vars as $v) {
            if (substr($v, 0-$l)!=='Service') {
                continue;
            }
            $name=ucfirst($v);
            $class=$namespace.$name;
            if (class_exists($class)) {
                $object->$v=$class::G();
            }
        }
    }
}
