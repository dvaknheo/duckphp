<?php

declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
// MAIN FILE
//dvaknheo@github.com
//OK, Lazy

namespace DuckPhp\Core;

use DuckPhp\Core\Console;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\Route;
use DuckPhp\Core\Runtime;

trait KernelTrait
{
    public $options = [];

    protected $kernel_options = [
        'path' => null,

        'name' => null,
        'phase_name' => null,
        'namespace' => null,

        'override_class' => null,
        'cli_enable' => true,
        'is_debug' => false,
        'init_components' => true,
        'ext' => [],
        'app' => [],
        'data' => [],
        'command' => [],

        'skip_404' => false,
        'skip_exception_check' => false,

        'on_init' => null,
        //'on_before_run' => null,
        //'on_after_run' => null,

        'setting_file' => 'config/DuckPhpSettings.config.php',
        'setting_file_ignore_exists' => true,
        'setting_file_enable' => true,
        'use_env_file' => false,

        'cli_command_classes' => [],
        'cli_command_prefix' => null,
        'cli_command_method_prefix' => 'command_',
        //*/
        // 'namespace' => '',
        // 'namespace_controller' => 'Controller',

        // 'controller_path_ext' => '',
        // 'controller_welcome_class' => 'Main',
        // 'controller_welcome_class_visible' => false,
        // 'controller_welcome_method' => 'index',

        // 'controller_class_base' => '',
        // 'controller_class_postfix' => 'Controller',
        // 'controller_method_prefix' => 'action_',
        // 'controller_prefix_post' => 'do_', //TODO remove it

        // 'controller_class_map' => [],

        // 'controller_resource_prefix' => '',
        // 'controller_url_prefix' => '',

        // 'use_output_buffer' => false,
        // 'path_runtime' => 'runtime',

        //*/
    ];
    public $setting = [];
    protected $is_root = true;
    protected static $root_instance = null;

    public static function RunQuickly(array $options = [], ?callable $after_init = null): bool
    {
        $instance = static::_()->init($options);
        if ($after_init) {
            ($after_init)();
        }
        return $instance->run();
    }
    public static function Current()
    {
        return self::_();
    }
    public static function Root()
    {
        return self::$root_instance;
    }
    public static function Phase($new = null)
    {
        return static::_()->_Phase($new);
    }
    public static function Setting($key = null, $default = null)
    {
        return static::_()->_Setting($key, $default);
    }
    public static function IsRoot()
    {
        return static::Current()->_IsRoot();
    }
    protected function initOptions(array $options): void
    {
        $this->options = array_replace_recursive($this->options, $options);
    }
    protected function getDefaultProjectNameSpace(?string $class): string
    {
        $a = explode('\\', $class ?? static::class);
        array_pop($a);
        array_pop($a);
        $namespace = implode('\\', $a);
        return $namespace;
    }
    protected function getDefaultProjectPath(): string
    {
        $my_server = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $path = realpath(dirname($my_server['SCRIPT_FILENAME']) . '/../');
        $path = (string)$path;
        $path = ($path !== '') ? rtrim($path, '/') . '/' : '';

        return $path;
    }
    protected function getDefaultName(): string
    {
        return $this->options['namespace'];
    }
    protected function getDefaultPhaseName(?object $context = null): string
    {
        if ($context) {
            $name = $this->options['name'] ? $this->options['name'] : static::class;
            return $context->options['phase_name'] ? $context->options['phase_name'] . ':' . $name : $name;
        } else {
            return $this->options['name'];
        }
    }
    protected function getDefaultConsoleNamespace(): string
    {
         if ($this->is_root) {
            return '';
        }
        $root_name = self::$root_instance->options['phase_name'];
        $ret = substr($this->options['phase_name'],strlen($root_name)+1);
        $ret = str_replace(['\\', '/'], '-', $ret);
        return $ret;
    }
    ////////
    public function _Phase(?string $new = null): string
    {
        $container = PhaseContainer::GetContainer();
        $old = $container->getCurrentContainer();
        if ($new) {
            $container->setCurrentContainer($new);
        }
        return $old;
    }
    public function _IsRoot()
    {
        return $this->is_root;
    }
    public function getOverridingClass()
    {
        return $this->overriding_class;
    }

