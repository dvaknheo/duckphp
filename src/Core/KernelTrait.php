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
        
        'cli_enable' => true,
        'skip_404' => false,
        'skip_exception_check' => false,
        'override_from' => null,
        'override_class' => null,
        'app_children_allow_mix_mode' => true,
        
        'on_init' => null,
        'on_inited' => null,
        'on_request' => null,

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
    protected static $ROOT_PHASE = '';
    protected static $ROOT_PHASE_OF_SHARED = '#public';
    
    private static $EXT_SKIP_INIT = -1;
    private static $EXT_DISABLE = 0;
    private static $EXT_DEFAULT = 1;
    private static $EXT_FOLLOW_APP = 2;
    private static $EXT_RENEW = 3;
    
    
    protected $is_root = true;
    protected $is_cli = false;
    protected $phase_name = '';
    protected $last_phase = '';
    // protected $this_class = '';      // from self
    // protected $is_inited = false;    // from ComponentBase::class
    
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
        return  PhaseContainer::_()->getClassOfContainer(self::class, self::$ROOT_PHASE);
    }
    public static function Phase($new = null)
    {
        return static::_()->_Phase($new);
    }
    public static function FromCurrentParent()
    {
        $APP = self::class;
        $flag = $APP::_()->toChildPhase(static::class);
        
        return $flag ? $APP::_() : null;
    }
    public static function SwitchRootPhase($phase)
    {
        self::$ROOT_PHASE = $phase;
        self::$ROOT_PHASE_OF_SHARED = $phase.'#public';

        // TODO be a function
        PhaseContainer::_()->current = self::$ROOT_PHASE;
        PhaseContainer::_()->default = self::$ROOT_PHASE_OF_SHARED;
    }
    protected function initOptions(array $options): void
    {
        $options['namespace'] = $options['namespace'] ?? ($this->options['namespace'] ?? ($this->getDefaultProjectNameSpace($options['override_from'] ?? null)));
        $options['path'] = $options['path'] ?? ($this->options['path'] ?? ($this->getDefaultProjectPath()));
        $options['path'] = realpath($options['path']).DIRECTORY_SEPARATOR;
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
        $container = PhaseContainer::_();
        $old = $container->getCurrentContainer();
        if (isset($new)) {
            $container->setCurrentContainer($new);
            $self = self::class;
            $self::_()->last_phase = $old;
        }
        return $old;
    }
    public function isRoot()
    {
        return $this->is_root;
    }
    public function getLastPhase()
    {
        return $this->last_phase ?? self::$ROOT_PHASE;
    }
    public function getThisClassName()
    {
        return $this->this_class;
    }
    public function getThisParent()
    {
        $a = explode(':', $this->phase_name);
        $phase_name = implode('\\', $a);
        $this->_Phase($phase_name);
        $class = self::class;
        return $class::_();
    }
    public function getThisChild($class)
    {
        $phase = $this->options['app'][$class]['__phase__'] ?? null;
        if (!isset($phase)) {
            return null;
        }
        $this->_Phase($phase);
        $class = self::class;
        return $class::_();
    }
    public function getThisPhaseName()
    {
        return $this->phase_name;
    }
    public function getThisCommandPrefix()
    {
        $name = $this->phase_name;
        $name = substr($name, strlen(self::$ROOT_PHASE) + 1);
        return str_replace('/', '-', $name);
    }
    public function regConsoleCommand($class, $default_method = 'command_')
    {
        $this->options['cmd'][$class] = $default_method;
        return Console::_()->regCommandClassSingle($this->getThisCommandPrefix(), $class, $default_method);
    }
    public function getProjectPath()
    {
        return self::Root()->options['path'];
    }
    protected function initContainer(?object $context = null): bool
    {
        //////////////////////////////
        if ($this->is_root) {
            //$flag = PhaseContainer::ReplaceSingletonImplement();
            $this->phase_name = self::$ROOT_PHASE;
            $container = PhaseContainer::_();
            $container->setDefaultContainer(self::$ROOT_PHASE_OF_SHARED);
            $container->setCurrentContainer($this->phase_name);

            $this->onAfterCreatePhases();
        } else {
            $name = $this->options['name'] ? $this->options['name'] : $this->options['namespace'];
            $name = ($name === '@')? basename(str_replace('\\', '/', $this->getThisClassName())) : $name;
            $name = ($name === '' && $this->options['namespace'] === '') ? static::class : $name;
            
            // @phpstan-ignore-next-line
            $this->phase_name = $context->getThisPhaseName() . ':' . str_replace('\\', '/', $name);
            $container = PhaseContainer::_();
            $is_same_name = $container->issetContainer($this->phase_name);
            if ($is_same_name) {
                $object = $container->getClassOfContainer(self::class, $this->phase_name);
                $class = get_class($object);
                throw new DuckPhpSystemException("Phase Short name ({$this->phase_name}) is used by ($class) <br/>\nset ".static::class ." 'name' options.");
            }
            $container->setCurrentContainer($this->phase_name);
        }
        $this->this_class = $this->options['override_from'] ?? $this->this_class;
        (self::class)::_($this);
        (static::class)::_($this);
        if ($this->options['override_from']) {
            ($this->options['override_from'])::_($this);
        }
        /////////////
        return true;
    }
    protected function createLocalObject(string $class, ?object $object = null): object
    {
        return PhaseContainer::_()->createLocalObject($class, $object);
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
        if ($options['override_class'] ?? false) {
            $class = $options['override_class'];
            unset($options['override_class']);
            $options['override_from'] = get_class($this); // importance
            return $class::_(new $class)->init($options, $context);
        }

        $this->initOptions($options);
        

        $this->is_root = is_null($context) || !(\is_a($context, self::class) || (static::class === self::class));
        $this->is_cli = PHP_SAPI === 'cli' && $this->options['cli_enable'];
        $this->initContainer($context);
        $this->onPrepare();
        
        $this->initException($this->options);
        $this->initComponents();

        $this->onInit();
        $this->initChildren($this->options['app']);

        $this->is_inited = true; // $this->is_inited come from ComponentTrait
        $this->onInited();
        return $this;
    }
    protected function initComponents()
    {
        if ($this->is_root) {
            $componets = [
                Console::class => self::$EXT_FOLLOW_APP,
            ];
            $this->initComponentsOfRoot($componets, self::$EXT_FOLLOW_APP);
        }
        
        $componets = [
            Route::class => self::$EXT_FOLLOW_APP,
        ];
        $this->initComponentsOfInner($componets, self::$EXT_FOLLOW_APP);

        $componets = $this->options['ext'];
        $this->initComponentsOfExt($componets, self::$EXT_FOLLOW_APP);
    }
    protected function initComponentsOfRoot($classes, $default): void
    {
        PhaseContainer::_()->addPublicClasses($classes);

        $this->initComponentsByClasseOptions($classes, $default);
    }
    
    protected function initComponentsOfInner($classes, $default): void
    {
        $this->initComponentsByClasseOptions($classes, $default);

        Console::_()->regCommmandPrefixPhase($this->getThisCommandPrefix(), $this->getThisPhaseName());
        Console::_()->regCommandClasses($this->getThisCommandPrefix(), $this->options['cmd']);
    }
    protected function initComponentsOfExt($classes, $default): void
    {
        $this->initComponentsByClasseOptions($classes, $default);
    }
    protected function initComponentsOfDynmic($classes, $default): void
    {
        $this->initComponentsByClasseOptions($classes, $default);
    }
    protected function initComponentsByClasseOptions(array $exts, $default): void
    {
        foreach ($exts as $class => $options) {
            $this->initExtensionsByOptions($class, $options, $default);
        }
    }
    //
    protected function initExtensionsByOptions(string $class, $options, $default)
    {
        if (!class_exists($class)) {
            throw new DuckPhpSystemException("ext [$class] not exists");
        }
    
        if ($options === false || $options === null || $options === self::$EXT_DISABLE) {
            return;
        }
        if ($options === true || $options === self::$EXT_DEFAULT) {
            $options = $default;
        }
        if ($options === self::$EXT_FOLLOW_APP) {
            $options = $this->options;
            $class::_()->init($options, $this);
            return;
        }
        if ($options === self::$EXT_SKIP_INIT) {
            $class::_();
            return;
        }
        if ($options === self::$EXT_RENEW) {
            $options = $class::_()->options;
            $class::_(new $class)->init($options, $this->options);
            return;
        }
        if (is_array($options)) {
            $class::_()->init($options, $this);
        }
        if (is_string($options)) {
            if ('@' === substr($options, 0, 1)) {
                $method = substr($options, 1);
                // @phpstan-ignore-next-line
                $options = call_user_func([$this,$method]);
                $this->initExtensionsByOptions($class, $options, $default);
            } else {
                $options = $this->options[$options] ?? false;
                $this->initExtensionsByOptions($class, $options, $default);
            }
            return;
        }
    }
    protected function initChildren(array $apps): void
    {
        if ($this->options['app_children_allow_mix_mode']) {
            $new_apps = [];
            foreach ($apps as $class => $options) {
                if (isset($options['class'])) {
                    $options['controller_url_prefix'] = $class;
                    $class = $options['class'];
                    unset($options['class']);
                }
                $new_apps[$class] = $options;
            }
            $apps = $new_apps;
            $this->options['app'] = $apps;
        }

        foreach ($apps as $class => $options) {
            if (empty($options) || !is_array($options)) {
                continue;
            }
            if (!class_exists($class)) {
                throw new DuckPhpSystemException("Child [$class] not exists");
            }

            if ('/' !== substr($options['controller_url_prefix'] ?? '', 0, 1)) {
                $options['controller_url_prefix'] = ltrim(Route::_()->options['controller_url_prefix'].'/'.$options['controller_url_prefix'], '/');
            }
            $object = $class::_()->init($options, $this);
            $phase = $object->getThisPhaseName();
            $this->phaseToCurrent();
            $this->options['app'][$class]['__phase__'] = $phase;
        }
    }
    public function toChildPhase(string $class)
    {
        if (!isset($this->options['app'][$class]['__phase__'])) {
            return false;
        }
        $this->_Phase($this->options['app'][$class]['__phase__']);
        return true;
    }

    public function run(): bool
    {
        if ($this->options['cli_enable']) {
            return $this->execute();
        } else {
            return $this->serve();
        }
    }
    public function serve(): bool
    {
        $ret = false;
        $this->prepareServe();
        $this->onRequest();
        try {
            Runtime::_()->run();
            $ret = Route::_()->run();
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
        return $ret;
    }
    protected function prepareServe()
    {
        $this->phaseToCurrent();
        $classes = [];
        $this->initComponentsOfDynmic($classes, self::$EXT_RENEW);
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
            $object = $this->getThisChild($class);
            $flag = $object->serve();
            if ($flag) {
                break;
            }
        }
        return $flag;
    }
    public function execute(): bool
    {
        $ret = false;

        try {
            $ret = Console::_()->run();
        } catch (\Throwable $ex) {
            $this->runException($ex);
            $ret = true;
        }
        return $ret;
    }
    public function phaseToCurrent(): void
    {
        PhaseContainer::_()->setCurrentContainer($this->phase_name);
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
    protected function onAfterCreatePhases(): void
    {
    }
    protected function onPrepare(): void
    {
    }
    protected function onInit(): void
    {
        if ($this->options['on_init']) {
            ($this->options['on_init'])();
        }
    }
    protected function onInited(): void
    {
        if ($this->options['on_inited']) {
            ($this->options['on_inited'])();
        }
    }
    protected function onRequest(): void
    {
        if ($this->options['on_request']) {
            ($this->options['on_request'])();
        }
    }
}
