<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class RouteHookRewrite extends ComponentBase
{
    public $options = [
        'rewrite_map' => [],
        'rewrite_auto_extend_method' => true,
    ];
    protected $rewrite_map = [];
    protected $context_class;
    
    public static function Hook($path_info)
    {
        return static::G()->doHook($path_info);
    }
    //@override
    protected function initOptions(array $options)
    {
        $this->rewrite_map = array_merge($this->rewrite_map, $this->options['rewrite_map'] ?? []);
    }
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        ($this->context_class)::Route()->addRouteHook([static::class,'Hook'], 'prepend-outter');
        if ($this->options['rewrite_auto_extend_method'] && \method_exists($context, 'extendComponents')) {
            $context->extendComponents(
                [
                    'assignRewrite' => static::class . '@assignRewrite',
                    'getRewrites' => static::class . '@getRewrites',
                ],
                ['C','A']
            );
        }
    }
    public function assignRewrite($key, $value = null)
    {
        if (is_array($key) && $value === null) {
            $this->rewrite_map = array_merge($this->rewrite_map, $key);
        } else {
            $this->rewrite_map[$key] = $value;
        }
    }
    public function getRewrites()
    {
        return $this->rewrite_map;
    }
    
    public function replaceRegexUrl($input_url, $template_url, $new_url)
    {
        if (substr($template_url, 0, 1) !== '~') {
            return null;
        }
        
        $input_path = (string) parse_url($input_url, PHP_URL_PATH);
        $input_get = [];
        parse_str((string) parse_url($input_url, PHP_URL_QUERY), $input_get);
        
        //$template_path=parse_url($template_url,PHP_URL_PATH);
        //$template_get=[];
        parse_str((string) parse_url($template_url, PHP_URL_QUERY), $template_get);
        $p = '/'.str_replace('/', '\/', substr($template_url, 1)).'/A';
        if (!preg_match($p, $input_path)) {
            return null;
        }
        //if(array_diff_assoc($input_get,$template_get)){ return null; }
        
        $new_url = str_replace('$', '\\', $new_url);
        $new_url = preg_replace($p, $new_url, $input_path);
        
        $new_path = parse_url($new_url ?? '', PHP_URL_PATH) ?? '';
        $new_get = [];
        parse_str((string) parse_url($new_url ?? '', PHP_URL_QUERY), $new_get);
        
        $get = array_merge($input_get, $new_get);
        $query = $get?'?'.http_build_query($get):'';
        return $new_path.$query;
    }
    public function replaceNormalUrl($input_url, $template_url, $new_url)
    {
        if (substr($template_url, 0, 1) === '~') {
            return null;
        }
        
        $input_path = parse_url($input_url, PHP_URL_PATH);
        $input_get = [];
        parse_str((string) parse_url($input_url, PHP_URL_QUERY), $input_get);
        
        $template_path = parse_url($template_url, PHP_URL_PATH);
        $template_get = [];
        parse_str((string) parse_url($template_url, PHP_URL_QUERY), $template_get);
        
        $input_path = '/'.$input_path;

        if ($input_path !== $template_path) {
            return null;
        }

        //if (array_diff_assoc($template_get,$input_get )) {
        //    return null;
        //}
        
        $new_path = parse_url($new_url, PHP_URL_PATH);
        $new_get = [];
        parse_str((string) parse_url($new_url, PHP_URL_QUERY), $new_get);
        
        $get = array_merge($input_get, $new_get);
        $query = $get?'?'.http_build_query($get):'';
        return $new_path.$query;
    }
    // used by RouteHookDirectoryMode
    public function filteRewrite($input_url)
    {
        foreach ($this->rewrite_map as $template_url => $new_url) {
            $ret = $this->replaceNormalUrl($input_url, $template_url, $new_url);
            if ($ret !== null) {
                return $ret;
            }
            $ret = $this->replaceRegexUrl($input_url, $template_url, $new_url);
            if ($ret !== null) {
                return $ret;
            }
        }
        return null;
    }
    protected function changeRouteUrl($url)
    {
        $url = (string)$url;
        $path = parse_url($url, PHP_URL_PATH);
        $input_get = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $input_get);
        
        
        
        $_SERVER['init_get'] = $_GET;
        $_GET = $input_get;
        
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            (__SUPERGLOBAL_CONTEXT)()->_SERVER = $_SERVER;
            (__SUPERGLOBAL_CONTEXT)()->_GET = $_GET;
        }
    }
    protected function doHook($path_info)
    {
        $path_info = ltrim($path_info, '/');
        $_GET = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_GET : $_GET;
        $query = $_GET;
        $query = $query?'?'.http_build_query($query):'';
        
        $input_url = $path_info.$query;
        
        $url = $this->filteRewrite($input_url);
        if ($url !== null) {
            $this->changeRouteUrl($url);
            $path_info = parse_url($url, PHP_URL_PATH);
            ($this->context_class)::Route()->setPathInfo($path_info);
        }
        return  false;
    }
}