    public function getThisClassName()
    {
        return $this->overriding_class ?? static::class;
    }
    public function getThisPhaseName()
    {
        return $this->options['phase_name'];
    }
    protected function initContainer(?object $context = null): bool
    {
        $context = $context ?? '';
        $this->is_root = !(\is_a($context, self::class) || (static::class === self::class));
        //////////////////////////////

        if ($this->is_root) {
            self::$root_instance = $this;

            $this->onBeforeCreatePhases();
            $flag = PhaseContainer::ReplaceSingletonImplement();
            $container = PhaseContainer::GetContainer();
            $container->setDefaultContainer($this->getThisPhaseName());
            $container->setCurrentContainer($this->getThisPhaseName());
            //TODO Move public containers to this;
            $this->onAfterCreatePhases();
        } else {
            $container = PhaseContainer::GetContainer();
            $container->setCurrentContainer($this->getThisPhaseName());
        }
        (self::class)::_($this);
        (static::class)::_($this);

        /////////////
        return false;
    }
    protected function addPublicClassesInRoot(array $classes): void
    {
        if (!$this->is_root) {
            return;
        }
        PhaseContainer::GetContainer()->addPublicClasses($classes);
        foreach ($classes as $class) {
            $class::_();
        }
    }
    protected function createLocalObject(string $class, ?object $object = null): object
    {
        return PhaseContainer::GetContainer()->createLocalObject($class, $object);
    }
    protected function initException(array $options): void
    {
        $exception_options = $options;
        $exception_options['default_exception_handler'] = [self::class, 'OnDefaultException']; // must be self,be root
        $exception_options['dev_error_handler'] = [self::class, 'OnDevErrorHandler'];        //be self, be root
        if (!$this->is_root) {
            $exception_option['handle_all_dev_error'] = false;
            $exception_option['handle_all_exception'] = false;
        }
        ExceptionManager::_()->init($exception_options, $this);
    }
    //init
    public function init(array $options, object $context = null)
    {

        $options['namespace'] = $options['namespace'] ?? ($this->options['namespace'] ?? ($this->getDefaultProjectNameSpace($this->overriding_class ?? null)));
        require_once __DIR__ . '/Functions.php';
        $this->initOptions($options);
        if ($options['override_class'] ?? false) {

            $class = $options['override_class'];
            unset($options['override_class']);

            //$options['override_class_from'] = $this->overriding_class;
            //$this->overriding_class = $options['override_class_from'];

            return $class::_(new $class)->init($options, $context);
        }
        $this->initOptions($options);

        $this->options['path'] = $this->options['path'] ?? $this->getDefaultProjectPath();
        $this->options['name'] = $this->options['name'] ?? $this->getDefaultName();
        $this->options['phase_name'] = $this->getDefaultPhaseName($context);
        ////[[[[
        ////]]]]

        $this->initContainer($context);
        $this->initException($this->options);

        $this->onPrepare();
        $this->initComponents();
        $this->initExtensions($this->options['ext']);
        $this->onInit();
        if ($this->options['on_init']) {
            ($this->options['on_init'])();
        }
        $this->onBeforeChildrenInit();
        $this->initChildren($this->options['app']);

        $this->is_inited = true; // from ComponentTrait
        $this->onInited();
        return $this;
    }
    protected function initComponents(): void
    {
        $is_cli = PHP_SAPI === 'cli' || $this->options['cli_enable'];

        if ($this->is_root) {
            $this->loadSetting(); // todo move to "App"
            $this->addPublicClassesInRoot([
                Console::class,
            ]);
            Console::_()->init($this->options, $this);
        }

        $cli_namespace = $this->getDefaultConsoleNamespace();
        Console::_()->regCommandClass($cli_namespace, $this->options['phase_name'], $this->options['command'], $this->options['cli_command_method_prefix']);
        Route::_()->init($this->options, $this);
        Runtime::_()->init($this->options, $this);
        $this->doInitComponents();
    }
    protected function doInitComponents(): void
    {
        //for override
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
    public function _Setting($key = null, $default = null)
    {
        return $key ? (static::Root()->setting[$key] ?? $default) : static::Root()->setting;
    }
    protected function initExtensions(array $exts): void
    {
        foreach ($exts as $class => $options) {
            if ($options === false) {
                continue;
            }
            if ($options === true) {
                $options = $this->options;
            }
            if (!class_exists($class)) {
                throw new \Exception("ext [$class] not exists");
            }
            $class::_()->init($options, $this);
        }
    }
    protected function initChildren(array $apps): void
    {
        foreach ($apps as $class => $options) {
            if ($options === false) {
                continue;
            }
            if (!is_array($options)) {
                continue;
            }
            if (!class_exists($class)) {
                throw new \Exception("Child [$class] not exists");
            }
            $class::_()->init($options, $this);

            $this->phaseToCurrent();
        }
    }


    public function run(): bool
    {
        if (PHP_SAPI === 'cli' && $this->is_root && $this->options['cli_enable']) {
            return $this->execute();
        }
        $ret = false;
        $this->phaseToCurrent();

        $this->onBeforeRun();
        try {
            Runtime::_()->run();
            $ret = Route::_()->run();
            //\DuckPhp\Core\PhaseContainer::GetContainerInstanceEx()->dumpAllObject();
            //$t = get_included_files();sort($t); var_export($t);
            if (!$ret) {
                $ret = $this->runChildren();
            }
            $this->phaseToCurrent();
            if (!$ret && $this->is_root && !($this->options['skip_404'] ?? false)) {
                $this->_On404();
            }
        } catch (\Throwable $ex) {
            $this->runException($ex);
            $ret = true;
        } finally {
            Runtime::_()->clear();
        }
        $this->onAfterRun();
        return $ret;
    }
    protected function runException(\Throwable $ex): void
    {
        $last_phase = $this->_Phase();

        if ($this->options['skip_exception_check']) {
            throw $ex;
        }
        $this->phaseToCurrent();
        Runtime::_()->last_phase = $last_phase;
        ExceptionManager::CallException($ex);
        Runtime::_()->onException($ex);
    }
    protected function runChildren(): bool
    {
        $flag = false;
        foreach ($this->options['app'] as $class => $options) {
            $flag = $class::_()->run();
            if ($flag) {
                break;
            }
        }
        return $flag;
    }
    public function execute(): bool
    {
        $ret = false;
        $this->phaseToCurrent();

        try {
            Runtime::_()->run();
            $ret = Console::_()->run();
        } catch (\Throwable $ex) {
            $this->runException($ex);
            $ret = true;
        } finally {
            Runtime::_()->clear();
        }
        return $ret;
    }
    protected function phaseToCurrent(): void
    {
        $this->_Phase($this->options['phase_name']);
    }
    //main produce end
    ////////////////////////
    //for override
    public static function On404(): void
    {
        static::_()->_On404();
    }
    public static function OnDefaultException($ex): void
    {
        static::_()->_OnDefaultException($ex);
    }
    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        static::_()->_OnDevErrorHandler($errno, $errstr, $errfile, $errline);
    }
    public function _On404(): void
    {
        echo "no found";
    }
    public function _OnDefaultException($ex): void
    {
        echo "_OnDefaultException";
    }
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        echo "_OnDevErrorHandler";
    }
    protected function onBeforeCreatePhases(): void {}
    protected function onAfterCreatePhases(): void {}
    protected function onPrepare(): void {}
    protected function onBeforeChildrenInit(): void {}
    protected function onInit() {}
    protected function onInited() {}
    protected function onBeforeRun(): void {}
    protected function onAfterRun(): void {}
}
