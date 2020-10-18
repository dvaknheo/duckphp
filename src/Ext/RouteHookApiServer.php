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
        'namespace' => '',
        'api_server_interface' => '',
        'api_server_namespace' => 'Api',
        'api_server_class_postfix' => '',
        //'api_server_config_cache_file' => '',
        //'api_server_on_missing' => '',
        'api_server_use_singletonex' => false,
        'api_server_404_as_exception' => false,
    ];
    protected $context_class;
    protected $headers = [
        'Access-Control-Allow-Origin'      => '*',
        'Access-Control-Allow-Methods'     => 'POST,PUT,GET,DELETE',
        'Access-Control-Allow-Headers'     => 'version, access-token, user-token, apiAuth, User-Agent, Keep-Alive, Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With',
        'Access-Control-Allow-Credentials' => 'true',
    ];
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        ($this->context_class)::addRouteHook([static::class,'Hook'], 'prepend-inner');
    }
    public static function Hook($path_info)
    {
        return static::G()->_Hook($path_info);
    }
    public function _Hook($path_info)
    {
        ($this->context_class)::setDefaultExceptionHandler([static::class,'OnJsonError']);
        
        list($object, $method) = $this->getObjectAndMethod($path_info);
        if($object ===null && $method === null) {
            return $this->onMissing();
        }
        $inputs = $this->getInputs($path_info);
        
        $data = $this->callAPI($object, $method, $inputs);
        $this->exitJson($data);
        
        return true;
    }
    protected function onMissing()
    {
        if ($this->options['api_server_404_as_exception']){
            throw new \ReflectionException("404", -1);
        }
        return false;
    }
    public static function OnJsonError($e)
    {
        return static::G()->_OnJsonError($e);
    }
    public function _OnJsonError($e)
    {
        $this->exitJson([
            'error_code' => $e->getCode(),
            'error_message' => $e->getMessage(),
        ]);
    }
    protected function getObjectAndMethod($path_info)
    {
        $path_info = trim($path_info, '/');
        $class_array = explode('.', $path_info);
        $method = array_pop($class_array);

        $class = implode('/', $class_array);
        if (empty($class)) {
            return [null, null];
        }
        
        $namespace = $this->getComponenetNamespace('api_server_namespace');
        $namespace_prefix = $namespace ? $namespace .'\\':'';
        
        $class = $namespace_prefix . $class;
        
        $interface = $this->options['api_server_interface'];
        if ($interface && substr($interface, 0, 1) === '~') {
            $interface = ltrim($namespace_prefix.substr($interface,1),'\\').$this->options['api_server_class_postfix'];
        }
        if ($interface && !is_subclass_of($class,$interface)) {
            return [null, null];
        }
        if($this->options['api_server_use_singletonex']){
            if($method==='G'){
                return [null, null];
            }
            return [$class::G(), $method];
        }
        $object = new $class;
        return [$object,$method];
    }
    
    protected function getInputs($path_info)
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
        foreach ($this->headers as $k => $v) {
            ($this->context_class)::header("$k: $v");
        }
        
        $flag = JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK;
        if ($this->context_class::IsDebug()) {
            $flag = $flag | JSON_PRETTY_PRINT;
        }
        echo json_encode($ret, $flag);
    }
    protected function callAPI($class, $method, $input)
    {
        $f = [
            'bool' => FILTER_VALIDATE_BOOLEAN  ,
            'int' => FILTER_VALIDATE_INT,
            'float' => FILTER_VALIDATE_FLOAT,
            'string' => FILTER_SANITIZE_STRING,
        ];

        $reflect = new \ReflectionMethod($class, $method);
        
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
                        throw new \ReflectionException("Type Unmatch: {$name}", -3);
                    }
                }
                $args[] = $input[$name];
                continue;
            }
            if (!$param->isDefaultValueAvailable()) {
                throw new \ReflectionException("Need Parameter: {$name}", -2);
            }
            $args[] = $param->getDefaultValue();
        }
        
        $ret = $reflect->invokeArgs(new $class(), $args);
        return $ret;
    }
}
