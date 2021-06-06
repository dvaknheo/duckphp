<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class RouteHookFunctionRoute extends ComponentBase
{
    public $options = [
        'function_route' => false,
        'function_route_method_prefix' => 'action_',
        'function_route_404_to_index' => false,
    ];

    protected $context_class;
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        ($this->context_class)::addRouteHook([static::class,'Hook'], 'append-inner');
    }
    public static function Hook($path_info)
    {
        return static::G()->_Hook($path_info);
    }
    public function _Hook($path_info = '/')
    {
        $path_info = ($this->context_class)::Route()->getPathInfo();
        $path_info = ltrim($path_info, '/');
        $path_info = empty($path_info) ? 'index' : $path_info;
        $path_info = str_replace('/', '_', $path_info);
        
        $_POST = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_POST : $_POST;
        $post_prefix = !empty($_POST)? ($this->context_class)::Route()->options['controller_prefix_post'] :'';
        $prefix = $this->options['function_route_method_prefix'] ?? '';
        
        $callback = $prefix.$post_prefix.$path_info;
        
        $flag = $this->runCallback($callback);
        if ($flag) {
            return true;
        }
        if (!empty($_POST) && !empty($post_prefix)) {
            $callback = $prefix.$path_info;
            $flag = $this->runCallback($callback);
            if ($flag) {
                return true;
            }
        }
        if (!$this->options['function_route_404_to_index']) {
            return false;
        }
        $callback = $prefix.'index';
        $flag = $this->runCallback($callback);
        return $flag;
    }
    private function runCallback($callback)
    {
        if (is_callable($callback)) {
            ($callback)();
            return true;
        }
        return false;
    }
}
