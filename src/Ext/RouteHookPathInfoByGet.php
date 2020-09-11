<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class RouteHookPathInfoByGet extends ComponentBase
{
    public $options = [
        'use_path_info_by_get' => false,
        'key_for_action' => '_r',
        'key_for_module' => '',
    ];
    protected $context_class;
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        if (!$this->options['use_path_info_by_get']) {
            return;
        }
        ($this->context_class)::Route()->addRouteHook([static::class,'Hook'], 'prepend-outter');
        ($this->context_class)::Route()->setURLHandler([static::class,'URL']);
    }
    
    public static function URL($url = null)
    {
        return static::G()->onURL($url);
    }
    public function onURL($url = null)
    {
        if (strlen($url) > 0 && '/' == $url{0}) {
            return $url;
        };
        
        $key_for_action = $this->options['key_for_action'];
        $key_for_module = $this->options['key_for_module'];
        $get = [];
        $path = '';
        
        $path = ($this->context_class)::SuperGlobal()->_SERVER['REQUEST_URI'] ?? '';
        $path_info = ($this->context_class)::SuperGlobal()->_SERVER['PATH_INFO'] ?? '';
        $script_file = ($this->context_class)::SuperGlobal()->_SERVER['SCRIPT_FILENAME'];
        
        $path = (string) parse_url($path, PHP_URL_PATH);

        if (strlen($path_info)) {
            $path = substr($path, 0, 0 - strlen($path_info));
        }
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
        
        if ($key_for_module) {
            $action = array_pop($blocks);
            $module = implode('/', $blocks);
            if ($module) {
                $get[$key_for_module] = $module;
            }
            $get[$key_for_action] = $action;
        } else {
            $get[$key_for_action] = $input_path;
        }
        $get = array_merge($input_get, $get);
        //if ($key_for_module && isset($get[$key_for_module]) && $get[$key_for_module]==='') {
        //    unset($get[$key_for_module]);
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
            if (strlen($url)>0 && '/'==$url{0}) {
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
        $k = $this->options['key_for_action'];
        $m = $this->options['key_for_module'];
        
        //$old_path_info=($this->context_class)::SuperGlobal()->_SERVER['PATH_INFO']??'';
        
        $module = ($this->context_class)::SuperGlobal()->_REQUEST[$m] ?? null;
        $path_info = ($this->context_class)::SuperGlobal()->_REQUEST[$k] ?? null;

        $path_info = $module.'/'.$path_info;
        
        ($this->context_class)::Route()->setPathInfo($path_info);
        
        return false;
    }
}
