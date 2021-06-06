<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class RouteHookPathInfoCompat extends ComponentBase
{
    public $options = [
        'path_info_compact_enable' => false,
        'path_info_compact_action_key' => '_r',
        'path_info_compact_class_key' => '',
    ];
    protected $context_class;
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        if (!$this->options['path_info_compact_enable']) {
            return;
        }
        ($this->context_class)::Route()->addRouteHook([static::class,'Hook'], 'prepend-outter');
        ($this->context_class)::Route()->setUrlHandler([static::class,'Url']);
    }
    
    public static function Url($url = null)
    {
        return static::G()->onURL($url);
    }
    public function onUrl($url = null)
    {
        if (strlen($url) > 0 && '/' == substr($url, 0, 1)) {
            return $url;
        };
        
        $path_info_compact_action_key = $this->options['path_info_compact_action_key'];
        $path_info_compact_class_key = $this->options['path_info_compact_class_key'];
        $get = [];
        $path = '';
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $path = $_SERVER['REQUEST_URI'] ?? '';
        $path_info = $_SERVER['PATH_INFO'] ?? '';
        $script_file = $_SERVER['SCRIPT_FILENAME'];
        
        $path = (string) parse_url($path, PHP_URL_PATH);

        //if (strlen($path_info)) {
        //    $path = substr($path, 0, 0 - strlen($path_info));
        //}
        if ($url === null || $url === '') {
            return $path;
        }
        ////////////////////////////////////
        $flag = false;
        $url = $this->filteRewrite($url, $flag);
        $input_path = (string) parse_url($url, PHP_URL_PATH);
        $input_get = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $input_get);
        
        $blocks = explode('/', $input_path);
        if (isset($blocks[0])) {
            $basefile = basename($script_file);
            if ($blocks[0] === $basefile) {
                array_shift($blocks);
            }
        }

        if ($path_info_compact_class_key) {
            $action = array_pop($blocks);
            $module = implode('/', $blocks);
            if ($module) {
                $get[$path_info_compact_class_key] = $module;
            }
            $get[$path_info_compact_action_key] = $action;
        } else {
            $get[$path_info_compact_action_key] = $input_path;
        }
        $get = array_merge($input_get, $get);
        //if ($path_info_compact_class_key && isset($get[$path_info_compact_class_key]) && $get[$path_info_compact_class_key]==='') {
        //    unset($get[$path_info_compact_class_key]);
        //}
        $query = $get?'?'.http_build_query($get):'';
        $url = $path.$query;
        return $url;
    }
    protected function filteRewrite($url, &$ret = false)
    {
        /* you may turn this on
        $new_url=RouteHookRewrite::G()->filteRewrite($url);
        if ($new_url) {
            $url=$new_url;
            if (strlen($url)>0 && '/'==substr($url,0,1)) {
                return $url;
            };
        }
        //*/
        return $url;
    }
    public static function Hook($path_info)
    {
        return static::G()->_Hook($path_info);
    }
    public function _Hook($path_info)
    {
        $k = $this->options['path_info_compact_action_key'];
        $m = $this->options['path_info_compact_class_key'];
        
        $_SERVER['PATH_INFO_OLD'] = $_SERVER['PATH_INFO'];
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            (__SUPERGLOBAL_CONTEXT)()->_SERVER = $_SERVER;
        }
        
        $_REQUEST = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_REQUEST : $_REQUEST;
        $module = $_REQUEST[$m] ?? null;
        $path_info = $_REQUEST[$k] ?? null;

        $path_info = $module.'/'.$path_info;
        
        ($this->context_class)::Route()->setPathInfo($path_info);
        
        return false;
    }
}
