<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;

class RouteHookPathInfoCompat extends ComponentBase
{
    public $options = [
        'path_info_compact_enable' => true,
        'path_info_compact_action_key' => '_r',
        'path_info_compact_class_key' => '',
    ];
    //@override
    protected function initContext(object $context): void
    {
        if (!$this->options['path_info_compact_enable']) {
            return;
        }
        Route::_()->addRouteHook([static::class,'Hook'], 'prepend-outter');
        Route::_()->setUrlHandler([static::class,'Url']);
    }
    
    public static function Url($url = null)
    {
        return static::_()->onURL($url);
    }
    public function onUrl(?string $url = null): string
    {
        if ($url === null) {
            return '';
        }
        if (strlen($url) > 0 && '/' == substr($url, 0, 1)) {
            return $url;
        };
        
        $path_info_compact_action_key = $this->options['path_info_compact_action_key'];
        $path_info_compact_class_key = $this->options['path_info_compact_class_key'];
        $get = [];
        $path = '';
        $my_server = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $path = $my_server['REQUEST_URI'] ?? '';
        $path_info = $my_server['PATH_INFO'] ?? '';
        $script_file = $my_server['SCRIPT_FILENAME'];
        
        $path = (string) parse_url($path, PHP_URL_PATH);

        //if (strlen($path_info)) {
        //    $path = substr($path, 0, 0 - strlen($path_info));
        //}
        if ($url === '') {
            return $path;
        }
        ////////////////////////////////////
        $flag = false;
        $url = $this->filteRewrite($url, $flag);
        $input_path = (string) parse_url((string)$url, PHP_URL_PATH);
        $input_get = [];
        parse_str((string) parse_url((string)$url, PHP_URL_QUERY), $input_get);
        
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
    protected function filteRewrite(string $url, &$ret = false): ?string
    {
        /* you may turn this on
        $new_url=RouteHookRewrite::_()->filteRewrite($url);
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
        return static::_()->_Hook($path_info);
    }
    public function _Hook($path_info)
    {
        $k = $this->options['path_info_compact_action_key'];
        $m = $this->options['path_info_compact_class_key'];
        
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            $sg = (__SUPERGLOBAL_CONTEXT)();
            $sg->_SERVER['PATH_INFO_OLD'] = $sg->_SERVER['PATH_INFO_OLD'] ?? '';
            $module = $sg->_REQUEST[$m] ?? '';
            $path_info = $sg->_REQUEST[$k] ?? '';
        } else {
            $_SERVER['PATH_INFO_OLD'] = $_SERVER['PATH_INFO'] ?? '';
            $module = $_REQUEST[$m] ?? '';
            $path_info = $_REQUEST[$k] ?? '';
        }
        
        $path_info = $module.'/'.$path_info;
        Route::_()::PathInfo($path_info);
        
        return false;
    }
}
