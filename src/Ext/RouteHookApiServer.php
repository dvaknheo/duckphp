<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class RouteHookApiServer extends ComponentBase
{
    public $options = [
        'api_class_base' => 'BaseApi',
        'api_class_prefix' => 'Api_',
        'api_config_file' => '',
    ];
    protected $context_class;

    //@override
    protected function initContext(object $context)
    {
        $context::addRouteHook([static::class,'Hook'], 'prepend-inner');
    }
    public static function Hook($path_info)
    {
        return static::G()->_Hook($path_info);
    }
    public function _Hook($path_info)
    {
        ($this->context_class)::setDefaultExceptionHandler([static::class,'OnJsonError']);
        
        list($class,$method) = $this->getClassAndMethod();
        $inputs = $this->getInputs();
        
        $object = new $class;        
        $data = $this->callAPI($object, $method, $inputs, $this->options['api_class_base']);
        $this->exitJson($data);
        
        return true;
    }
    public static function OnJsonError($e)
    {
        return static::G()->_OnJsonError($e);
    }
    protected function _OnJsonError($e)
    {
        $this->exitJson([
            'error_code' => $e->getCode(),
            'error_message' => $e->getMessage(),
        ]);
    }
    protected function getClassAndMethod()
    {
        $path_info = trim($path_info, '/');
        $class_array = explode('.', $path_info);
        $method = array_pop($class_array);

        $class = implode('/', $class_array);
        $class = $this->options['api_class_prefix'] . $class;
        return [$class,$method];
    }
    
    protected function getInputs()
    {
        if (($this->context_class)::IsDebug()) {
            $inputs = ($this->context_class)::SuperGlobal()->_REQUEST;
        } else {
            $inputs = ($this->context_class)::SuperGlobal()->_POST;
        }
        return $inputs;
    }
    protected function exitJson($ret, $exit = true)
    {
        ($this->context_class)::header('Access-Control-Allow-Origin: *');
        ($this->context_class)::header('Access-Control-Allow-Headers: Authori-zation,Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
        ($this->context_class)::header('Access-Control-Allow-Methods: GET,POST,PATCH,PUT,DELETE,OPTIONS,DELETE');
        ($this->context_class)::header('Access-Control-Max-Age: 1728000');
        ($this->context_class)::header('Content-Type:text/json');
        
        $flag = JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK;
        if ($this->context_class::IsDebug()) {
            $flag = $flag | JSON_PRETTY_PRINT;
        }
        echo json_encode($ret, $flag);
    }
    protected function callAPI($class, $method, $input, $interface = '')
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
                if (null === $type) {
                    $args[] = $input[$name];
                    continue;
                }
                if (in_array((string)$type, array_keys($f))) {
                    $flag = filter_var($input[$name], $f[(string)$type], FILTER_NULL_ON_FAILURE);
                    if ($flag === null) {
                        throw new ReflectionException("Type Unmatch: {$name}", -1);
                    }
                }
                $args[] = $input[$name];
                continue;
            }
            if (!$param->isDefaultValueAvailable()) {
                throw new ReflectionException("Need Parameter: {$name}", -2);
                
            }
            $args[] = $param->getDefaultValue();
        }
        
        $ret = $reflect->invokeArgs(new $class(), $args);
        return $ret;
    }
}
