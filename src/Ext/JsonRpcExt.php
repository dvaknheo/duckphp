<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use Exception;

class JsonRpcExt extends ComponentBase
{
    public $options = [
        'jsonrpc_namespace' => 'JsonRpc',
        'jsonrpc_backend' => 'https://127.0.0.1',
        'jsonrpc_is_debug' => false,
        'jsonrpc_enable_autoload' => true,
        'jsonrpc_check_token_handler' => null,
        'jsonrpc_wrap_auto_adjust' => true,
        'jsonrpc_service_interface' => '',
        'jsonrpc_service_namespace' => '',
    ];
    
    protected $prefix;
    protected $is_debug;
    
    //@override
    protected function initOptions(array $options)
    {
        $this->is_debug = $this->options['jsonrpc_is_debug'];
        $this->prefix = trim($this->options['jsonrpc_namespace'], '\\').'\\';
        
        if ($this->options['jsonrpc_enable_autoload']) {
            spl_autoload_register([$this,'_autoload']);
        }
    }
    
    public function clear()
    {
        spl_autoload_unregister([$this,'_autoload']);
    }
    public function getRealClass($object)
    {
        $class = get_class($object);
        if (substr($class, 0, strlen($this->prefix)) !== $this->prefix) {
            return $class;
        }
        return substr($class, strlen($this->prefix));
    }
    public static function Wrap($class)
    {
        return static::_()->_Wrap($class);
    }
    public static function _Wrap($class)
    {
        $class = is_object($class)?get_class($class):$class;
        $base = (new JsonRpcClientBase())->setJsonRpcClientBase($class);
        return $class::_($base);
    }
    
    public function _autoload($class): void
    {
        if (substr($class, 0, strlen($this->prefix)) !== $this->prefix) {
            return;
        }
        $blocks = explode('\\', $class);
        $basename = array_pop($blocks);
        $namespace = implode('\\', $blocks);
        
        $code = "namespace $namespace{ class $basename extends \\". __NAMESPACE__  ."\\JsonRpcClientBase{} }";
        eval($code);
    }
    public function callRpc($classname, $method, $arguments)
    {
        $namespace = trim($this->options['jsonrpc_service_namespace'], '\\');
        $classname = str_replace('.', '\\', $classname);
        
        $classname = $namespace?$namespace."\\".$classname:$classname;


        $post = [
           "jsonrpc" => "2.0",
        ];
        $post['method'] = str_replace("\\", ".", $classname."\\".$method);
        $post['params'] = $arguments;
        
        $post['id'] = time();
        $str_data = $this->curl_file_get_contents($this->options['jsonrpc_backend'], $post);
        $data = json_decode($str_data, true);
        if (empty($data)) {
            $str_data = $this->options['jsonrpc_is_debug']?$str_data:'';
            throw new \ErrorException("JsonRpc failed,(".var_export($this->options['jsonrpc_backend'], true).")returns:[".$str_data."]", -1);
        }
        if (isset($data['error'])) {
            throw new \Exception($data['error']['message'], $data['error']['code']);
        }
        return $data['result'];
    }
    
    public function onRpcCall(array $input)
    {
        $ret = [
           "jsonrpc" => "2.0",
        ];
        $id = $input['id'] ?? null;
        $method = $input['method'] ?? '';
        $a = explode('.', $method);
        $method = array_pop($a);
        $service = implode("\\", $a);
        try {
            $service = $this->adjustService($service);
            $args = $input['params'] ?? [];
            $ret['result'] = $service::_()->$method(...$args);
        } catch (\Throwable $ex) {
            $ret['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
            ];
        }
        $ret['id'] = $id;
        
        return $ret;
    }
    /////////////////////
    protected function adjustService($service)
    {
        $namespace = trim($this->options['jsonrpc_service_namespace'], '\\');
        //$namespace=$namespace?$namespace."\\":'';
        
        $service = $namespace?$namespace."\\".$service:$service;
        if (empty($this->options['jsonrpc_service_interface'])) {
            return $service;
        }
        if (!is_subclass_of($service, $this->options['jsonrpc_service_interface'])) {
            return null;
        }
        return $service;
    }
    protected function curl_file_get_contents($url, $post)
    {
        $ch = curl_init();
        
        if (is_array($url)) {
            list($base_url, $real_host) = $url;
            $url = $base_url;
            $host = parse_url($url, PHP_URL_HOST);
            $port = parse_url($url, PHP_URL_PORT);
            $c = $host.':'.$port.':'.$real_host;
            curl_setopt($ch, CURLOPT_CONNECT_TO, [$c]);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $this->prepare_token($ch);
        
        $data = curl_exec($ch);
        curl_close($ch);
        return $data !== false?$data:'';
    }
    protected function prepare_token($ch)
    {
        if (isset($this->options['jsonrpc_check_token_handler'])) {
            return ($this->options['jsonrpc_check_token_handler'])($ch);
        }
        return;
    }
}
