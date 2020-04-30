<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;
use Exception;

class JsonRpcExt
{
    use SingletonEx;
    public $options = [
        'jsonrpc_namespace' => 'JsonRpc',
        'jsonrpc_backend' => 'https://127.0.0.1',
        'jsonrpc_is_debug' => false,
        'jsonrpc_enable_autoload' => true,
        'jsonrpc_check_token_handler' => null,
        'jsonrpc_service_interface' => '',//todo next version
        'jsonrpc_service_namespace' => '',//todo next version
    ];
    
    public $is_inited;
    protected $backend;
    protected $prefix;
    protected $is_debug;
    
    public function __construct()
    {
    }
    public function init(array $options, object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        
        $this->backend = $this->options['jsonrpc_backend'];
        $this->is_debug = $this->options['jsonrpc_is_debug'];
        
        $namespace = $this->options['jsonrpc_namespace'];

        $this->prefix = trim($namespace, '\\').'\\';
        
        if ($this->options['jsonrpc_enable_autoload']) {
            spl_autoload_register([$this,'_autoload']);
        }
        
        return $this;
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
        return static::G()->_Wrap($class);
    }
    public static function _Wrap($class)
    {
        $class = is_object($class)?get_class($class):$class;
        $base = new JsonRpcClientBase();
        $base->_base_class = $class;
        return $base;
    }
    
    public function _autoload($class)
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
        $post = [
           "jsonrpc" => "2.0",
        ];
        $post['method'] = str_replace("\\", ".", $classname."\\".$method);
        $post['params'] = $arguments;
        
        $post['id'] = time();
        $str_data = $this->curl_file_get_contents($this->backend, $post);
        $data = json_decode($str_data, true);
        if (empty($data)) {
            $str_data = $this->is_debug?$str_data:'';
            throw new Exception("rpc failed".$str_data, -1);
        }
        if (isset($data['error'])) {
            throw new Exception($data['error']['message'], $data['error']['code']);
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
            //ThrowOn()
            $ret['result'] = $service::G()->$method(...$args);
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
