<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\Configer;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;

trait AppPluginTrait
{
    // public $plugin_options = [] => in parent
    
    public $onPluginModePrepare;
    public $onPluginModeInit;
    public $onPluginModeBeforeRun;
    public $onPluginModeAfterRun;
    public $onPluginModeException;
    
    protected $plugin_context_class = '';
    protected $plugin_route_old = null;
    protected $plugin_view_old = null;
    protected $plugin_view_path = null; //TODO remove
    protected $plugin_old_component_map = [];
    private $old_controller_map = [];
    /////////
    //for override
    protected function onPluginModePrepare()
    {
        if ($this->onPluginModePrepare) {
            return ($this->onPluginModePrepare)();
        }
    }
    //for override
    protected function onPluginModeInit()
    {
        if ($this->onPluginModeInit) {
            return ($this->onPluginModeInit)();
        }
    }
    //for override
    protected function onPluginModeBeforeRun()
    {
        if ($this->onPluginModeBeforeRun) {
            return ($this->onPluginModeBeforeRun)();
        }
    }
    //for override
    public function onPluginModeAfterRun()
    {
        if ($this->onPluginModeAfterRun) {
            return ($this->onPluginModeAfterRun)();
        }
    }
    public function onPluginModeException()
    {
        if ($this->onPluginModeException) {
            return ($this->onPluginModeException)();
        }
    }
    //callback
    public static function PluginModeRouteHook($path_info)
    {
        return static::G()->_PluginModeRouteHook($path_info);
    }
    ////////
    public function pluginModeInit(array $plugin_options, object $context = null)
    {
        $plugin_options_default = [
            'plugin_path' => null,
            'plugin_namespace' => null,
            
            'plugin_url_prefix' => '',
            
            'plugin_path_conifg' => 'config',
            'plugin_path_view' => 'view',
            'plugin_path_document' => 'public',
            
            'plugin_routehook_position' => 'append-outter',
            
            'plugin_files_config' => [],
            
            'plugin_view_options' => [],
            'plugin_route_options' => [],
            'plugin_component_class_view' => '',
            'plugin_component_class_route' => '',
            
            'plugin_enable_readfile' => false,
            'plugin_readfile_prefix' => '',
            'plugin_search_config' => true,
            'plugin_use_singletonex_route' => true,
            'plugin_injected_helper_map' => '',
        ];
        $this->plugin_options = $this->plugin_options ?? [];
        $this->plugin_options = array_replace_recursive($plugin_options_default, $this->plugin_options, $plugin_options ?? []);
        $this->plugin_context_class = get_class($context);
        
        $this->pluginModeInitBasePath();
        $this->onPluginModePrepare();
        
        //View , Configer;
        $this->plugin_view_path = View::G()->getViewPath() . str_replace('\\', DIRECTORY_SEPARATOR, $this->plugin_options['plugin_namespace']).DIRECTORY_SEPARATOR;
        $setting_file = $context->options['setting_file'] ?? 'setting';
        $ext_config_files = $this->pluginModeInitConfigFiles($setting_file);
        if (!empty($ext_config_files)) {
            $old_data = Configer::G()->options['config_ext_file_map'] ?? [];
            Configer::G()->options['config_ext_file_map'] = array_merge($old_data, $ext_config_files);
        }
        
        //clone Helper
        if ($this->plugin_options['plugin_injected_helper_map']) {
            $this->plugin_context_class::G()->cloneHelpers($this->plugin_options['plugin_namespace'], $this->plugin_options['plugin_injected_helper_map']);
        }
        
        Route::G()->addRouteHook([static::class,'PluginModeRouteHook'], $this->plugin_options['plugin_routehook_position']);
        $this->onPluginModeInit();
        
        return $this;
    }
    protected function pluginModeInitBasePath()
    {
        if (empty($this->plugin_options['plugin_namespace']) || empty($this->plugin_options['plugin_path'])) {
            $class = static::class;
            $t = explode('\\', $class);
            $t_class = array_pop($t);
            $t_base = array_pop($t);
            $namespace = implode('\\', $t);
            if (empty($this->plugin_options['plugin_namespace'])) {
                $this->plugin_options['plugin_namespace'] = $namespace;
            }
            if (empty($this->plugin_options['plugin_path'])) {
                $file = (new \ReflectionClass($class))->getFileName();
                $this->plugin_options['plugin_path'] = realpath(dirname(dirname(dirname($file)))).DIRECTORY_SEPARATOR;
            }
        }
        if (false !== strpos($this->plugin_options['plugin_path'], '~')) {
            $file = (new \ReflectionClass($this->plugin_context_class))->getFileName();
            $dir = dirname($file);
            $this->plugin_options['plugin_path'] = str_replace('~', $dir, $this->plugin_options['plugin_path']);
        }
    }
    protected function pluginModeInitConfigFiles($setting_file)
    {
        $path_config_override = $this->pluginModeGetPath('plugin_path_conifg');
        if ($this->plugin_options['plugin_search_config']) {
            $this->plugin_options['plugin_files_config'] = $this->pluginModeSearchAllPluginFile($path_config_override, $setting_file);
        }
        
        $ret = [];
        foreach ($this->plugin_options['plugin_files_config'] as $name) {
            $file = $path_config_override.$name.'.php';
            $ret[$name] = $file;
        }
        return $ret;
    }
    protected function pluginModeSearchAllPluginFile($path, $setting_file = '')
    {
        $ret = [];
        $setting_file = !empty($setting_file) ? $path.$setting_file . '.php' : '';
        $flags = \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS ;
        $directory = new \RecursiveDirectoryIterator($path, $flags);
        $it = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($it, '/^.+\.php$/i', \RecursiveRegexIterator::MATCH);
        foreach ($regex as $k => $_) {
            if ($k === $setting_file) {
                continue;
            }
            if (substr($k, -strlen('.sample.php')) === '.sample.php') {
                continue;
            }
            $k = substr($regex->getSubPathName(), 0, -4);
            $ret[] = $k;
        }
        return $ret;
    }
    //////////////
    private function pluginModeCheckPathInfo($path_info)
    {
        $flag = $this->plugin_options['plugin_url_prefix'] ?? false;
        if (!$flag) {
            return true;
        }
        $prefix = '/'.trim($this->plugin_options['plugin_url_prefix'], '/').'/';
        $l = strlen($prefix);
        if (substr($path_info, 0, $l) !== $prefix) {
            return false;
        }
        return true;
    }
    //callback
    protected function _PluginModeRouteHook($path_info)
    {
        $flag = $this->pluginModeCheckPathInfo($path_info);
        if (!$flag) {
            return false;
        }
        //
        $this->pluginModeReplaceDynamicComponent();
        $this->pluginModeInitDynamicComponent();
        
        $this->onPluginModeBeforeRun();
        
        $ret = false;
        try {
            $ret = Route::G()->run();
            if (!$ret && $this->plugin_options['plugin_enable_readfile']) {
                $ret = $this->pluginModeReadFile($path_info);
            }
        } catch (\Throwable $ex) {
            $this->onPluginModeException();
            ExceptionManager::CallException($ex);
        }
        if ($ret) {
            $this->onPluginModeAfterRun();
        }
        $this->pluginModeClear();
        return $ret;
    }
    protected function pluginModeReplaceDynamicComponent()
    {
        $this->old_controller_map = Route::G()->options['controller_class_map'];
        $classes = $this->plugin_context_class::G()->getDynamicComponentClasses();
        foreach ($classes as $class) {
            $object = $class::G();
            $this->plugin_old_component_map[$class] = $object;
            $class::G(clone $object);
        }
    }
    protected function pluginModeInitDynamicComponent()
    {
        $view_class = $this->plugin_options['plugin_component_class_view'] ? : View::class;
        $route_class = $this->plugin_options['plugin_component_class_route'] ? : Route::class;
        View::G(new $view_class());
        Route::G(new $route_class());
        
        $view_options = $this->plugin_options['plugin_view_options'];
        $view_options['path_view'] = $this->plugin_view_path;
        $view_options['path_view_override'] = $this->pluginModeGetPath('plugin_path_view');
        View::G()->init($view_options);
        
        $route_options = $this->plugin_options['plugin_route_options'];
        $route_options['namespace'] = $this->plugin_options['plugin_namespace'];
        $route_options['controller_path_prefix'] = $this->plugin_options['plugin_url_prefix'];
        $route_options['controller_class_map'] = $this->old_controller_map;
        Route::G()->init($route_options);
    }
    protected function pluginModeReadFile($path_info)
    {
        $path_document = $this->pluginModeGetPath('plugin_path_document');
        $file = urldecode(substr($path_info, strlen($this->plugin_options['plugin_url_prefix'])));
        
        $prefix = $this->plugin_options['plugin_readfile_prefix'];
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
        $file = $path_document.$file;
        if (!is_file($file)) {
            return false;
        }
        ($this->plugin_context_class)::header('Content-Type: '.$this->mime_content_type($file));
        echo file_get_contents($file);
        return true;
    }
    public function pluginModeClear()
    {
        foreach ($this->plugin_old_component_map as $class => $object) {
            $class::G($object);
        }
        $this->plugin_old_component_map = [];
    }
    /////////////////////////////
    public function pluginModeGetOldComponent($class)
    {
        return $this->plugin_old_component_map[$class] ?? null;
    }
    private function pluginModeGetPath($path_key, $path_key_parent = 'plugin_path'): string
    {
        if (DIRECTORY_SEPARATOR === '/') {
            if (substr($this->plugin_options[$path_key], 0, 1) === '/') {
                return rtrim($this->plugin_options[$path_key], '/').'/';
            } else {
                return $this->plugin_options[$path_key_parent].rtrim($this->plugin_options[$path_key], '/').'/';
            }
        } else { // @codeCoverageIgnoreStart
            if (substr($this->plugin_options[$path_key], 1, 1) === ':') {
                return rtrim($this->plugin_options[$path_key], '\\').'\\';
            } else {
                return $this->plugin_options[$path_key_parent].rtrim($this->plugin_options[$path_key], '\\').'\\';
            } // @codeCoverageIgnoreEnd
        }
    }
    
