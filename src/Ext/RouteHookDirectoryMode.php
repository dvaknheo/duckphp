<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class RouteHookDirectoryMode extends ComponentBase
{
    public $options = [
        'mode_dir_basepath' => '',
        //'mode_dir_use_path_info'=>true,
        //'mode_dir_key_for_module'=>true,
        //'mode_dir_key_for_action'=>true,
    ];
    protected $basepath;
    protected $context_class;
    
    protected function initOptions(array $options)
    {
        $this->basepath = $this->options['mode_dir_basepath'];
    }
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        ($this->context_class)::Route()->addRouteHook([static::class,'Hook'], 'prepend-outter');
        ($this->context_class)::Route()->setUrlHandler([static::class,'Url']);
    }
    
    protected function adjustPathinfo($basepath, $path_info)
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $input_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $script_filename = $_SERVER['SCRIPT_FILENAME'];
        $document_root = $_SERVER['DOCUMENT_ROOT'];
        
        $path_info = substr($document_root.$input_path, strlen($basepath));
        $path_info = ltrim((string)$path_info, '/').'/';
        $blocks = explode('/', $path_info);

        $path_info = '';
        $has_file = false;
        foreach ($blocks as $i => $v) {
            if (!$has_file && substr($v, -strlen('.php')) === '.php') {
                $has_file = true;
                $path_info .= substr($v, 0, -strlen('.php')).'/';
                if (!($blocks[$i + 1])) {
                    $path_info .= 'index';
                    break;
                }
            } else {
                $path_info .= $v.'/';
            }
        }
        $path_info = rtrim($path_info, '/');
        
        return $path_info;
    }
    public static function Url($url = null)
    {
        return static::G()->onUrl($url);
    }
    public function onUrl($url = null)
    {
        if (strlen($url) > 0 && '/' === substr($url, 0, 1)) {
            return $url;
        };
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $document_root = $_SERVER['DOCUMENT_ROOT'];
        $base_url = substr($this->basepath, strlen($document_root));
        $input_path = (string) parse_url($url, PHP_URL_PATH);
        
        $blocks = explode('/', $input_path);
        
        $basepath = $this->basepath;
        $new_path = '';
        $l = count($blocks);
        foreach ($blocks as $i => $v) {
            if ($i + 1 >= $l) {
                break;
            }
            $class_names = array_slice($blocks, 0, $i + 1);
            $full_class_name = implode('/', $class_names);
            $file = $basepath.$full_class_name.'.php';
            if (is_file($file)) {
                $path_info = isset($blocks[$i])?array_slice($blocks, -$i - 1):[];
                $path_info = implode('/', $path_info);
                $new_path = $base_url.implode('/', $class_names).'.php'.($path_info?'/'.$path_info:'');
                break;
            }
        }
        if (!$new_path) {
            return $url;
        }
    
        $new_get = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $new_get);
        
        $get = array_merge($new_get, $new_get);
        $query = $get?'?'.http_build_query($get):'';
        $ret = $new_path.$query;
        return $ret;
    }
    public static function Hook($path_info)
    {
        return static::G()->_Hook($path_info);
    }
    public function _Hook($path_info)
    {
        $path_info = $this->adjustPathinfo($this->basepath, $path_info);
        ($this->context_class)::Route()->setPathInfo($path_info);
        return false;
    }
}
