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
        'api_server_base_class' => '',
        'api_server_namespace' => 'Api',
        'api_server_class_postfix' => '',
        'api_server_use_singletonex' => false,
        'api_server_404_as_exception' => false,
    ];
    //'api_server_config_cache_file' => '',
    //'api_server_on_missing' => '',
    protected $context_class;
    protected $headers = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'POST,PUT,GET,DELETE',
        'Access-Control-Allow-Headers' => 'version, access-token, user-token, apiAuth, User-Agent, Keep-Alive, Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With',
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
        if ($object === null && $method === null) {
            return $this->onMissing();
        }
        $inputs = $this->getInputs($path_info);
        
        $data = $this->callAPI($object, $method, $inputs);
        $this->exitJson($data);
        
        return true;
    }
    protected function onMissing()
    {
        if ($this->options['api_server_404_as_exception']) {
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
    protected function getComponenetNamespace($namespace_key)
    {
        $namespace = $this->options['namespace'];
        $namespace_componenet = $this->options[$namespace_key];
        if (substr($namespace_componenet, 0, 1) !== '\\') {
            $namespace_componenet = rtrim($namespace, '\\').'\\'.$namespace_componenet;
        }
        $namespace_componenet = trim($namespace_componenet, '\\');
        
        return $namespace_componenet;
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
        
        $class = $namespace_prefix . $class . $this->options['api_server_class_postfix'];
        /** @var string */
        $base_class = str_replace('~', $namespace_prefix, $this->options['api_server_base_class']);
        if ($base_class && !is_subclass_of($class, $base_class)) {
            return [null, null];
        }
        if ($this->options['api_server_use_singletonex']) {
            if ($method === 'G') {
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
            $_REQUEST = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_REQUEST : $_REQUEST;
            $inputs = $_REQUEST;
        } else {
            $_POST = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_POST : $_POST;
            $inputs = $_POST;
        }
        return $inputs;
    }
    protected function exitJson($ret, $exit = true)
    {
        foreach ($this->headers as $k => $v) {
            ($this->context_class)::header("$k: $v");
        }
        ($this->context_class)::header('Content-Type: text/plain; charset=utf-8');
        $flag = JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK;
        if ($this->context_class::IsDebug()) {
            $flag = $flag | JSON_PRETTY_PRINT;
        }
        echo json_encode($ret, $flag);
    }
    protected function callAPI($object, $method, $input)
    {
        $f = [
            'bool' => FILTER_VALIDATE_BOOLEAN  ,
            'int' => FILTER_VALIDATE_INT,
            'float' => FILTER_VALIDATE_FLOAT,
            'string' => FILTER_SANITIZE_STRING,
        ];

        $reflect = new \ReflectionMethod($object, $method);
        
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
        
        $ret = $reflect->invokeArgs($object, $args);
        return $ret;
    }
}
