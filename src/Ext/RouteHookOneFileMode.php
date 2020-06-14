<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Ext\RouteHookRewrite;

class RouteHookOneFileMode extends ComponentBase
{
    public $options = [
        'key_for_action' => '_r',
        'key_for_module' => '',
    ];
    public $key_for_action = '_r';
    public $key_for_module = '';
    
    //@override
    protected function initOptions(array $options)
    {
        $this->key_for_action = $this->options['key_for_action'];
        $this->key_for_module = $this->options['key_for_module'];
    }
    //@override
    protected function initContext(object $context)
    {
        Route::G()->addRouteHook([static::class,'Hook'], 'prepend-outter');
        Route::G()->setURLHandler([static::class,'URL']);
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
        
        $key_for_action = $this->key_for_action;
        $key_for_module = $this->key_for_module;
        $get = [];
        $path = '';
        
        $path = SuperGlobal::G()->_SERVER['REQUEST_URI'];
        $path_info = SuperGlobal::G()->_SERVER['PATH_INFO'] ?? '';
        $script_file = SuperGlobal::G()->_SERVER['SCRIPT_FILENAME'];
        
        $path = $path ?? '';
        $path_info = $path_info ?? '';
        
        $path = parse_url($path, PHP_URL_PATH) ?? '';

        if (strlen($path_info)) {
            $path = substr($path, 0, 0 - strlen($path_info));
        }
        if ($url === null || $url === '') {
            return $path;
        }
        ////////////////////////////////////
        $flag = false;
        $url = $this->filteRewrite($url, $flag);
        $input_path = parse_url($url, PHP_URL_PATH) ?? '';
        $input_get = [];
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $input_get);
        
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
        $k = $this->key_for_action;
        $m = $this->key_for_module;
        
        //$old_path_info=SuperGlobal::G()->_SERVER['PATH_INFO']??'';
        
        $module = SuperGlobal::G()->_REQUEST[$m] ?? null;
        $path_info = SuperGlobal::G()->_REQUEST[$k] ?? null;

        $path_info = $module.'/'.$path_info;
        
        Route::G()->setPathInfo($path_info);
        
        return false;
    }
}
