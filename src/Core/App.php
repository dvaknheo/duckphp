<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\KernelTrait;
use DuckPhp\Core\Logger;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\View;

require_once __DIR__ . '/Functions.php';
/**
 * MAIN FILE
 * dvaknheo@github.com
 * OK, Lazy
 *
 */
class App extends ComponentBase
{
    const VERSION = '1.3.5';
    
    use KernelTrait {
        initComponents as Kernel_initComponents;
        prepareServe as Kernel_prepareServe;
        initComponentsOfRoot as Kernel_initComponentsOfRoot;
        initComponentsOfInner as Kernel_initComponentsOfInner;
        initComponentsOfDynmic as  Kernel_initComponentsOfDynmic;
    }
    const EXT_SKIP_INIT = -1;
    const EXT_DISABLE = 0;
    const EXT_DEFAULT = 1;
    const EXT_FOLLOW_APP = 2;
    const EXT_RENEW = 3;
    
    const HOOK_PREPEND_OUTTER = 'prepend-outter';
    const HOOK_PREPEND_INNER = 'prepend-inner';
    const HOOK_APPPEND_INNER = 'append-inner';
    const HOOK_APPPEND_OUTTER = 'append-outter';
    
    protected $core_options = [
        'path_runtime' => 'runtime',
        
        'default_exception_do_log' => true,
        'close_resource_at_output' => false,
        'html_handler' => null,
        'lang_handler' => null,
        
        //// error handler ////
        'is_maintain' => false,
        'error_404' => null,            //'_sys/error-404',
        'error_500' => null,            //'_sys/error-500',
        'error_debug' => null,          //'_sys/error-debug',
        'error_maintain' => null,       //'_sys/error-maintan',
        
        'setting_file' => 'config/DuckPhpSettings.config.php',
        'setting_file_ignore_exists' => true,
        'setting_file_enable' => true,
        'use_env_file' => false,
        
        //*
        // 'path_log' => 'runtime',
        // 'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
        // 'log_prefix' => 'DuckPhpLog',
        
        // 'path_view' => 'view',
        // 'view_skip_notice_error' => true,
        
        // 'superglobal_auto_define' => false,
        //*/
    ];
    protected $common_options = [];
    protected $this_class = '';
    public $setting = [];
    public function __construct()
    {
        parent::__construct();
        $this->options = array_replace_recursive($this->kernel_options, $this->core_options, $this->common_options, $this->options);
        unset($this->kernel_options); // not use again;
        unset($this->core_options); // not use again;
        unset($this->common_options); // not use again;
        $this->this_class = static::class;
    }
    public function version()
    {
        return '('.static::class.')'.static::VERSION;
    }
    //////// override KernelTrait ////////
    protected function initComponents(): void
    {
        if ($this->is_root) {
            $this->loadSetting();
        }
        $this->Kernel_initComponents();
    }
    protected function initComponentsOfRoot($components, $default): void
    {
        $this->loadSetting();

        $my_components = [
            SystemWrapper::class => self::EXT_SKIP_INIT,
            Logger::class => self::EXT_SKIP_INIT,
            CoreHelper::class => self::EXT_SKIP_INIT,
        ];
        $components = array_merge($components, $my_components);

        $this->Kernel_initComponentsOfRoot($components, $default);
    }
    protected function initComponentsOfInner($classes, $default): void
    {
        $components = [
            View::class => self::EXT_FOLLOW_APP,
        ];
        $components = array_merge($components, $classes);
        $this->Kernel_initComponentsOfInner($components, $default);
    }

    protected function initComponentsOfDynmic($classes, $default): void
    {
        $components = [
            SuperGlobal::class => self::EXT_FOLLOW_APP,
            View::class => self::EXT_FOLLOW_APP,
        ];
        $components = array_merge($components, $classes);
        $this->Kernel_initComponentsOfDynmic($components, $default);
    }
    protected function prepareServe()
    {
        $this->Kernel_prepareServe();
        if (App::Setting('duckphp_is_maintain', false) || ($this->options['is_maintain'] ?? false)) {
            $error_maintain = $this->options['error_maintain'] ?? null;
            if (!is_string($error_maintain) && is_callable($error_maintain)) {
                ($error_maintain)();
                return;
            }
            if (!$error_maintain) {
                $str = <<<EOT
Maintaining. <!-- set options['error_maintain'] to override -->
EOT;
                echo $str;
                return;
            }
            View::Show([], $error_maintain);
        }
    }
    