    protected function mime_content_type($file)
    {
        static $mimes = [];
        if (empty($mimes)) {
            $mime_string = $this->getMimeData();
            $items = explode("\n", $mime_string);
            foreach ($items as $content) {
                if (\preg_match("/\s*(\S+)\s+(\S.+)/", $content, $match)) {
                    $mime_type = $match[1];
                    $extension_var = $match[2];
                    $extension_array = \explode(' ', \substr($extension_var, 0, -1));
                    foreach ($extension_array as $file_extension) {
                        $mimes[$file_extension] = $mime_type;
                    }
                }
            }
        }
        return $mimes[pathinfo($file, PATHINFO_EXTENSION)] ?? 'text/plain';
    }
    protected function getMimeData()
    {
        return <<<EOT
types {
    text/html                             html htm shtml;
    text/css                              css;
    text/xml                              xml;
    image/gif                             gif;
    image/jpeg                            jpeg jpg;
    application/javascript                js;
    application/atom+xml                  atom;
    application/rss+xml                   rss;

    text/mathml                           mml;
    text/plain                            txt;
    text/vnd.sun.j2me.app-descriptor      jad;
    text/vnd.wap.wml                      wml;
    text/x-component                      htc;

    image/png                             png;
    image/tiff                            tif tiff;
    image/vnd.wap.wbmp                    wbmp;
    image/x-icon                          ico;
    image/x-jng                           jng;
    image/x-ms-bmp                        bmp;
    image/svg+xml                         svg svgz;
    image/webp                            webp;

    application/font-woff                 woff;
    application/java-archive              jar war ear;
    application/json                      json;
    application/mac-binhex40              hqx;
    application/msword                    doc;
    application/pdf                       pdf;
    application/postscript                ps eps ai;
    application/rtf                       rtf;
    application/vnd.apple.mpegurl         m3u8;
    application/vnd.ms-excel              xls;
    application/vnd.ms-fontobject         eot;
    application/vnd.ms-powerpoint         ppt;
    application/vnd.wap.wmlc              wmlc;
    application/vnd.google-earth.kml+xml  kml;
    application/vnd.google-earth.kmz      kmz;
    application/x-7z-compressed           7z;
    application/x-cocoa                   cco;
    application/x-java-archive-diff       jardiff;
    application/x-java-jnlp-file          jnlp;
    application/x-makeself                run;
    application/x-perl                    pl pm;
    application/x-pilot                   prc pdb;
    application/x-rar-compressed          rar;
    application/x-redhat-package-manager  rpm;
    application/x-sea                     sea;
    application/x-shockwave-flash         swf;
    application/x-stuffit                 sit;
    application/x-tcl                     tcl tk;
    application/x-x509-ca-cert            der pem crt;
    application/x-xpinstall               xpi;
    application/xhtml+xml                 xhtml;
    application/xspf+xml                  xspf;
    application/zip                       zip;

    application/octet-stream              bin exe dll;
    application/octet-stream              deb;
    application/octet-stream              dmg;
    application/octet-stream              iso img;
    application/octet-stream              msi msp msm;

    application/vnd.openxmlformats-officedocument.wordprocessingml.document    docx;
    application/vnd.openxmlformats-officedocument.spreadsheetml.sheet          xlsx;
    application/vnd.openxmlformats-officedocument.presentationml.presentation  pptx;

    audio/midi                            mid midi kar;
    audio/mpeg                            mp3;
    audio/ogg                             ogg;
    audio/x-m4a                           m4a;
    audio/x-realaudio                     ra;

    video/3gpp                            3gpp 3gp;
    video/mp2t                            ts;
    video/mp4                             mp4;
    video/mpeg                            mpeg mpg;
    video/quicktime                       mov;
    video/webm                            webm;
    video/x-flv                           flv;
    video/x-m4v                           m4v;
    video/x-mng                           mng;
    video/x-ms-asf                        asx asf;
    video/x-ms-wmv                        wmv;
    video/x-msvideo                       avi;
    font/ttf                              ttf;
}

EOT;
    }
}
