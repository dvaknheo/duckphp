<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class RouteHookResource extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_override_from' => '',
        'path_resource' => 'res',
        'controller_url_prefix' => '',
        'controller_resource_prefix' => '',
    ];
    public static function Hook($path_info)
    {
        return static::G()->_Hook($path_info);
    }
    protected function initContext(object $context)
    {
        ($this->context_class)::Route()->addRouteHook([static::class,'Hook'], 'append-outter');
        return $this;
    }
    public function _Hook($path_info)
    {
        $file = urldecode(''.$path_info);
        $prefix = $this->options['controller_url_prefix'];
        $prefix = $prefix?'/'.$prefix:'';
        $prefix .= $this->options['controller_resource_prefix'];
        if (!empty($prefix) && (substr($file, 0, strlen($prefix)) !== $prefix)) {
            return false;
        }
        
        if (!empty($prefix)) {
            $file = substr($file, strlen($prefix));
        }
        if (false !== strpos($file, '../')) {
            return false;
        }
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            return false;
        }
        
        $options = $this->options;
        $options['path'] = $options['path_override_from']?$options['path_override_from']:$options['path'];
        
        $file = ComponentBase::GetFileFromSubComponent($options, 'resource', $file);
        if (!$file) {
            return false;
        }
        ($this->context_class)::header('Content-Type: '.($this->context_class)::mime_content_type($file));
        echo file_get_contents($file);
        ($this->context_class)::exit();
        return true;
    }
}
