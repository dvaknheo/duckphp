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
        'is_debug' => false, // no use in ,just align
        'path' => null,
        'namespace' => null,
        'name' => '',
        
        'app' => [],
        'cmd' => [],
        'data' => [],
        'ext' => [],
        
        'override_class' => null,
        
        'cli_enable' => true,
        'skip_404' => false,
        'skip_exception_check' => false,
        'init_components' => true,

        'on_init' => null,
        // 'on_inited' => null,
        'on_before_run' => null,
        //'on_after_run' => null,

        'setting_file' => 'config/DuckPhpSettings.config.php',
        'setting_file_ignore_exists' => true,
        'setting_file_enable' => true,
        'use_env_file' => false,

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
        //*/
    ];
    protected $is_root = true;
    protected $phase_name = '';
    protected static $ROOT_PHASE = '';
    protected $children_phase_map = [];
    
    public static function RunQuickly(array $options = [], ?callable $after_init = null): bool
    {
        $instance = static::_()->init($options);
        if ($after_init) {
            ($after_init)();
        }
        if (PHP_SAPI === 'cli' && $instance->isRoot() && $instance->options['cli_enable']) {
            return $instance->execute();
        } else {
            return $instance->serve();
        }
    }
    public static function Root()
    {
        return  PhaseContainer::GetContainer()->getClassOfContainer(self::class, self::$ROOT_PHASE);
    }
    public static function Phase($new = null)
    {
        return static::_()->_Phase($new);
    }
    public static function Setting($key = null, $default = null)
    {
        return static::_()->_Setting($key, $default);
    }
    public static function FromCurrentParent()
    {
        $APP = self::class;
        $flag = $APP::_()->toChildPhase(static::class);
        
        return $flag ? $APP::_() : null;
    }
    protected function initOptions(array $options): void
    {
        $this->options = array_replace_recursive($this->options, $options);
    }
    protected function getDefaultProjectNameSpace(?string $class): string
    {
        // MyProject\System\App => MyProject
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
    public function _Phase(?string $new = null): string
    {
        $container = PhaseContainer::GetContainerInstanceEx();
        $old = $container->getCurrentContainer();
        if (isset($new)) {
            $container->setCurrentContainer($new);
        }
        return $old;
    }
    public function isRoot()
    {
        return $this->is_root;
    }
    public function getThisClass()
    {
        return $this->this_class;
    }
    public function getThisParent()
    {
        $a = explode(':', $this->phase_name);
        $phase_name = implode('\\', $a);
        $this->_Phase($phase_name);

        return self::_();
    }
    public function getThisChild($class)
    {
        $phase = $this->options[$class]['__phase__'] ?? '';
        if (!$phase) {
            return null;
        }
        $this->_Phase($phase);
        return self::_();
    }
    public function getThisPhaseName()
    {
        return $this->phase_name;
    }
    public function getThisCommandPrefix()
    {
        return str_replace('/', '-', $this->phase_name);
    }
    protected function initContainer(?object $context = null): bool
    {
        //////////////////////////////
        $this->options['__class__'] = $this->getThisClass();
        
        if ($this->is_root) {
            $this->onBeforeCreatePhases();
            $flag = PhaseContainer::ReplaceSingletonImplement();
            $container = PhaseContainer::GetContainer();
            $container->setDefaultContainer($this->phase_name);
            $container->setCurrentContainer($this->phase_name);

            
            $this->onAfterCreatePhases();
        } else {
            $name = $this->options['name'] ? $this->options['name'] : $this->options['namespace'];
            $name = ($name === '@')? basename(str_replace('\\', '/', $this->getThisClass())) : $name;
            $name = ($name === '' && $this->options['namespace'] === '') ? static::class : $name;
            
            // @phpstan-ignore-next-line
            $this->phase_name = ltrim($context->getThisPhaseName() . ':' . str_replace('\\', '/', $name), ':');
            $container = PhaseContainer::GetContainer();
            $is_same_name = $container->issetContainer($this->phase_name);
            if ($is_same_name) {
                $object = $container->getClassOfContainer(self::class, $this->phase_name);
                $class = get_class($object);
                throw new DuckPhpSystemException("Phase Short name ({$this->phase_name}) is used by ($class) <br/>\nset ".static::class ." 'name' options.");
            }
            $container->setCurrentContainer($this->phase_name);
        }
        (self::class)::_($this);
        (static::class)::_($this);
       
        $this->options['__phase__'] = $this->getThisPhaseName();
        /////////////
        return true;
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
        $options['namespace'] = $options['namespace'] ?? ($this->options['namespace'] ?? ($this->getDefaultProjectNameSpace($this->this_class ?? null)));
        $options['path'] = $options['path'] ?? ($this->options['path'] ?? ($this->getDefaultProjectPath()));
        
        if ($options['override_class'] ?? false) {
            $class = $options['override_class'];
            unset($options['override_class']);
            return $class::_(new $class)->init($options, $context);
        }
        $this->is_root = is_null($context) || !(\is_a($context, self::class) || (static::class === self::class));
        if ($this->is_root) {
            require_once __DIR__ . '/Functions.php';
        }
        $this->initOptions($options);
        
         
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
            $this->addPublicClassesInRoot([
                Console::class,
            ]);
            Console::_()->init($this->options, $this);
        }

        Console::_()->regCommmandPrefixPhase($this->getThisCommandPrefix(), $this->getThisPhaseName());
        Console::_()->regCommandClasses($this->getThisCommandPrefix(), $this->options['cmd']);
        
        Route::_()->init($this->options, $this);
        Runtime::_()->init($this->options, $this);
        $this->doInitComponents();
    }
    protected function doInitComponents(): void
    {
        //for DuckPhp\Core\App override initComponents
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
            if (empty($options) || !is_array($options)) {
                continue;
            }
            if (!class_exists($class)) {
                throw new \Exception("Child [$class] not exists");
            }
            
            $options['controller_url_prefix'] = ltrim(Route::_()->options['controller_url_prefix'].'/'.$options['controller_url_prefix'], '/');
            $object = $class::_()->init($options, $this);
            $this->phaseToCurrent();
            $this->options[$class]['__phase__'] = $object->options['__phase__'] ?? '';
        }
    }
    public function toChildPhase(string $class)
    {
        if (!isset($this->options[$class]['__phase__'])) {
            return false;
        }
        $this->_Phase($this->options[$class]['__phase__']);
        return true;
    }

    public function run(): bool
    {
        if (PHP_SAPI === 'cli' && $this->is_root && $this->options['cli_enable']) {
            return $this->execute();
        } else {
            return $this->serve();
        }
    }
    public function serve(): bool
    {
        $ret = false;
        $this->phaseToCurrent();
        //TODO $this->resetDyminicObject();
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
            $flag = $class::_()->serve();
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
        $this->_Phase($this->phase_name);
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
    protected function onBeforeCreatePhases(): void
    {
    }
    protected function onAfterCreatePhases(): void
    {
    }
    protected function onPrepare(): void
    {
    }
    protected function onBeforeChildrenInit(): void
    {
    }
    protected function onInit()
    {
    }
    protected function onInited()
    {
    }
    protected function onBeforeRun(): void
    {
    }
    protected function onAfterRun(): void
    {
    }
}