    protected function loadSetting(): void
    {
        $this->setting = $this->options['setting'] ?? [];
        if ($this->options['use_env_file']) {
            $this->dealWithEnvFile();
        }
        if ($this->options['setting_file_enable']) {
            $this->dealWithSettingFile();
        }
        return;
    }
    protected function dealWithEnvFile(): void
    {
        $env_setting = parse_ini_file(realpath($this->options['path']) . '/.env');
        $env_setting = $env_setting ?: [];
        $this->setting = array_merge($this->setting, $env_setting);
    }
    protected function dealWithSettingFile(): void
    {
        $path = $this->options['setting_file'];
        $is_abs = (DIRECTORY_SEPARATOR === '/') ? (substr($path, 0, 1) === '/') : preg_match('/^(([a-zA-Z]+:(\\|\/\/?))|\\\\|\/\/)/', $path);
        if ($is_abs) {
            $full_file = $this->options['setting_file'];
        } else {
            $full_file = realpath($this->options['path']) . '/' . $this->options['setting_file'];
        }
        if (!is_file($full_file)) {
            if (!$this->options['setting_file_ignore_exists']) {
                throw new \ErrorException('DuckPhp: no Setting File');
            }
            return;
        }
        $setting = (function ($file) {
            return require $file;
        })($full_file);
        $this->setting = array_merge($this->setting, $setting);
    }
    ////////////////
    public static function Setting($key = null, $default = null)
    {
        return static::_()->_Setting($key, $default);
    }
    public function _Setting($key = null, $default = null)
    {
        return $key ? (static::Root()->setting[$key] ?? $default) : static::Root()->setting;
    }
    //@override
    public function _On404(): void
    {
        $error_view = $this->options['error_404'] ?? null;
        $error_view = $this->is_inited?$error_view:null;
        
        SystemWrapper::_()->_header('HTTP/1.1 404 Not Found', true, 404);
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)();
            return;
        }
        //// no error_404 setting.
        if (!$error_view) {
            $my_server = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
            $path_info = $my_server['PATH_INFO'] ?? '';
            echo "404 File Not Found<!--PATH_INFO: ($path_info) DuckPhp set options ['error_404'] to override me. -->\n";
            if ($this->options['is_debug']) {
                echo "Debug Info (" . static::class .") Route Error Info: ".Route::_()->getRouteError()." !\n";
            }
            return;
        }
        
        View::_(new View())->init($this->options, $this);
        View::_()->_Show([], $error_view);
    }
    //@override
    public function _OnDefaultException($ex): void
    {
        // exception to root;
        $this->phaseToCurrent(); //Important
        
        if ($this->options['default_exception_do_log']) {
            try {
                Logger::_()->error('['.get_class($ex).']('.$ex->getMessage().')'.$ex->getMessage()."\n".$ex->getTraceAsString());
            } catch (\Throwable $ex) { // @codeCoverageIgnore
                //do nothing
            } // @codeCoverageIgnore
        }
        $error_view = $this->options['error_500'] ?? null;
        $error_view = $this->is_inited?$error_view:null;
        
        //SystemWrapper::_()->_header('Server Error', true, 500); //  do not this :c
        SystemWrapper::_()->_header("HTTP/1.1 500 Server Error", true, 500);
         
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($ex);
            return;
        }
        
        $data = [];
        $data['is_debug'] = $this->_IsDebug();
        $data['ex'] = $ex;
        $data['class'] = get_class($ex);
        $data['message'] = $ex->getMessage();
        $data['code'] = $ex->getCode();
        $data['trace'] = $ex->getTraceAsString();
        $data['file'] = $ex->getFile();
        $data['line'] = $ex->getLine();
        
        //// no error_500 setting.
        if (!$error_view) {
            echo "Internal Error \n<!--DuckPhp set options['error_500'] to override me  -->\n";
            if (!$this->is_inited) {
                echo "<div>error trigger before inited, options['error_500'] ignore. </div>";
            }
            if ($data['is_debug']) {
                echo "<h3>{$data['class']}({$data['code']}):{$data['message']}</h3>";
                echo "<div>{$data['file']} : {$data['line']}</div>";
                echo "\n<pre>Debug On\n\n";
                echo $data['trace'];
                echo "\n</pre>\n";
            } else {
                echo "<!-- DuckPhp set options ['is_debug'] to show debug info -->\n";
            }
            return;
        }
        
        View::_(new View())->init($this->options, $this);
        View::_()->_Show($data, $error_view);
    }
    //@override
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        if (!$this->_IsDebug()) {
            return;
        }
        $descs = array(
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_NOTICE => 'E_NOTICE',
            E_STRICT => 'E_STRICT',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        );
        $error_shortfile = $errfile;
        if (!empty($this->options['path'])) {
            $path = $this->options['path'];
            $error_shortfile = (substr($errfile, 0, strlen($path)) == $path)?substr($errfile, strlen($path)):$errfile;
        }
        $data = array(
            'errno' => $errno,
            'errstr' => $errstr,
            'errfile' => $errfile,
            'errline' => $errline,
            'error_desc' => $descs[$errno],
            'error_shortfile' => $error_shortfile,
        );
        $error_view = $this->options['error_debug'] ?? '';
        $error_view = $this->is_inited?$error_view:null;
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($data);
            return;
        }
        $error_desc = '';
        $ext = ($this->is_inited)? '':"<div>error trigger before inited, options['error_debug'] ignore.";
        if (!$error_view) {
            extract($data);
            echo  <<<EOT
<!--DuckPhp  set options ['error_debug']='_sys/error-debug.php' to override me -->
<fieldset class="_DuckPhp_DEBUG">
    <legend>$error_desc($errno)</legend>
<pre>
{$error_shortfile}:{$errline}
{$errstr}
{$ext}
</pre>
</fieldset>

EOT;
            return;
        }
        View::_()->_Display($error_view, $data);
    }
    protected function onPrepare(): void
    {
        throw new \Exception("DO NOT INIT class DuckPhp\Core\App!");
    }
    
    public function getOverrideableFile($path_sub, $file, $use_override = true)
    {
        if (static::IsAbsPath($file)) {
            return $file;
        }
        if (static::IsAbsPath($path_sub)) {
            return static::SlashDir($path_sub) . $file;
        }
        $path_sub = static::SlashDir($path_sub);
        $phase_name = $this->getThisPhaseName();
        $phase_block = explode(':', $phase_name);
        
        $base_phase = '';
        $full_file = self::_()->options['path'].$path_sub;
        self::Phase($base_phase);
        
        foreach ($phase_block as $i => $v) {
            $path_dir = self::_()->options['path'].$path_sub.implode('/', array_slice($phase_block, $i));
            $full_file = $path_dir.$file;
            if (file_exists($full_file)) {
                break;
            }
            $base_phase = !$base_phase? $v: $base_phase.':'.$v;
            self::Phase($base_phase);
        }
        self::Phase($phase_name);
        return $full_file;
    }
    public function skip404Handler()
    {
        $this->options['skip_404'] = true;
    }
    
    //////// features for view

    public function onBeforeOutput()
    {
    }
    public function adjustViewFile($view)
    {
        return $view === '' ? Route::_()->getRouteCallingPath() : $view;
    }
    ///////
    public static function Platform()
    {
        return static::_()->_Platform();
    }
    public function _Platform()
    {
        return static::_()->_Setting('duckphp_platform', '');
    }
    public static function IsDebug()
    {
        return static::_()->_IsDebug();
    }
    public function _IsDebug()
    {
        $setting_debug = static::_()->_Setting('duckphp_is_debug', false);
        $root_debug = $setting_debug || static::Root()->options['is_debug'] ?? false;
        $this_debug = $this->options['is_debug'] ?? false;
        return $root_debug || $this_debug;
    }
    public static function IsRealDebug()
    {
        return static::_()->_IsRealDebug();
    }
    public function _IsRealDebug()
    {
        return $this->_IsDebug();
    }
    public function isInstalled()
    {
        return $this->options['installed'] ?? false;
    }
    public function lang($str, $args = [])
    {
        $handler = $this->options['lang_handler'] ?? null;
        if ($handler) {
            return $handler($str, $args);
        }
        if (empty($args)) {
            return $str;
        }
        $a = [];
        foreach ($args as $k => $v) {
            $a["{".$k."}"] = $v;
        }
        $ret = str_replace(array_keys($a), array_values($a), $str);
        return $ret;
    }
}
