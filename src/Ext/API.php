<?php
class API
{
    protected static function GetTypeFilter()
    {
        return [
            'boolean'=>FILTER_VALIDATE_BOOLEAN  ,
            'bool'=>FILTER_VALIDATE_BOOLEAN  ,
            'int'=>FILTER_VALIDATE_INT,
            'float'=>FILTER_VALIDATE_FLOAT,
            'string'=>FILTER_SANITIZE_STRING,
        ];
    }
    public static function Call($class, $method, $input)
    {
        $f=self::GetTypeFilter();
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
                        if($flag===null){
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
        
        $ret=$reflect->invokeArgs(new $service(), $args);
        return $ret;
    }
}